<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AvailabilitySlot extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'day' => 'date',
            'is_locked' => 'boolean',
        ];
    }

    public const LABELS = [
        'soiree' => 'Soirée',
        'weekend' => 'Week-end',
        'teletravail' => 'Télétravail',
        'conge' => 'Congé',
        'examen' => 'Épreuve',
    ];

    public function blocks(): HasMany
    {
        return $this->hasMany(StudyBlock::class)->orderBy('position');
    }

    protected function labelText(): Attribute
    {
        return Attribute::get(fn () => self::LABELS[$this->label] ?? $this->label);
    }

    /** Minutes encore libres après ce que le planning a déjà posé. */
    protected function freeMinutes(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->minutes - $this->blocks->sum('planned_minutes')));
    }
}