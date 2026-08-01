<?php

namespace App\Http\Controllers;

use App\Models\MockExam;
use App\Models\MockExamAnswer;
use App\Models\MockExamSession;
use App\Models\StudyEvent;
use App\Models\Subject;
use App\Services\MasteryCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamenBlancController extends Controller
{
    public function index(): View
    {
        return view('examens.index', [
            'subjects' => Subject::with(['mockExams.sessions', 'mockExams.questions'])
                ->whereNotNull('exam_at')
                ->examOrder()
                ->get(),
            'enCours' => MockExamSession::with('mockExam.subject')
                ->whereNull('finished_at')
                ->latest('started_at')
                ->first(),
            'passees' => MockExamSession::with('mockExam.subject')
                ->whereNotNull('finished_at')
                ->latest('finished_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function show(MockExam $mockExam): View
    {
        $mockExam->load(['subject', 'questions', 'sessions']);

        return view('examens.show', [
            'examen' => $mockExam,
            'modes' => MockExamSession::MODES,
            // Le mode par défaut reproduit les conditions réelles de l'épreuve.
            'modeSuggere' => $mockExam->subject->exam_mode,
        ]);
    }

    public function start(Request $request, MockExam $mockExam): RedirectResponse
    {
        $mode = $request->validate([
            'mode' => ['required', 'in:amphi,distance_nuit'],
        ])['mode'];

        // Une seule composition à la fois : on clôt toute session laissée ouverte.
        MockExamSession::whereNull('finished_at')->update([
            'finished_at' => now(),
            'was_timed_out' => true,
        ]);

        $session = MockExamSession::create([
            'mock_exam_id' => $mockExam->id,
            'mode' => $mode,
            'started_at' => now(),
            'allowed_sec' => $mockExam->duration_min * 60,
            'max_score' => $mockExam->total_points,
        ]);

        return redirect()->route('examens.composer', $session);
    }

    public function compose(MockExamSession $session): View|RedirectResponse
    {
        if (! $session->isRunning()) {
            return redirect()->route('examens.correction', $session);
        }

        $session->load(['mockExam.subject', 'mockExam.questions', 'answers']);

        return view('examens.composer', [
            'session' => $session,
            'examen' => $session->mockExam,
            'questions' => $session->mockExam->questions,
            'reponses' => $session->answers->keyBy('mock_exam_question_id'),
            'finTimestamp' => $session->started_at->copy()->addSeconds($session->allowed_sec)->timestamp,
        ]);
    }

    public function finish(Request $request, MockExamSession $session): RedirectResponse
    {
        if ($session->isRunning()) {
            $reponses = $request->input('reponses', []);

            foreach ($session->mockExam->questions as $question) {
                MockExamAnswer::updateOrCreate(
                    [
                        'mock_exam_session_id' => $session->id,
                        'mock_exam_question_id' => $question->id,
                    ],
                    ['answer' => $reponses[$question->id] ?? null]
                );
            }

            $ecoule = (int) $session->started_at->diffInSeconds(now());

            $session->update([
                'finished_at' => now(),
                'elapsed_sec' => min($ecoule, $session->allowed_sec),
                'was_timed_out' => $ecoule >= $session->allowed_sec,
            ]);
        }

        return redirect()->route('examens.correction', $session)
            ->with('succes', 'Copie rendue. Corrigez-vous à la grille : cochez uniquement ce qui figure réellement dans votre réponse.');
    }

    public function review(MockExamSession $session): View
    {
        $session->load(['mockExam.subject', 'mockExam.questions.chapter', 'answers']);

        return view('examens.correction', [
            'session' => $session,
            'examen' => $session->mockExam,
            'questions' => $session->mockExam->questions,
            'reponses' => $session->answers->keyBy('mock_exam_question_id'),
        ]);
    }

    /**
     * Auto-correction à la grille.
     *
     * L'exigence est explicite : on ne coche un attendu que s'il est écrit noir
     * sur blanc dans la copie. « J'y avais pensé » ne vaut aucun point à l'examen.
     */
    public function grade(Request $request, MockExamSession $session, MasteryCalculator $mastery): RedirectResponse
    {
        $grilles = $request->input('grille', []);
        $total = 0.0;

        foreach ($session->mockExam->questions as $question) {
            $coches = array_map('intval', array_keys(array_filter($grilles[$question->id] ?? [])));
            $attendus = $question->rubric ?? [];

            $points = 0.0;
            foreach ($coches as $i) {
                $points += (float) ($attendus[$i]['points'] ?? 0);
            }

            $points = min($points, (float) $question->points);
            $total += $points;

            MockExamAnswer::updateOrCreate(
                [
                    'mock_exam_session_id' => $session->id,
                    'mock_exam_question_id' => $question->id,
                ],
                ['rubric_check' => $coches, 'points_awarded' => $points]
            );
        }

        $session->update([
            'score' => round($total, 2),
            'max_score' => $session->mockExam->total_points,
        ]);

        StudyEvent::record('mock_finished', [
            'subject_id' => $session->mockExam->subject_id,
            'minutes' => (int) round($session->elapsed_sec / 60),
            'payload' => [
                'mock_exam_id' => $session->mockExam->id,
                'score' => $total,
                'mode' => $session->mode,
            ],
        ]);

        foreach ($session->mockExam->questions->pluck('chapter')->filter()->unique('id') as $chapitre) {
            $mastery->forChapter($chapitre);
        }

        $sur20 = $session->mockExam->total_points > 0
            ? round($total / (float) $session->mockExam->total_points * 20, 1)
            : 0;

        return back()->with('succes', "Note : {$sur20} / 20. Chaque attendu non coché est un point à aller chercher.");
    }
}