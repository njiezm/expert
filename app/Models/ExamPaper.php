<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamPaper extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sat_on' => 'date',
            'grade' => 'decimal:2',
            'max_grade' => 'decimal:2',
            'score_breakdown' => 'array',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function gaps(): HasMany
    {
        return $this->hasMany(Gap::class);
    }

    protected function gradeRatio(): Attribute
    {
        return Attribute::get(fn () => $this->max_grade > 0
            ? round(((float) $this->grade / (float) $this->max_grade) * 100)
            : 0);
    }
}