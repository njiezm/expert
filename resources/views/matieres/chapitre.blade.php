@extends('layouts.app')

@section('titre', $chapter->title)
@section('sous-titre', $subject->code.' · '.$subject->name)

@section('contenu')

    @php $m = $chapter->progress?->mastery ?? 0; @endphp

    <div class="mb-5 flex flex-wrap items-center gap-3">
        <a href="{{ route('matieres.show', $subject) }}" class="btn btn-fantome !px-2.5 !py-1.5">
            <x-icone name="arrow-l" class="size-4" />
        </a>
        <div class="flex flex-1 items-center gap-3">
            <x-jauge :value="$m" :color="$subject->color" class="max-w-xs flex-1" height="0.4rem" />
            <span class="text-sm font-semibold tabulaire texte-fort">{{ $m }} %</span>
        </div>
        @if ($precedent)
            <a href="{{ route('chapitres.show', [$subject, $precedent]) }}" class="btn btn-fantome text-xs">Précédent</a>
        @endif
        @if ($suivant)
            <a href="{{ route('chapitres.show', [$subject, $suivant]) }}" class="btn btn-fantome text-xs">Suivant</a>
        @endif
    </div>

    @if ($chapter->summary)
        <p class="carte mb-6 px-4 py-3 text-sm leading-relaxed texte-doux">{{ $chapter->summary }}</p>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1.7fr_1fr]">

        <div class="space-y-6">

            {{-- Lacunes du chapitre : mises en tête, c'est là qu'il faut viser --}}
            @if ($chapter->gaps->count())
                <section class="carte overflow-hidden">
                    <div class="px-4 py-3" style="background: color-mix(in oklab, var(--color-lacune) 10%, transparent)">
                        <h2 class="text-sm font-semibold texte-fort">Ce qui a été raté ici</h2>
                    </div>
                    <div class="divide-y" style="border-color: var(--bordure)">
                        @foreach ($chapter->gaps as $lacune)
                            <div class="px-4 py-3">
                                <div class="flex items-start gap-2">
                                    <span class="mt-1 size-1.5 shrink-0 rounded-full"
                                          style="background: {{ $lacune->status === 'maitrisee' ? 'var(--color-acquis)' : 'var(--color-lacune)' }}"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium texte-fort">{{ $lacune->title }}</p>
                                        @if ($lacune->evidence)
                                            <p class="mt-1 text-xs italic texte-faible">Annotation : « {{ $lacune->evidence }} »</p>
                                        @endif
                                        @if ($lacune->explanation)
                                            <p class="mt-1.5 text-xs leading-relaxed texte-doux">{{ $lacune->explanation }}</p>
                                        @endif
                                        @if ($lacune->remedy)
                                            <p class="mt-1.5 text-xs leading-relaxed" style="color: var(--color-acquis-fort)">
                                                → {{ $lacune->remedy }}
                                            </p>
                                        @endif
                                    </div>
                                    @php $fermee = $lacune->status === 'maitrisee'; @endphp
                                    <form method="POST" action="{{ route('lacunes.statut', $lacune) }}" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $fermee ? 'ouverte' : 'maitrisee' }}">
                                        <button class="btn btn-fantome whitespace-nowrap text-xs"
                                                @if ($fermee) style="border-color: var(--color-acquis); color: var(--color-acquis-fort)" @endif>
                                            <x-icone :name="$fermee ? 'refresh' : 'check'" class="size-3.5" />
                                            {{ $fermee ? 'Rouvrir' : 'Refermer' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Fiches de cours --}}
            <section>
                <h2 class="mb-3 text-sm font-semibold texte-fort">Fiches de cours</h2>
                @forelse ($chapter->lessons as $fiche)
                    <a href="{{ route('cours.show', $fiche) }}"
                       class="carte mb-2 flex items-center gap-3 px-4 py-3 transition-colors hover:bg-[var(--surface-survol)]">
                        <span class="w-6 shrink-0 text-xs tabulaire texte-faible">{{ $loop->iteration }}</span>
                        <span class="min-w-0 flex-1 truncate text-sm texte-fort">{{ $fiche->title }}</span>
                        <span class="shrink-0 text-[11px] tabulaire texte-faible">{{ $fiche->est_minutes }} min</span>
                        <x-icone name="arrow-r" class="size-4 shrink-0 texte-faible" />
                    </a>
                @empty
                    <x-vide icon="book" titre="Fiches à générer pour ce chapitre" />
                @endforelse
            </section>

            {{-- Exercices --}}
            <section>
                <h2 class="mb-3 text-sm font-semibold texte-fort">Exercices</h2>
                @forelse ($chapter->exercises as $exo)
                    <a href="{{ route('exercices.show', $exo) }}"
                       class="carte mb-2 flex items-center gap-3 px-4 py-3 transition-colors hover:bg-[var(--surface-survol)]">
                        <span class="puce">{{ $exo->origin_label }}</span>
                        <span class="min-w-0 flex-1 truncate text-sm texte-fort">{{ $exo->title }}</span>
                        <span class="shrink-0 text-[11px] tabulaire texte-faible">{{ $exo->est_minutes }} min</span>
                    </a>
                @empty
                    <x-vide icon="steps" titre="Exercices à générer pour ce chapitre" />
                @endforelse
            </section>
        </div>

        {{-- Colonne de droite --}}
        <div class="space-y-6">

            <section class="carte p-4">
                <h2 class="text-sm font-semibold texte-fort">Cartes mémoire</h2>
                <p class="mt-1 text-xs texte-doux">
                    {{ $cartes->count() }} carte{{ $cartes->count() > 1 ? 's' : '' }} ·
                    {{ $cartes->filter(fn ($c) => $c->state?->isMature())->count() }} acquise(s)
                </p>
                @if ($cartes->count())
                    <a href="{{ route('drill.session', ['matiere' => $subject->id]) }}" class="btn btn-doux mt-3 w-full text-xs">
                        <x-icone name="play" class="size-3.5" /> Réviser
                    </a>
                @endif
            </section>

            @if ($chapter->resources->count())
                <section class="carte p-4">
                    <h2 class="text-sm font-semibold texte-fort">Documents rattachés</h2>
                    <div class="mt-3 space-y-1.5">
                        @foreach ($chapter->resources as $doc)
                            <a href="{{ route('bibliotheque.show', $doc) }}"
                               class="flex items-center gap-2 rounded-md px-2 py-1.5 text-xs hover:bg-[var(--surface-survol)]">
                                <x-icone name="file" class="size-3.5 shrink-0 texte-faible" />
                                <span class="min-w-0 flex-1 truncate texte-doux">{{ $doc->title }}</span>
                                <span class="puce shrink-0">{{ $doc->kind_label }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

@endsection