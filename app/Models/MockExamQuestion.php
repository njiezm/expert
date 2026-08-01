<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockExamQuestion extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rubric' => 'array',
            'points' => 'decimal:2',
        ];
    }

    public function mockExam(): BelongsTo
    {
        return $this->belongsTo(MockExam::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(MockExamAnswer::class);
    }
}