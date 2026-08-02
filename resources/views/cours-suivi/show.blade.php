@extends('layouts.app')

@section('titre', $seance->title)
@section('sous-titre', $subject->code.' · séance '.$numero.' sur '.$seances->count())

@section('actions-entete')
    <x-lire cible="seance-cours" label="Écouter" :vitesse="true" />
@endsection

@section('contenu')

    <div class="grid gap-6 lg:grid-cols-[1fr_15rem]">

        {{-- ==================== Le cours ==================== --}}
        <article class="min-w-0">

            <div class="mb-5 flex flex-wrap items-center gap-2">
                <a href="{{ route('cours-suivi.index', $subject) }}" class="btn btn-fantome !px-2.5 !py-1.5">
                    <x-icone name="arrow-l" class="size-4" />
                </a>
                <span class="puce" style="border-color: {{ $subject->color }}; color: {{ $subject->color }}">
                    Séance {{ $seance->position }}
                </span>
                <span class="puce">{{ $seance->duree_min }} min</span>
                @if ($dejaSuivie)
                    <span class="puce" style="border-color: var(--color-acquis); color: var(--color-acquis-fort)">
                        <x-icone name="check" class="size-3" /> Suivie
                    </span>
                @endif
            </div>

            {{-- Prérequis --}}
            @if ($seance->prerequis)
                <div class="carte mb-5 border-l-2 px-5 py-3.5" style="border-left-color: var(--color-info)">
                    <p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.1em]"
                       style="color: var(--color-info)">Avant de commencer</p>
                    <div class="prose-cours !text-sm">{!! Str::markdown($seance->prerequis) !!}</div>
                </div>
            @endif

            <div id="seance-cours">

                {{-- Introduction : « aujourd'hui, on va voir… » --}}
                @if ($seance->intro)
                    <div class="carte-haute mb-6 px-6 py-5">
                        <div class="prose-cours">{!! Str::markdown($seance->intro) !!}</div>
                    </div>
                @endif

                {{-- Le corps du cours --}}
                <div class="carte px-6 py-6 sm:px-8 sm:py-7">
                    <div class="prose-cours">{!! Str::markdown($seance->body) !!}</div>
                </div>

                {{-- Ce qu'il faut retenir --}}
                @if ($seance->recap)
                    <section class="mt-6">
                        <div class="mb-2.5 flex items-center gap-2">
                            <span class="grid size-6 shrink-0 place-items-center rounded-md"
                                  style="background: var(--accent-doux)">
                                <x-icone name="target" class="size-3.5" style="color: var(--accent)" />
                            </span>
                            <h2 class="text-[13px] font-semibold uppercase tracking-[0.08em]"
                                style="color: var(--accent)">Ce qu'il faut retenir</h2>
                        </div>
                        <div class="carte border-l-2 px-6 py-5" style="border-left-color: var(--accent)">
                            <div class="prose-cours">{!! Str::markdown($seance->recap) !!}</div>
                        </div>
                    </section>
                @endif
            </div>

            {{-- Navigation --}}
            <div class="mt-8 flex flex-wrap items-center gap-3 border-t bord pt-6">
                @if ($precedente)
                    <a href="{{ route('cours-suivi.show', [$subject, $precedente]) }}" class="btn btn-fantome">
                        <x-icone name="arrow-l" class="size-4" /> Séance {{ $precedente->position }}
                    </a>
                @endif

                @if ($dejaSuivie)
                    <form method="POST" action="{{ route('cours-suivi.reprendre', [$subject, $seance]) }}">
                        @csrf
                        <button class="btn btn-fantome text-xs">
                            <x-icone name="refresh" class="size-3.5" /> Marquer à revoir
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('cours-suivi.terminer', [$subject, $seance]) }}" class="ml-auto">
                    @csrf
                    <input type="hidden" name="minutes" value="{{ $seance->duree_min }}">
                    <button class="btn btn-accent">
                        <x-icone name="check" class="size-4" />
                        {{ $suivante ? 'Compris — séance suivante' : 'Compris — terminer le cours' }}
                    </button>
                </form>
            </div>

            @if ($seance->chapter)
                <p class="mt-5 text-center text-[11px] leading-relaxed texte-faible">
                    Cette séance couvre le chapitre
                    <a href="{{ route('chapitres.show', [$subject, $seance->chapter]) }}"
                       class="underline">{{ $seance->chapter->title }}</a>.
                    Ses cartes et ses exercices vous y attendent.
                </p>
            @endif
        </article>

        {{-- ==================== Sommaire latéral ==================== --}}
        <aside class="hidden lg:block">
            <div class="sticky top-20">
                <p class="mb-2.5 px-2 text-[10px] font-semibold uppercase tracking-[0.12em] texte-faible">
                    Le cours · {{ count($suivies) }}/{{ $seances->count() }}
                </p>

                <nav class="space-y-0.5">
                    @foreach ($seances as $s)
                        @php
                            $faite = in_array($s->id, $suivies, true);
                            $courante = $s->id === $seance->id;
                        @endphp
                        <a href="{{ route('cours-suivi.show', [$subject, $s]) }}"
                           class="flex items-start gap-2 rounded-md px-2 py-1.5 text-[11px] leading-snug transition-colors hover:bg-[var(--surface-survol)]"
                           style="{{ $courante
                               ? 'background: var(--accent-doux); color: var(--texte-fort); font-weight: 600'
                               : ($faite ? 'color: var(--texte-faible)' : 'color: var(--texte-doux)') }}">
                            <span class="mt-px w-4 shrink-0 text-right tabulaire">
                                @if ($faite)
                                    <x-icone name="check" class="inline size-3"
                                             style="color: var(--color-acquis-fort)" />
                                @else
                                    {{ $s->position }}
                                @endif
                            </span>
                            <span class="min-w-0 flex-1">{{ $s->title }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </aside>
    </div>

@endsection