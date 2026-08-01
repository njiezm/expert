@extends('layouts.app')

@section('titre', 'Exercices')
@section('sous-titre', 'TD, devoirs et annales — corrigés progressivement, jamais d’un bloc')

@section('contenu')

    {{-- Filtres --}}
    <form method="GET" class="mb-5 flex flex-wrap items-center gap-2">
        <a href="{{ route('exercices.index') }}"
           class="btn {{ ! $subject ? 'btn-accent' : 'btn-fantome' }} text-xs">Toutes</a>
        @foreach ($subjects as $s)
            <a href="{{ route('exercices.index', ['matiere' => $s->slug]) }}"
               class="btn {{ $subject?->id === $s->id ? 'btn-accent' : 'btn-fantome' }} text-xs">
                <span class="size-1.5 rounded-full" style="background: {{ $s->color }}"></span>
                {{ $s->code }}
            </a>
        @endforeach
    </form>

    @forelse ($exercices as $code => $liste)
        <section class="mb-6">
            <h2 class="mb-3 text-sm font-semibold texte-fort">
                {{ $code }} <span class="ml-1 texte-faible">({{ $liste->count() }})</span>
            </h2>

            @foreach ($liste as $exo)
                @php
                    $derniere = $exo->attempts->first();
                    $score = $derniere?->self_score;
                @endphp
                <a href="{{ route('exercices.show', $exo) }}"
                   class="carte mb-2 flex items-center gap-3 px-4 py-3 transition-colors hover:bg-[var(--surface-survol)]">
                    <span class="puce shrink-0">{{ $exo->origin_label }}</span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm texte-fort">{{ $exo->title }}</p>
                        @if ($exo->chapter)
                            <p class="truncate text-[11px] texte-faible">{{ $exo->chapter->title }}</p>
                        @endif
                    </div>

                    @if ($score !== null)
                        <span class="shrink-0 text-xs font-semibold tabulaire"
                              style="color: {{ $score >= 80 ? 'var(--color-acquis-fort)' : ($score >= 50 ? 'var(--accent)' : 'var(--color-lacune-fort)') }}">
                            {{ $score }} %
                        </span>
                    @endif

                    <span class="shrink-0 text-[11px] tabulaire texte-faible">{{ $exo->est_minutes }} min</span>
                </a>
            @endforeach
        </section>
    @empty
        <x-vide icon="steps" titre="Exercices en cours de génération">
            Les TD, devoirs et annales sont indexés dans la bibliothèque ; leur transformation en
            exercices à correction progressive se fait matière par matière.
        </x-vide>
    @endforelse

@endsection