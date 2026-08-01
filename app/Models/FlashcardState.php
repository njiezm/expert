<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashcardState extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'due_on' => 'date',
            'last_reviewed_at' => 'datetime',
            'ease_factor' => 'decimal:2',
        ];
    }

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }

    /** Une carte est « acquise » à partir de trois succès et 7 jours d'intervalle. */
    public function isMature(): bool
    {
        return $this->repetitions >= 3 && $this->interval_days >= 7;
    }
}