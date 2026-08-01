@extends('layouts.app')

@section('titre', $exercice->title)
@section('sous-titre', $exercice->subject->code.($exercice->chapter ? ' · '.$exercice->chapter->title : ''))

@section('contenu')

    <div class="mx-auto max-w-3xl">

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('exercices.index', ['matiere' => $exercice->subject->slug]) }}"
               class="btn btn-fantome !px-2.5 !py-1.5">
                <x-icone name="arrow-l" class="size-4" />
            </a>
            <span class="puce">{{ $exercice->origin_label }}</span>
            <span class="puce">{{ $exercice->est_minutes }} min</span>
            @if ($exercice->resource)
                <a href="{{ route('bibliotheque.show', $exercice->resource) }}" class="puce hover:underline">
                    <x-icone name="file" class="size-3" /> Source
                </a>
            @endif
        </div>

        {{-- Énoncé --}}
        <section class="carte-haute mb-5 px-6 py-5">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-[11px] font-semibold uppercase tracking-[0.1em] texte-faible">Énoncé</h2>
                <x-lire cible="enonce-exo" :vitesse="true" />
            </div>
            <div id="enonce-exo" class="prose-cours">{!! Str::markdown($exercice->statement) !!}</div>
        </section>

        <form method="POST" action="{{ route('exercices.soumettre', $exercice) }}">
            @csrf
            <input type="hidden" name="reveal_level" value="0" data-niveau-devoile>
            <input type="hidden" name="duree" value="0" id="duree-exo">

            {{-- Diagramme de classes, quand l'exercice l'exige --}}
            @if ($exercice->needs_diagram)
                <section class="mb-5">
                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.1em] texte-faible">
                        Votre diagramme de classes
                    </label>
                    <x-schema name="diagram" :value="$derniere?->diagram" />
                </section>
            @endif

            {{-- Rédaction --}}
            <section class="mb-5">
                <label for="answer" class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.1em] texte-faible">
                    {{ $exercice->needs_diagram
                        ? 'Justification — une ligne par patron identifié'
                        : 'Votre réponse — rédigez comme sur une copie' }}
                </label>
                <textarea id="answer" name="answer" rows="10"
                          class="champ font-mono text-[13px] leading-relaxed"
                          data-brouillon="exo-{{ $exercice->id }}" data-auto-hauteur
                          placeholder="Posez les hypothèses, nommez les règles utilisées, chiffrez la complexité. Une seule réponse : ne proposez pas deux formalisations."></textarea>
            </section>

            {{-- Paliers de dévoilement --}}
            <section class="mb-5 flex flex-wrap gap-2">
                @if ($exercice->hint)
                    <button type="button" data-devoiler="palier-indice" data-niveau="1" class="btn btn-fantome text-xs">
                        <x-icone name="bulb" class="size-3.5" /> Indice
                    </button>
                @endif
                @if ($exercice->method)
                    <button type="button" data-devoiler="palier-methode" data-niveau="2" class="btn btn-fantome text-xs">
                        <x-icone name="steps" class="size-3.5" /> Méthode (sans le résultat)
                    </button>
                @endif
                @if ($exercice->solution)
                    <button type="button" data-devoiler="palier-solution" data-niveau="3" class="btn btn-fantome text-xs"
                            style="border-color: var(--color-lacune)">
                        <x-icone name="eye" class="size-3.5" /> Solution — plafonne le crédit à 40 %
                    </button>
                @endif
            </section>

            @if ($exercice->hint)
                <section id="palier-indice" hidden class="carte mb-3 border-l-2 px-5 py-4" style="border-left-color: var(--accent)">
                    <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-[0.1em]" style="color: var(--accent)">Indice</p>
                    <div class="prose-cours !text-sm">{!! Str::markdown($exercice->hint) !!}</div>
                </section>
            @endif

            @if ($exercice->method)
                <section id="palier-methode" hidden class="carte mb-3 border-l-2 px-5 py-4" style="border-left-color: var(--color-info)">
                    <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-[0.1em]" style="color: var(--color-info)">Méthode</p>
                    <div class="prose-cours !text-sm">{!! Str::markdown($exercice->method) !!}</div>
                </section>
            @endif

            @if ($exercice->solution)
                <section id="palier-solution" hidden class="carte mb-3 border-l-2 px-5 py-4" style="border-left-color: var(--color-lacune)">
                    <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-[0.1em]" style="color: var(--color-lacune-fort)">Solution</p>
                    <div class="prose-cours !text-sm">{!! Str::markdown($exercice->solution) !!}</div>
                </section>
            @endif

            {{-- Grille d'attendus : le cœur du remède à la faiblesse de rédaction --}}
            @if ($exercice->rubric)
                <section class="carte mb-5 overflow-hidden">
                    <div class="px-5 py-3.5" style="background: var(--accent-doux)">
                        <div class="flex items-center gap-2">
                            <x-icone name="target" class="size-4" style="color: var(--accent)" />
                            <h2 class="text-sm font-semibold texte-fort">Ce que le correcteur attend</h2>
                        </div>
                        <p class="mt-1.5 text-xs leading-relaxed texte-doux">
                            Ne cochez que ce qui figure <strong class="texte-fort">littéralement</strong> dans votre réponse.
                            « J’y avais pensé » ne vaut aucun point à l’examen.
                        </p>
                    </div>

                    <div class="divide-y" style="border-color: var(--bordure)">
                        @foreach ($exercice->rubric as $i => $attendu)
                            <label class="flex cursor-pointer items-start gap-3 px-5 py-3 hover:bg-[var(--surface-survol)]">
                                <input type="checkbox" name="rubric[{{ $i }}]" value="1"
                                       class="mt-0.5 size-4 shrink-0 rounded" style="accent-color: var(--accent)">
                                <span class="flex-1 text-sm leading-relaxed texte-doux">{{ $attendu['label'] }}</span>
                                <span class="shrink-0 text-xs tabulaire texte-faible">{{ $attendu['points'] }} pt</span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endif

            <button type="submit" class="btn btn-accent w-full">Valider la tentative</button>
        </form>

        {{-- Historique --}}
        @if ($tentatives->count())
            <section class="mt-8">
                <h2 class="mb-3 text-sm font-semibold texte-fort">Tentatives précédentes</h2>
                @foreach ($tentatives as $t)
                    <div class="carte mb-2 flex items-center gap-3 px-4 py-2.5 text-xs">
                        <span class="tabulaire texte-faible">{{ $t->created_at->translatedFormat('d/m à H\hi') }}</span>
                        <span class="puce">
                            {{ ['Sans aide', 'Avec indice', 'Avec méthode', 'Solution vue'][$t->reveal_level] }}
                        </span>
                        <span class="ml-auto font-semibold tabulaire"
                              style="color: {{ $t->self_score >= 80 ? 'var(--color-acquis-fort)' : ($t->self_score >= 50 ? 'var(--accent)' : 'var(--color-lacune-fort)') }}">
                            {{ $t->self_score }} %
                        </span>
                    </div>
                @endforeach
            </section>
        @endif
    </div>

    @push('scripts')
        <script>
            // Temps réellement passé sur l'exercice, pour distinguer une réponse
            // réfléchie d'un survol du corrigé.
            (function () {
                const debut = Date.now();
                const champ = document.getElementById('duree-exo');
                champ?.form?.addEventListener('submit', () => {
                    champ.value = Math.round((Date.now() - debut) / 1000);
                });
            })();
        </script>
    @endpush

@endsection