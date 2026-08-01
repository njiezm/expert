<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockExam extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['total_points' => 'decimal:2'];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function sourceResource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'source_resource_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(MockExamQuestion::class)->orderBy('position');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(MockExamSession::class)->latest('started_at');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function bestScore(): ?float
    {
        return $this->sessions()->whereNotNull('score')->max('score');
    }
}