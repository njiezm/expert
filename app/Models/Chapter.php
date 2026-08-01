<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chapter extends Model
{
    protected $guarded = [];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class)->orderBy('position');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class)->orderBy('position');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class)->orderBy('position');
    }

    public function gaps(): HasMany
    {
        return $this->hasMany(Gap::class)->orderByDesc('severity');
    }

    public function progress(): HasOne
    {
        return $this->hasOne(ChapterProgress::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}