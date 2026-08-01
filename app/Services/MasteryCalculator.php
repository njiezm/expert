<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\ChapterProgress;
use Illuminate\Support\Facades\DB;

/**
 * Calcule la maîtrise d'un chapitre, de 0 à 100.
 *
 * La maîtrise n'est pas « j'ai lu la fiche ». Elle combine quatre signaux, dont
 * trois exigent une production active. Lire ne vaut que 15 % — c'est délibéré :
 * la relecture donne un sentiment de maîtrise que les copies ont démenti.
 */
class MasteryCalculator
{
    private const WEIGHTS = [
        'lecture' => 0.15,   // fiches parcourues
        'memoire' => 0.30,   // cartes arrivées à maturité
        'pratique' => 0.35,  // exercices réussis sans dévoiler la solution
        'lacunes' => 0.20,   // lacunes refermées
    ];

    public function forChapter(Chapter $chapter): ChapterProgress
    {
        $lessonsTotal = $chapter->lessons()->count();
        $lessonsDone = DB::table('study_events')
            ->where('chapter_id', $chapter->id)
            ->where('kind', 'lesson_read')
            ->distinct()
            ->count(DB::raw("payload->>'lesson_id'"));

        $cardsTotal = $chapter->flashcards()->count();
        $cardsMature = DB::table('flashcards')
            ->join('flashcard_states', 'flashcard_states.flashcard_id', '=', 'flashcards.id')
            ->where('flashcards.chapter_id', $chapter->id)
            ->where('flashcard_states.repetitions', '>=', config('meridien.sm2.mature_after'))
            ->where('flashcard_states.interval_days', '>=', 7)
            ->count();

        $exercisesTotal = $chapter->exercises()->count();

        // Un exercice ne compte que s'il a été traité sans ouvrir la solution.
        $exercisesDone = DB::table('exercises')
            ->join('exercise_attempts', 'exercise_attempts.exercise_id', '=', 'exercises.id')
            ->where('exercises.chapter_id', $chapter->id)
            ->whereNotNull('exercise_attempts.completed_at')
            ->where('exercise_attempts.reveal_level', '<', 3)
            ->where('exercise_attempts.self_score', '>=', 60)
            ->distinct()
            ->count('exercises.id');

        $gapsTotal = $chapter->gaps()->count();
        $gapsOpen = $chapter->gaps()->open()->count();

        $ratio = fn (int $done, int $total) => $total > 0 ? min(1, $done / $total) : 0.0;

        // Sans lacune identifiée sur le chapitre, la composante est neutre (pleine).
        $lacunes = $gapsTotal > 0 ? 1 - ($gapsOpen / $gapsTotal) : 1.0;

        $mastery = (int) round(100 * (
            self::WEIGHTS['lecture'] * $ratio($lessonsDone, $lessonsTotal)
            + self::WEIGHTS['memoire'] * $ratio($cardsMature, $cardsTotal)
            + self::WEIGHTS['pratique'] * $ratio($exercisesDone, $exercisesTotal)
            + self::WEIGHTS['lacunes'] * $lacunes
        ));

        $minutes = (int) DB::table('study_events')
            ->where('chapter_id', $chapter->id)
            ->sum('minutes');

        return ChapterProgress::updateOrCreate(
            ['chapter_id' => $chapter->id],
            [
                'mastery' => $mastery,
                'lessons_done' => $lessonsDone,
                'lessons_total' => $lessonsTotal,
                'cards_mature' => $cardsMature,
                'cards_total' => $cardsTotal,
                'exercises_done' => $exercisesDone,
                'exercises_total' => $exercisesTotal,
                'gaps_open' => $gapsOpen,
                'minutes_spent' => $minutes,
                'last_touched_at' => now(),
            ]
        );
    }

    /** Recalcule tout : appelé après ingestion, génération de contenu ou drill. */
    public function refreshAll(): int
    {
        $n = 0;

        Chapter::with(['lessons', 'flashcards', 'exercises', 'gaps'])
            ->chunk(50, function ($chapters) use (&$n) {
                foreach ($chapters as $chapter) {
                    $this->forChapter($chapter);
                    $n++;
                }
            });

        return $n;
    }
}