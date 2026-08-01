<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\StudyEvent;
use App\Services\MasteryCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Lesson $lesson): View
    {
        $lesson->load('chapter.subject');

        $frere = $lesson->chapter->lessons;

        return view('cours.show', [
            'lesson' => $lesson,
            'chapter' => $lesson->chapter,
            'subject' => $lesson->chapter->subject,
            'precedent' => $frere->where('position', '<', $lesson->position)->last(),
            'suivant' => $frere->where('position', '>', $lesson->position)->first(),
            'dejaLue' => StudyEvent::where('kind', 'lesson_read')
                ->where('chapter_id', $lesson->chapter_id)
                ->whereJsonContains('payload->lesson_id', $lesson->id)
                ->exists(),
        ]);
    }

    public function markRead(Request $request, Lesson $lesson, MasteryCalculator $mastery): RedirectResponse
    {
        StudyEvent::record('lesson_read', [
            'chapter_id' => $lesson->chapter_id,
            'subject_id' => $lesson->chapter->subject_id,
            'minutes' => (int) $request->integer('minutes', $lesson->est_minutes),
            'payload' => ['lesson_id' => $lesson->id],
        ]);

        $mastery->forChapter($lesson->chapter);

        $suivant = $lesson->chapter->lessons()->where('position', '>', $lesson->position)->first();

        return $suivant
            ? redirect()->route('cours.show', $suivant)
            : redirect()
                ->route('chapitres.show', [$lesson->chapter->subject, $lesson->chapter])
                ->with('succes', 'Chapitre parcouru. Place aux cartes et aux exercices — la lecture seule ne fait pas la note.');
    }
}