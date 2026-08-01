<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Flashcard extends Model
{
    protected $guarded = [];

    public const KINDS = [
        'definition' => 'Définition',
        'formule' => 'Formule',
        'methode' => 'Méthode',
        'piege' => 'Piège',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function gap(): BelongsTo
    {
        return $this->belongsTo(Gap::class);
    }

    public function state(): HasOne
    {
        return $this->hasOne(FlashcardState::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(FlashcardReview::class);
    }
}