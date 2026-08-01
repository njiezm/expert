<?php

namespace App\Services;

use App\Models\AvailabilitySlot;
use App\Models\Chapter;
use App\Models\StudyBlock;
use App\Models\Subject;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Répartit le temps disponible d'ici au 28 août sur les cinq matières.
 *
 * Le principe est le rétroplanning : on part de chaque date d'épreuve et on
 * remonte. Trois règles dominent l'allocation :
 *
 *  1. Une matière ne se travaille plus après son épreuve.
 *  2. Le dernier créneau avant une épreuve lui est réservé.
 *  3. Le 26 août porte deux épreuves (AGC 15h–18h, SPP 20h–23h). Les jours qui
 *     le précèdent doivent couvrir les deux, et la journée elle-même est bloquée.
 */
class PlanningEngine
{
    /** Modèles de créneaux par type de journée : [début, fin]. */
    private const SLOT_TEMPLATES = [
        'soiree' => [['19:30', '22:30']],
        'weekend' => [['09:00', '12:30'], ['14:00', '18:00'], ['20:00', '22:30']],
        'teletravail' => [['07:00', '08:45'], ['12:15', '13:45'], ['18:30', '22:30']],
        'conge' => [['09:00', '12:30'], ['14:00', '18:00'], ['20:00', '22:30']],
    ];

    /**
     * Crée les créneaux de la période, sans écraser ceux déjà personnalisés.
     *
     * @param  array<string, string>  $dayTypes  ['2026-08-05' => 'conge', ...]
     */
    public function generateSlots(Carbon $from, Carbon $to, array $dayTypes = []): int
    {
        $created = 0;
        $examDays = Subject::whereNotNull('exam_at')->get()
            ->groupBy(fn (Subject $s) => $s->exam_at->toDateString());

        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $key = $day->toDateString();

            $type = $dayTypes[$key]
                ?? ($day->isWeekend() ? 'weekend' : 'soiree');

            foreach (self::SLOT_TEMPLATES[$type] ?? self::SLOT_TEMPLATES['soiree'] as [$start, $end]) {
                $minutes = Carbon::parse($start)->diffInMinutes(Carbon::parse($end));

                $slot = AvailabilitySlot::firstOrCreate(
                    ['day' => $key, 'starts_at' => $start],
                    ['label' => $type, 'ends_at' => $end, 'minutes' => $minutes]
                );

                $created += $slot->wasRecentlyCreated ? 1 : 0;
            }

            // Les épreuves sont posées comme créneaux verrouillés : le planning
            // les contourne et elles restent visibles dans l'agenda.
            foreach ($examDays[$key] ?? [] as $subject) {
                $start = $subject->exam_at->format('H:i');
                $end = $subject->exam_at->copy()->addMinutes($subject->exam_duration_min)->format('H:i');

                AvailabilitySlot::firstOrCreate(
                    ['day' => $key, 'starts_at' => $start],
                    [
                        'label' => 'examen',
                        'ends_at' => $end,
                        'minutes' => $subject->exam_duration_min,
                        'is_locked' => true,
                        'note' => "ÉPREUVE — {$subject->code} · {$subject->name}",
                    ]
                );
            }
        }

