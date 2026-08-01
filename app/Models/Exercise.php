<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rubric' => 'array',
            'needs_diagram' => 'boolean',
        ];
    }

    public const ORIGINS = [
        'td' => 'TD',
        'devoir' => 'Devoir',
        'annale' => 'Annale',
        'genere' => 'Ciblé sur une lacune',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class)->latest();
    }

    protected function originLabel(): Attribute
    {
        return Attribute::get(fn () => self::ORIGINS[$this->origin] ?? $this->origin);
    }

    /** Nombre de points que vaut la grille d'attendus. */
    protected function rubricPoints(): Attribute
    {
        return Attribute::get(fn () => collect($this->rubric ?? [])->sum('points'));
    }
}