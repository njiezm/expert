<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    protected $guarded = [];

    /** Le texte intégral n'est chargé que sur demande explicite. */
    protected $hidden = ['text_content'];

    protected function casts(): array
    {
        return [
            'is_scan' => 'boolean',
            'has_text' => 'boolean',
            'is_solution' => 'boolean',
        ];
    }

    public const KINDS = [
        'cours' => 'Cours',
        'td' => 'TD',
        'exercice' => 'Exercices',
        'devoir' => 'Devoir',
        'corrige' => 'Corrigé',
        'annale' => 'Annale',
        'copie' => 'Ma copie',
        'annexe' => 'Annexe',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /** URL publique du fichier, servi depuis public/pdfs. */
    protected function url(): Attribute
    {
        return Attribute::get(fn () => asset(
            implode('/', array_map(rawurlencode(...), explode('/', $this->relative_path)))
        ));
    }

    protected function kindLabel(): Attribute
    {
        return Attribute::get(fn () => self::KINDS[$this->kind] ?? ucfirst($this->kind));
    }

    protected function sizeHuman(): Attribute
    {
        return Attribute::get(function () {
            $mb = $this->size_bytes / 1048576;

            return $mb >= 1
                ? number_format($mb, 1, ',', ' ').' Mo'
                : number_format($this->size_bytes / 1024, 0, ',', ' ').' Ko';
        });
    }

    protected function isViewable(): Attribute
    {
        return Attribute::get(fn () => strtolower($this->extension) === 'pdf');
    }
}