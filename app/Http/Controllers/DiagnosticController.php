<?php

namespace App\Http\Controllers;

use App\Models\ExamPaper;
use App\Models\Gap;
use App\Models\Subject;
use App\Services\MasteryCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiagnosticController extends Controller
{
    public function index(): View
    {
        $papers = ExamPaper::with('subject', 'resource')
            ->get()
            ->sortBy('grade');

        return view('diagnostic.index', [
            'papers' => $papers,
            'moyenne' => round($papers->avg('grade'), 2),
            'parNature' => Gap::selectRaw('kind, count(*) as total, sum(case when status = \'maitrisee\' then 1 else 0 end) as fermees')
                ->groupBy('kind')
                ->get()
                ->keyBy('kind'),
            'lacunesGraves' => Gap::with('subject', 'chapter')
                ->open()
                ->orderByDesc('severity')
                ->limit(12)
                ->get(),
            'rigueur' => Subject::where('is_transversal', true)->first(),
        ]);
    }

    public function show(Subject $subject): View
    {
        return view('diagnostic.show', [
            'subject' => $subject,
            'paper' => $subject->examPapers()->with('resource')->first(),
            'lacunes' => $subject->gaps()->with('chapter')->orderByDesc('severity')->get()->groupBy('kind'),
        ]);
    }

    public function updateGap(Request $request, Gap $gap, MasteryCalculator $mastery): RedirectResponse
    {
        $statut = $request->validate([
            'status' => ['required', 'in:ouverte,en_cours,maitrisee'],
        ])['status'];

        $gap->update([
            'status' => $statut,
            'resolved_at' => $statut === 'maitrisee' ? now() : null,
        ]);

        if ($gap->chapter) {
            $mastery->forChapter($gap->chapter);
        }

        return back()->with('succes', $statut === 'maitrisee'
            ? 'Lacune refermée. Elle restera dans le drill quelques jours pour vérifier que ça tient.'
            : 'Statut mis à jour.');
    }
}