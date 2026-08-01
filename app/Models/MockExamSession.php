<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockExamSession extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'was_timed_out' => 'boolean',
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
        ];
    }

    public const MODES = [
        'amphi' => 'Amphi — Besançon, en journée',
        'distance_nuit' => 'À distance — épreuve nocturne',
    ];

    public function mockExam(): BelongsTo
    {
        return $this->belongsTo(MockExam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(MockExamAnswer::class);
    }

    public function isRunning(): bool
    {
        return $this->finished_at === null;
    }

    /** Secondes restantes, bornées à zéro. */
    protected function remainingSec(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->isRunning()) {
                return 0;
            }

            return max(0, $this->allowed_sec - $this->started_at->diffInSeconds(now()));
        });
    }

    protected function modeLabel(): Attribute
    {
        return Attribute::get(fn () => self::MODES[$this->mode] ?? $this->mode);
    }
}