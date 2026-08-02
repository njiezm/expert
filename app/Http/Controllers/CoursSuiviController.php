<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use App\Models\StudyEvent;
use App\Models\Subject;
use App\Services\MasteryCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * « Suivre le cours » — le parcours linéaire, séance après séance.
 */
class CoursSuiviController extends Controller
{
    /** Le sommaire du cours d'une matière, avec la progression. */
    public function index(Subject $subject): View
    {
        $seances = Seance::where('subject_id', $subject->id)
            ->orderBy('position')
            ->get();

        $suivies = Seance::suiviesPour($subject->id);

        // La séance à reprendre : la première non suivie.
        $reprise = $seances->first(fn (Seance $s) => ! in_array($s->id, $suivies, true));

        return view('cours-suivi.index', [
            'subject' => $subject,
            'seances' => $seances,
            'suivies' => $suivies,
            'reprise' => $reprise,
            'minutesTotal' => $seances->sum('duree_min'),
            'minutesRestantes' => $seances
                ->reject(fn (Seance $s) => in_array($s->id, $suivies, true))
                ->sum('duree_min'),
        ]);
    }

    public function show(Subject $subject, Seance $seance): View
    {
        abort_unless($seance->subject_id === $subject->id, 404);

        $seances = Seance::where('subject_id', $subject->id)->orderBy('position')->get();
        $suivies = Seance::suiviesPour($subject->id);

        return view('cours-suivi.show', [
            'subject' => $subject,
            'seance' => $seance,
            'seances' => $seances,
            'suivies' => $suivies,
            'precedente' => $seance->precedente(),
            'suivante' => $seance->suivante(),
            'dejaSuivie' => in_array($seance->id, $suivies, true),
            'numero' => $seances->search(fn (Seance $s) => $s->id === $seance->id) + 1,
        ]);
    }

    /** Marque la séance comme suivie, puis enchaîne sur la suivante. */
    public function terminer(Request $request, Subject $subject, Seance $seance, MasteryCalculator $mastery): RedirectResponse
    {
        abort_unless($seance->subject_id === $subject->id, 404);

        StudyEvent::record('seance_suivie', [
            'subject_id' => $subject->id,
            'chapter_id' => $seance->chapter_id,
            'minutes' => (int) $request->integer('minutes', $seance->duree_min),
            'payload' => ['seance_id' => $seance->id],
        ]);

        if ($seance->chapter) {
            $mastery->forChapter($seance->chapter);
        }

        $suivante = $seance->suivante();

        if ($suivante) {
            return redirect()->route('cours-suivi.show', [$subject, $suivante]);
        }

        return redirect()
            ->route('cours-suivi.index', $subject)
            ->with('succes', "Cours de {$subject->code} terminé. Place aux cartes et aux exercices — c'est là que la note se joue.");
    }

    /** Rouvre une séance déjà suivie, pour la reprendre. */
    public function reprendre(Subject $subject, Seance $seance): RedirectResponse
    {
        abort_unless($seance->subject_id === $subject->id, 404);

        StudyEvent::where('kind', 'seance_suivie')
            ->where('subject_id', $subject->id)
            ->whereJsonContains('payload->seance_id', $seance->id)
            ->delete();

        return redirect()->route('cours-suivi.show', [$subject, $seance]);
    }
}