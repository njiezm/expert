<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Subject;
use App\Services\SpacedRepetition;
use Illuminate\View\View;

class MatiereController extends Controller
{
    public function show(Subject $subject, SpacedRepetition $srs): View
    {
        $subject->load([
            'chapters.progress',
            'chapters.lessons',
            'examPapers',
            'mockExams.sessions',
        ]);

        return view('matieres.show', [
            'subject' => $subject,
            'lacunes' => $subject->gaps()->with('chapter')->open()->get(),
            // Les lacunes refermées restent accessibles : une lacune close par erreur,
            // ou rouverte parce qu'elle n'a pas tenu, doit pouvoir être retrouvée ici.
            'lacunesFermees' => $subject->gaps()->with('chapter')
                ->where('status', 'maitrisee')
                ->latest('resolved_at')
                ->get(),
            'ressources' => $subject->resources()
                ->orderBy('kind')->orderBy('title')
                ->get()
                ->groupBy('kind'),
            'cartesDues' => $srs->dueCountBySubject()[$subject->id] ?? 0,
        ]);
    }

    public function chapter(Subject $subject, Chapter $chapter): View
    {
        abort_unless($chapter->subject_id === $subject->id, 404);

        $chapter->load(['lessons', 'exercises', 'gaps', 'progress', 'resources']);

        return view('matieres.chapitre', [
            'subject' => $subject,
            'chapter' => $chapter,
            'cartes' => $chapter->flashcards()->with('state')->get(),
            'precedent' => $subject->chapters()->where('position', '<', $chapter->position)
                ->orderByDesc('position')->first(),
            'suivant' => $subject->chapters()->where('position', '>', $chapter->position)
                ->orderBy('position')->first(),
        ]);
    }
}