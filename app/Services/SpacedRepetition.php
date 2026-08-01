<?php

namespace App\Services;

use App\Models\Flashcard;
use App\Models\FlashcardReview;
use App\Models\FlashcardState;
use App\Models\StudyEvent;
use Illuminate\Support\Facades\DB;

/**
 * Répétition espacée, variante SM-2 resserrée.
 *
 * Le SM-2 canonique vise la rétention à plusieurs mois ; ici l'échéance est à
 * moins de quatre semaines. Les intervalles sont donc plafonnés (voir
 * config/meridien.sm2.max_interval) pour qu'aucune carte ne sorte de la
 * fenêtre de révision avant les épreuves.
 */
class SpacedRepetition
{
    /** Les quatre boutons de notation, traduits vers l'échelle SM-2 (0-5). */
    public const GRADES = [
        1 => ['label' => 'Raté', 'quality' => 1, 'tone' => 'lacune'],
        2 => ['label' => 'Difficile', 'quality' => 3, 'tone' => 'alerte'],
        3 => ['label' => 'Correct', 'quality' => 4, 'tone' => 'accent'],
        4 => ['label' => 'Facile', 'quality' => 5, 'tone' => 'acquis'],
    ];

    public function state(Flashcard $card): FlashcardState
    {
        // Les valeurs sont posées explicitement : se reposer sur les défauts SQL
        // laisserait le modèle en mémoire avec des colonnes nulles, qui seraient
        // ensuite réécrites telles quelles lors de la première mise à jour.
        return $card->state ?? $card->state()->create([
            'ease_factor' => 2.50,
            'interval_days' => 0,
            'repetitions' => 0,
            'lapses' => 0,
            'due_on' => today(),
        ]);
    }

    /**
     * Enregistre une révision et reprogramme la carte.
     *
     * @param  int  $button  1 à 4 (voir self::GRADES)
     */
    public function review(Flashcard $card, int $button, int $durationSec = 0): FlashcardState
    {
        $quality = self::GRADES[$button]['quality'] ?? 3;
        $cfg = config('meridien.sm2');

        return DB::transaction(function () use ($card, $button, $quality, $durationSec, $cfg) {
            $state = $this->state($card);

            $ease = (float) $state->ease_factor;
            $reps = $state->repetitions;
            $lapses = $state->lapses;

            if ($quality < 3) {
                // Échec : la carte repart à zéro et revient dès le lendemain.
                $reps = 0;
                $lapses++;
                $interval = 1;
            } else {
                $reps++;
                $interval = match ($reps) {
                    1 => $cfg['first_interval'],
                    2 => $cfg['second_interval'],
                    default => (int) ceil($state->interval_days * $ease),
                };
                $interval = min($interval, $cfg['max_interval']);
            }

            // Ajustement de facilité SM-2.
            $ease = max(
                $cfg['min_ease'],
                $ease + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02))
            );

            $state->update([
                'ease_factor' => round($ease, 2),
                'interval_days' => $interval,
                'repetitions' => $reps,
                'lapses' => $lapses,
                'due_on' => today()->addDays($interval),
                'last_reviewed_at' => now(),
            ]);

            FlashcardReview::create([
                'flashcard_id' => $card->id,
                'quality' => $quality,
                'duration_sec' => $durationSec,
                'ease_after' => round($ease, 2),
                'interval_after' => $interval,
                'reviewed_at' => now(),
            ]);

            StudyEvent::record('card_reviewed', [
                'chapter_id' => $card->chapter_id,
                'subject_id' => $card->chapter->subject_id,
                'minutes' => (int) round($durationSec / 60),
                'payload' => ['button' => $button, 'quality' => $quality, 'interval' => $interval],
            ]);

            return $state;
        });
    }

    /**
     * File d'attente du jour.
     *
     * L'ordre n'est pas neutre : les cartes rattachées à une lacune constatée
     * en examen passent devant, puis les cartes en retard, puis les neuves.
     */
    public function queue(?int $subjectId = null, int $limit = 40)
    {
        return Flashcard::query()
            ->with(['chapter.subject', 'state', 'gap'])
            ->when($subjectId, fn ($q) => $q->whereHas('chapter', fn ($c) => $c->where('subject_id', $subjectId)))
            ->where(function ($q) {
                $q->whereDoesntHave('state')
                    ->orWhereHas('state', fn ($s) => $s->whereDate('due_on', '<=', today()));
            })
            ->orderByRaw('gap_id is null')                       // les lacunes d'abord
            ->orderByRaw('(select due_on from flashcard_states where flashcard_states.flashcard_id = flashcards.id) asc nulls last')
            ->limit($limit)
            ->get();
    }

    /** Compte des cartes dues, par matière. */
    public function dueCountBySubject(): array
    {
        // L'agrégat doit porter un alias explicite : sans lui, PostgreSQL nomme la
        // colonne « count » et pluck() cherche « count(*) », qui n'existe pas.
        return DB::table('flashcards')
            ->join('chapters', 'chapters.id', '=', 'flashcards.chapter_id')
            ->leftJoin('flashcard_states', 'flashcard_states.flashcard_id', '=', 'flashcards.id')
            ->where(function ($q) {
                $q->whereNull('flashcard_states.id')
                    ->orWhereDate('flashcard_states.due_on', '<=', today());
            })
            ->groupBy('chapters.subject_id')
            ->selectRaw('chapters.subject_id, count(*) as total')
            ->pluck('total', 'subject_id')
            ->all();
    }
}