<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

class Subject extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'exam_at' => 'datetime',
            'initial_grade' => 'decimal:2',
            'is_transversal' => 'boolean',
        ];
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    /**
     * Les séances du cours suivi.
     *
     * Nécessaire au-delà du confort : la route imbriquée
     * `/matieres/{subject:slug}/cours/suivre/{seance:slug}` amène Laravel à
     * restreindre la séance au sujet parent, ce qu'il fait via cette relation.
     */
    public function seances(): HasMany
    {
        return $this->hasMany(Seance::class)->orderBy('position');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class)->orderBy('position');
    }

    public function examPapers(): HasMany
    {
        return $this->hasMany(ExamPaper::class);
    }

    public function gaps(): HasMany
    {
        return $this->hasMany(Gap::class)->orderByDesc('severity');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    public function mockExams(): HasMany
    {
        return $this->hasMany(MockExam::class)->orderBy('position');
    }

    public function studyBlocks(): HasMany
    {
        return $this->hasMany(StudyBlock::class);
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Chapter::class);
    }

    public function flashcards(): HasManyThrough
    {
        return $this->hasManyThrough(Flashcard::class, Chapter::class);
    }

    /** Jours restants avant l'épreuve (négatif une fois passée). */
    protected function daysUntilExam(): Attribute
    {
        return Attribute::get(fn () => $this->exam_at
            ? (int) Carbon::today()->diffInDays($this->exam_at->copy()->startOfDay(), false)
            : null);
    }

    /** Maîtrise moyenne, pondérée par le poids de chaque chapitre au barème. */
    protected function mastery(): Attribute
    {
        return Attribute::get(function () {
            $rows = $this->chapters()->with('progress')->get();
            $weight = $rows->sum('exam_weight');

            if ($weight === 0) {
                return 0;
            }

            $score = $rows->sum(fn (Chapter $c) => ($c->progress?->mastery ?? 0) * $c->exam_weight);

            return (int) round($score / $weight);
        });
    }

    /** Le compte à rebours ne suffit pas : on veut l'urgence relative. */
    protected function urgency(): Attribute
    {
        return Attribute::get(function () {
            $days = max($this->days_until_exam ?? 30, 1);
            $missing = 100 - $this->mastery;

            return (int) round(min(100, $missing * (20 / $days)));
        });
    }

    public function scopeExamOrder($query)
    {
        return $query->orderByRaw('exam_at is null')->orderBy('exam_at');
    }
}