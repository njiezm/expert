<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyBlock extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['day' => 'date'];
    }

    public const ACTIVITIES = [
        'cours' => 'Cours digeste',
        'drill' => 'Drill mémoire',
        'exercice' => 'Exercices',
        'examen_blanc' => 'Examen blanc',
        'revision' => 'Révision ciblée',
    ];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(AvailabilitySlot::class, 'availability_slot_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    protected function activityLabel(): Attribute
    {
        return Attribute::get(fn () => self::ACTIVITIES[$this->activity] ?? $this->activity);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('day', today());
    }
}