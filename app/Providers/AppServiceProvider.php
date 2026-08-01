<?php

namespace App\Providers;

use App\Models\FlashcardState;
use App\Models\Subject;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('fr');

        // Données de navigation présentes sur toutes les pages du layout.
        View::composer('layouts.app', function ($view) {
            $subjects = Subject::orderBy('position')->get();

            $view->with([
                'navSubjects' => $subjects,
                'navNextExam' => $subjects->whereNotNull('exam_at')
                    ->where('exam_at', '>=', now()->startOfDay())
                    ->sortBy('exam_at')
                    ->first(),
                'navDueCards' => FlashcardState::whereDate('due_on', '<=', today())->count(),
                'naveMasteredCount' => $subjects->where('is_transversal', false)
                    ->filter(fn (Subject $s) => $s->mastery >= 80)
                    ->count(),
            ]);
        });
    }
}