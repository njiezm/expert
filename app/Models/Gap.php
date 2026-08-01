<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gap extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public const KINDS = [
        'contenu' => 'Connaissance',
        'methode' => 'Méthode',
        'rigueur' => 'Rigueur de rédaction',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function examPaper(): BelongsTo
    {
        return $this->belongsTo(ExamPaper::class);
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    protected function kindLabel(): Attribute
    {
        return Attribute::get(fn () => self::KINDS[$this->kind] ?? $this->kind);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', '!=', 'maitrisee');
    }
}