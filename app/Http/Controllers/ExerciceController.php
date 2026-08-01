<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\StudyEvent;
use App\Models\Subject;
use App\Services\MasteryCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExerciceController extends Controller
{
    public function index(Request $request): View
    {
        $subject = $request->filled('matiere')
            ? Subject::where('slug', $request->string('matiere'))->first()
            : null;

        $exercices = Exercise::with(['subject', 'chapter', 'attempts'])
            ->when($subject, fn ($q) => $q->where('subject_id', $subject->id))
            ->when($request->filled('origine'), fn ($q) => $q->where('origin', $request->string('origine')))
            ->orderBy('subject_id')->orderBy('position')
            ->get();

        return view('exercices.index', [
            'exercices' => $exercices->groupBy(fn (Exercise $e) => $e->subject->code),
            'subjects' => Subject::orderBy('position')->get(),
            'subject' => $subject,
            'origine' => $request->string('origine')->toString(),
        ]);
    }

    public function show(Exercise $exercise): View
    {
        $exercise->load(['subject', 'chapter', 'resource']);

        return view('exercices.show', [
            'exercice' => $exercise,
            'derniere' => $exercise->attempts()->first(),
            'tentatives' => $exercise->attempts()->limit(5)->get(),
        ]);
    }

    /**
     * Enregistre la tentative.
     *
     * Le score n'est pas déclaratif au doigt mouillé : il est dérivé de la
     * grille d'attendus cochée, puis pénalisé si la solution a été ouverte.
     * C'est le remède direct au « décrire au lieu de démontrer ».
     */
    public function submit(Request $request, Exercise $exercise, MasteryCalculator $mastery): RedirectResponse
    {
        $data = $request->validate([
            'answer' => ['nullable', 'string', 'max:50000'],
            'reveal_level' => ['required', 'integer', 'between:0,3'],
            'rubric' => ['nullable', 'array'],
            'rubric.*' => ['nullable'],
            'duree' => ['nullable', 'integer', 'min:0'],
        ]);

        $coches = array_map('intval', array_keys(array_filter($data['rubric'] ?? [])));
        $grille = $exercise->rubric ?? [];

        $obtenus = 0;
        foreach ($coches as $i) {
            $obtenus += (float) ($grille[$i]['points'] ?? 0);
        }

        $total = (float) collect($grille)->sum('points');
        $brut = $total > 0 ? ($obtenus / $total) * 100 : 0;

        // Ouvrir la solution avant de rédiger plafonne le crédit : on n'apprend
        // pas en lisant un corrigé, on apprend en butant dessus.
        $penalite = match ($data['reveal_level']) {
            0 => 1.0,
            1 => 0.9,
            2 => 0.75,
            default => 0.4,
        };

        $score = (int) round($brut * $penalite);

        ExerciseAttempt::create([
            'exercise_id' => $exercise->id,
            'answer' => $data['answer'] ?? null,
            'reveal_level' => $data['reveal_level'],
            'rubric_check' => $coches,
            'self_score' => $score,
            'duration_sec' => $data['duree'] ?? 0,
            'completed_at' => now(),
        ]);

        StudyEvent::record('exercise_done', [
            'chapter_id' => $exercise->chapter_id,
            'subject_id' => $exercise->subject_id,
            'minutes' => (int) round(($data['duree'] ?? 0) / 60) ?: $exercise->est_minutes,
            'payload' => ['exercise_id' => $exercise->id, 'score' => $score],
        ]);

        if ($exercise->chapter) {
            $mastery->forChapter($exercise->chapter);
        }

        $message = match (true) {
            $data['reveal_level'] >= 3 => "Solution consultée : l'exercice ne compte qu'à 40 %. Refaites-le à blanc dans deux jours.",
            $score >= 80 => "Bien. {$score} % des attendus cochés.",
            $score >= 50 => "{$score} % des attendus. Reprenez les points manqués : c'est exactement là que le barème se joue.",
            default => "{$score} %. À reprendre depuis la fiche de cours avant de retenter.",
        };

        return redirect()->route('exercices.show', $exercise)->with('succes', $message);
    }
}