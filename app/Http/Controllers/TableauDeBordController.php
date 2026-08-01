<?php

namespace App\Http\Controllers;

use App\Models\Gap;
use App\Models\StudyBlock;
use App\Models\StudyEvent;
use App\Models\Subject;
use App\Services\SpacedRepetition;
use Illuminate\View\View;

class TableauDeBordController extends Controller
{
    public function __construct(private readonly SpacedRepetition $srs) {}

    public function index(): View
    {
        $subjects = Subject::with('chapters.progress')
            ->orderBy('position')
            ->get();

        $matieres = $subjects->where('is_transversal', false);
        $rigueur = $subjects->firstWhere('is_transversal', true);

        $dueBySubject = $this->srs->dueCountBySubject();

        // Charge restante : les minutes qu'il reste à poser d'ici chaque épreuve.
        $planToday = StudyBlock::today()
            ->with(['subject', 'chapter', 'slot'])
            ->orderBy('position')
            ->get();

        return view('tableau-de-bord', [
            'matieres' => $matieres,
            'rigueur' => $rigueur,
            'dueBySubject' => $dueBySubject,
            'dueTotal' => array_sum($dueBySubject),
            'planToday' => $planToday,
            'prochaine' => $matieres->whereNotNull('exam_at')
                ->where('exam_at', '>=', now()->startOfDay())
                ->sortBy('exam_at')->first(),
            'lacunesOuvertes' => Gap::open()->count(),
            'lacunesTotal' => Gap::count(),
            'minutes7j' => (int) StudyEvent::where('occurred_at', '>=', now()->subDays(7))->sum('minutes'),
            'serie' => $this->serie(),
            'joursRestants' => (int) now()->startOfDay()->diffInDays(
                $matieres->whereNotNull('exam_at')->max('exam_at')?->startOfDay() ?? now(),
                false
            ),
        ]);
    }

    /** Nombre de jours consécutifs travaillés, en remontant depuis aujourd'hui. */
    private function serie(): int
    {
        $jours = StudyEvent::query()
            ->selectRaw('date(occurred_at) as j')
            ->groupBy('j')
            ->orderByDesc('j')
            ->limit(60)
            ->pluck('j')
            ->map(fn ($d) => (string) $d)
            ->flip();

        $serie = 0;
        $curseur = now()->startOfDay();

        // Une journée encore en cours ne casse pas la série.
        if (! $jours->has($curseur->toDateString())) {
            $curseur->subDay();
        }

        while ($jours->has($curseur->toDateString())) {
            $serie++;
            $curseur->subDay();
        }

        return $serie;
    }
}