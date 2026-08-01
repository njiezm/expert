<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['source_refs' => 'array'];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Les 5 temps de la fiche, dans l'ordre de lecture imposé. */
    public function sections(): array
    {
        return array_filter([
            ['key' => 'intuition', 'label' => "L'intuition", 'icon' => 'bulb', 'body' => $this->intuition],
            ['key' => 'formalism', 'label' => 'Le formalisme exact', 'icon' => 'sigma', 'body' => $this->formalism],
            ['key' => 'worked_example', 'label' => 'Exemple déroulé', 'icon' => 'steps', 'body' => $this->worked_example],
            ['key' => 'pitfalls', 'label' => 'Les pièges', 'icon' => 'alert', 'body' => $this->pitfalls],
            ['key' => 'examiner_expects', 'label' => 'Ce que le correcteur attend', 'icon' => 'target', 'body' => $this->examiner_expects],
        ], fn ($s) => filled($s['body']));
    }
}