<?php

namespace App\Models;

use App\Models\StudyEvent;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une séance du cours suivi.
 *
 * Contrairement à une fiche, elle se lit dans l'ordre : la séance n suppose
 * la séance n−1 comprise, et rien d'autre.
 */
class Seance extends Model
{
    protected $guarded = [];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function precedente(): ?self
    {
        return static::where('subject_id', $this->subject_id)
            ->where('position', '<', $this->position)
            ->orderByDesc('position')
            ->first();
    }

    public function suivante(): ?self
    {
        return static::where('subject_id', $this->subject_id)
            ->where('position', '>', $this->position)
            ->orderBy('position')
            ->first();
    }

    /** La séance a-t-elle déjà été suivie ? */
    protected function suivie(): Attribute
    {
        return Attribute::get(fn () => StudyEvent::where('kind', 'seance_suivie')
            ->whereJsonContains('payload->seance_id', $this->id)
            ->exists());
    }

    /** Identifiants des séances déjà suivies, pour une matière. */
    public static function suiviesPour(int $subjectId): array
    {
        return StudyEvent::where('kind', 'seance_suivie')
            ->where('subject_id', $subjectId)
            ->get()
            ->pluck('payload.seance_id')
            ->filter()
            ->unique()
            ->all();
    }
}