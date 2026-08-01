<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use App\Models\Subject;
use App\Services\MasteryCalculator;
use App\Services\SpacedRepetition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DrillController extends Controller
{
    public function __construct(private readonly SpacedRepetition $srs) {}

    public function index(): View
    {
        $due = $this->srs->dueCountBySubject();

        return view('drill.index', [
            'subjects' => Subject::orderBy('position')->get(),
            'due' => $due,
            'total' => array_sum($due),
            'revuesAujourdhui' => \App\Models\FlashcardReview::whereDate('reviewed_at', today())->count(),
        ]);
    }

    /**
     * Une carte à la fois. La file est recalculée à chaque passage : une carte
     * ratée revient donc en fin de session, pas au lendemain.
     */
    public function session(Request $request): View|RedirectResponse
    {
        $subjectId = $request->integer('matiere') ?: null;
        $queue = $this->srs->queue($subjectId, 40);

        if ($queue->isEmpty()) {
            return redirect()->route('drill.index')
                ->with('succes', 'Plus rien à réviser pour le moment. Passez aux exercices.');
        }

        $carte = $queue->first();

        return view('drill.session', [
            'carte' => $carte,
            'restantes' => $queue->count(),
            'subjectId' => $subjectId,
            'notes' => SpacedRepetition::GRADES,
        ]);
    }

    public function review(
        Request $request,
        Flashcard $flashcard,
        MasteryCalculator $mastery
    ): RedirectResponse {
        $data = $request->validate([
            'note' => ['required', 'integer', 'between:1,4'],
            'duree' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'matiere' => ['nullable', 'integer'],
        ]);

        $this->srs->review($flashcard, $data['note'], $data['duree'] ?? 0);
        $mastery->forChapter($flashcard->chapter);

        return redirect()->route('drill.session', array_filter(['matiere' => $data['matiere'] ?? null]));
    }
}