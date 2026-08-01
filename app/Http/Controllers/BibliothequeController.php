<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BibliothequeController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->string('q'));

        $query = Resource::with('subject', 'chapter')
            ->when($request->filled('matiere'), function ($q) use ($request) {
                $q->whereHas('subject', fn ($s) => $s->where('slug', $request->string('matiere')));
            })
            ->when($request->filled('type'), fn ($q) => $q->where('kind', $request->string('type')))
            ->when($request->boolean('sans_corriges'), fn ($q) => $q->where('is_solution', false));

        if ($recherche !== '') {
            // Recherche sur le titre et sur le texte extrait des 120 PDF lisibles.
            $query->where(function ($q) use ($recherche) {
                $q->where('title', 'ilike', "%{$recherche}%")
                    ->orWhere('filename', 'ilike', "%{$recherche}%")
                    ->orWhere('text_content', 'ilike', "%{$recherche}%");
            });
        }

        $resources = $query->orderBy('subject_id')
            ->orderByRaw("array_position(array['cours','td','exercice','devoir','annale','copie','annexe']::text[], kind)")
            ->orderBy('title')
            ->paginate(40)
            ->withQueryString();

        return view('bibliotheque.index', [
            'resources' => $resources,
            'subjects' => Subject::orderBy('position')->get(),
            'kinds' => Resource::KINDS,
            'recherche' => $recherche,
            'total' => Resource::count(),
        ]);
    }

    public function show(Request $request, Resource $resource): View
    {
        $resource->load('subject', 'chapter');

        // Le texte n'est chargé que sur cette page, et seulement pour les extraits.
        $extraits = [];
        $terme = trim((string) $request->string('q'));

        if ($terme !== '' && $resource->has_text) {
            $texte = Resource::whereKey($resource->id)->value('text_content') ?? '';
            $extraits = $this->extraits($texte, $terme);
        }

        return view('bibliotheque.show', [
            'resource' => $resource,
            'extraits' => $extraits,
            'terme' => $terme,
            'voisins' => Resource::where('subject_id', $resource->subject_id)
                ->where('id', '!=', $resource->id)
                ->where('kind', $resource->kind)
                ->orderBy('title')
                ->limit(8)
                ->get(),
        ]);
    }

    /** Quelques lignes autour de chaque occurrence, pour situer sans ouvrir le PDF. */
    private function extraits(string $texte, string $terme, int $max = 5): array
    {
        $out = [];
        $offset = 0;

        while (count($out) < $max
            && ($pos = mb_stripos($texte, $terme, $offset)) !== false) {
            $debut = max(0, $pos - 160);
            $out[] = trim(mb_substr($texte, $debut, 380));
            $offset = $pos + mb_strlen($terme);
        }

        return $out;
    }
}