        return $created;
    }

    /**
     * Recalcule intégralement les blocs de travail à partir d'aujourd'hui.
     *
     * Les blocs déjà marqués « fait » sont conservés : on ne réécrit pas le passé.
     */
    public function rebuild(): array
    {
        return DB::transaction(function () {
            StudyBlock::whereDate('day', '>=', today())
                ->where('status', '!=', 'fait')
                ->delete();

            $subjects = Subject::whereNotNull('exam_at')
                ->where('exam_at', '>=', now())
                ->orderBy('exam_at')
                ->get();

            $transversal = Subject::where('is_transversal', true)->first();

            $slots = AvailabilitySlot::whereDate('day', '>=', today())
                ->where('is_locked', false)
                ->with('blocks')
                ->orderBy('day')->orderBy('starts_at')
                ->get();

            $placed = 0;
            $blockMin = config('meridien.planning.block_minutes');

            // Besoin relatif de chaque matière, figé pour toute la répartition.
            $need = $subjects->mapWithKeys(fn (Subject $s) => [$s->id => $this->need($s)]);
            $totalNeed = max(0.001, $need->sum());

            // Minutes déjà attribuées : c'est ce compteur qui garantit que la
            // matière la plus en retard rattrape, au lieu d'un simple tourniquet
            // où les dernières de la liste ne passaient jamais.
            $given = $subjects->mapWithKeys(fn (Subject $s) => [$s->id => 0])->all();
            $rigueurDays = [];

            foreach ($slots as $slot) {
                $eligible = $this->eligibleSubjects($subjects, $slot->day);

                if ($eligible->isEmpty()) {
                    continue;
                }

                $position = $slot->blocks->count();

                // Un créneau accueille au plus quatre blocs : au-delà, le rendement chute.
                $capacity = min(intdiv($slot->free_minutes, $blockMin), 4);
                $jour = $slot->day->toDateString();

                for ($i = 0; $i < $capacity; $i++) {
                    // Une seule séance de rédaction par jour, sur un créneau long.
                    $prendRigueur = $transversal
                        && $capacity >= 3
                        && $i === $capacity - 1
                        && ! isset($rigueurDays[$jour]);

                    if ($prendRigueur) {
                        $rigueurDays[$jour] = true;
                        $subject = $transversal;
                        $activity = 'revision';
                    } else {
                        $subject = $this->mostBehind($eligible, $need, $given, $totalNeed);
                        $given[$subject->id] += $blockMin;
                        $activity = $this->activityFor($i, $subject, $slot);
                    }

                    $chapter = $activity === 'examen_blanc' ? null : $this->weakestChapter($subject);

                    StudyBlock::create([
                        'availability_slot_id' => $slot->id,
                        'day' => $slot->day,
                        'subject_id' => $subject->id,
                        'chapter_id' => $chapter?->id,
                        'activity' => $activity,
                        'planned_minutes' => $blockMin,
                        'position' => $position++,
                        'rationale' => $prendRigueur
                            ? 'Rédaction : justifier, trancher, rester dans le référentiel — la lacune qui a coûté des points dans trois copies.'
                            : $this->explain($subject, $chapter, $activity),
                    ]);
                    $placed++;
                }
            }

            return ['blocs' => $placed, 'creneaux' => $slots->count()];
        });
    }

    /**
     * Matières encore travaillables ce jour-là, pondérées par l'urgence.
     *
     * La veille d'une épreuve, tout le créneau bascule sur cette matière : c'est
     * la règle du dernier jour, non négociable.
     */
    private function eligibleSubjects(Collection $subjects, Carbon $day): Collection
    {
        $open = $subjects->filter(fn (Subject $s) => $s->exam_at->startOfDay()->gte($day));

        if (config('meridien.planning.reserve_last_day')) {
            $eve = $open->filter(fn (Subject $s) => $s->exam_at->copy()->subDay()->isSameDay($day));

            // Le 26 août tombe deux épreuves : la veille est partagée entre les deux.
            if ($eve->isNotEmpty()) {
                return $eve;
            }

            $sameDay = $open->filter(fn (Subject $s) => $s->exam_at->isSameDay($day));

            if ($sameDay->isNotEmpty()) {
                return $sameDay;
            }
        }

        return $open;
    }

    /**
     * Besoin relatif d'une matière : ce qui reste à combler.
     *
     * Deux termes. Ce qui manque en maîtrise, et la sévérité de l'échec initial :
     * un 1,5/20 ne demande pas le même volume qu'un 7/20, même à maîtrise
     * mesurée identique. Sans ce second terme, toutes les matières partent
     * à égalité tant qu'aucun contenu n'a été travaillé.
     */
    private function need(Subject $subject): float
    {
        $manque = max(5, 100 - $subject->mastery);

        $severite = $subject->initial_grade !== null
            ? 1 + (20 - (float) $subject->initial_grade) / 20   // 1,0 à 2,0
            : 1.5;

        return $manque * $severite;
    }

    /**
     * La matière la plus en retard sur sa part théorique.
     *
     * @param  \Illuminate\Support\Collection<int, float>  $need
     * @param  array<int, int>  $given
     */
    private function mostBehind(Collection $eligible, Collection $need, array $given, float $totalNeed): Subject
    {
        $totalGiven = max(1, array_sum($given));

        return $eligible
            ->sortByDesc(function (Subject $s) use ($need, $given, $totalNeed, $totalGiven) {
                $part = ($need[$s->id] ?? 1) / $totalNeed;      // part visée
                $recu = ($given[$s->id] ?? 0) / $totalGiven;    // part reçue

                return $part - $recu;
            })
            ->first();
    }

    /**
     * Nature du bloc selon sa place dans le créneau.
     *
     * Un créneau commence toujours par du drill : la mémoire se réactive avant
     * d'ajouter du neuf.
     */
    private function activityFor(int $index, Subject $subject, AvailabilitySlot $slot): string
    {
        if ($index >= 2 && $this->deservesMock($subject, $slot)) {
            return 'examen_blanc';
        }

        return match ($index) {
            0 => 'drill',
            1 => 'cours',
            default => 'exercice',
        };
    }

    /** Un examen blanc se justifie à J−7 puis à J−2 de l'épreuve. */
    private function deservesMock(Subject $subject, AvailabilitySlot $slot): bool
    {
        $days = (int) $slot->day->diffInDays($subject->exam_at->startOfDay(), false);

        return in_array($days, [7, 2], true);
    }

    /** Le chapitre le moins maîtrisé, à poids d'examen égal le plus lourd. */
    private function weakestChapter(Subject $subject): ?Chapter
    {
        return $subject->chapters()
            ->with('progress')
            ->get()
            ->sortBy(fn (Chapter $c) => ($c->progress?->mastery ?? 0) - $c->exam_weight * 5)
            ->first();
    }

    private function explain(Subject $subject, ?Chapter $chapter, string $activity): string
    {
        $days = max(0, $subject->days_until_exam ?? 0);
        $cible = $chapter ? " — {$chapter->title}" : '';

        return match ($activity) {
            'drill' => "Réactivation mémoire {$subject->code}{$cible}. J−{$days}.",
            'cours' => "Acquisition {$subject->code}{$cible} : chapitre le plus faible au regard de son poids au barème.",
            'exercice' => "Mise en application {$subject->code}{$cible}. La note se joue sur la pratique, pas sur la relecture.",
            'examen_blanc' => "Examen blanc {$subject->code} en conditions réelles, à J−{$days}.",
            default => "Travail {$subject->code}{$cible}.",
        };
    }
}