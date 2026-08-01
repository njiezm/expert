@extends('layouts.app')

@section('titre', $subject->name)
@section('sous-titre')
    @if ($subject->exam_at)
        Épreuve le {{ $subject->exam_at->translatedFormat('l j F') }},
        {{ $subject->exam_at->format('H\hi') }}–{{ $subject->exam_at->copy()->addMinutes($subject->exam_duration_min)->format('H\hi') }}
        · J−{{ max(0, $subject->days_until_exam) }}
    @else
        {{ $subject->tagline }}
    @endif
@endsection

@section('contenu')

    {{-- Bandeau matière --}}
    <section class="carte-haute mb-6 overflow-hidden">
        <div class="h-1" style="background: {{ $subject->color }}"></div>
        <div class="grid gap-5 p-5 sm:grid-cols-[1fr_auto] sm:items-end">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="puce" style="border-color: {{ $subject->color }}; color: {{ $subject->color }}">
                        {{ $subject->code }}
                    </span>
                    @if ($subject->initial_grade !== null)
                        <span class="puce" style="border-color: var(--color-lacune); color: var(--color-lacune-fort)">
                            Session 1 : {{ rtrim(rtrim(number_format((float) $subject->initial_grade, 2, ',', ''), '0'), ',') }}/20
                        </span>
                    @endif
                </div>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed texte-doux">{{ $subject->tagline }}</p>

                <div class="mt-4 flex items-center gap-3">
                    <x-jauge :value="$subject->mastery" :color="$subject->color" class="max-w-md flex-1" height="0.5rem" />
                    <span class="text-sm font-semibold tabulaire texte-fort">{{ $subject->mastery }} %</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($cartesDues > 0)
                    <a href="{{ route('drill.session', ['matiere' => $subject->id]) }}" class="btn btn-accent">
                        <x-icone name="layers" class="size-4" /> Drill · {{ $cartesDues }}
                    </a>
                @endif
                <a href="{{ route('exercices.index', ['matiere' => $subject->slug]) }}" class="btn btn-doux">
                    <x-icone name="steps" class="size-4" /> Exercices
                </a>
                <a href="{{ route('diagnostic.show', $subject->slug) }}" class="btn btn-fantome">
                    <x-icone name="pulse" class="size-4" /> Diagnostic
                </a>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[1.7fr_1fr]">

        {{-- ============ Chapitres ============ --}}
        <section>
            <h2 class="mb-3 text-sm font-semibold texte-fort">
                Chapitres <span class="ml-1 texte-faible">({{ $subject->chapters->count() }})</span>
            </h2>

            @forelse ($subject->chapters as $chapitre)
                @php $m = $chapitre->progress?->mastery ?? 0; @endphp
                <a href="{{ route('chapitres.show', [$subject, $chapitre]) }}"
                   class="carte mb-2 block px-4 py-3.5 transition-colors hover:bg-[var(--surface-survol)]">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 w-7 shrink-0 text-xs font-semibold tabulaire texte-faible">
                            {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-medium texte-fort">{{ $chapitre->title }}</span>
                                @if ($chapitre->gaps->count())
                                    <span class="puce" style="border-color: var(--color-lacune); color: var(--color-lacune-fort)">
                                        {{ $chapitre->gaps->count() }} lacune{{ $chapitre->gaps->count() > 1 ? 's' : '' }}
                                    </span>
                                @endif
                                @if ($chapitre->exam_weight >= 4)
                                    <span class="puce" style="border-color: var(--accent); color: var(--accent)">Poids fort</span>
                                @endif
                            </div>

                            @if ($chapitre->summary)
                                <p class="mt-1 line-clamp-2 text-xs leading-relaxed texte-doux">{{ $chapitre->summary }}</p>
                            @endif

                            <div class="mt-2.5 flex items-center gap-3">
                                <x-jauge :value="$m" class="max-w-[14rem] flex-1" height="0.25rem" />
                                <span class="text-[11px] tabulaire texte-faible">{{ $m }} %</span>
                                <span class="text-[11px] texte-faible">
                                    {{ $chapitre->lessons->count() }} fiche{{ $chapitre->lessons->count() > 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>

                        <x-icone name="arrow-r" class="mt-1 size-4 shrink-0 texte-faible" />
                    </div>
                </a>
            @empty
                <x-vide icon="book" titre="Chapitres en cours de génération">
                    Le découpage de cette matière est extrait des polycopiés puis structuré en fiches.
                    Les ressources brutes sont déjà consultables dans la bibliothèque.
                </x-vide>
            @endforelse
        </section>

        {{-- ============ Colonne de droite ============ --}}
        <div class="space-y-6">

            {{-- Lacunes --}}
            <section class="carte p-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold texte-fort">Lacunes ouvertes</h2>
                    <span class="text-xs tabulaire texte-faible">
                        {{ $lacunes->count() }}/{{ $lacunes->count() + $lacunesFermees->count() }}
                    </span>
                </div>

                @forelse ($lacunes->take(6) as $lacune)
                    <div class="mt-3 border-l-2 pl-3" style="border-color: var(--color-lacune)">
                        <p class="text-xs font-medium texte-fort">{{ $lacune->title }}</p>
                        @if ($lacune->evidence)
                            <p class="mt-1 text-[11px] italic texte-faible">« {{ $lacune->evidence }} »</p>
                        @endif
                    </div>
                @empty
                    <p class="mt-2 text-xs texte-doux">
                        {{ $lacunesFermees->count()
                            ? 'Toutes les lacunes de cette matière sont refermées.'
                            : 'Aucune lacune enregistrée pour cette matière.' }}
                    </p>
                @endforelse

                @if ($lacunes->count() > 6)
                    <a href="{{ route('diagnostic.show', $subject->slug) }}"
                       class="mt-3 block text-xs texte-doux hover:underline">
                        Voir les {{ $lacunes->count() }} lacunes →
                    </a>
                @endif

                {{-- Refermées : repliées, mais jamais hors de portée. Une lacune close
                     par erreur doit pouvoir être rouverte sans passer par le diagnostic. --}}
                @if ($lacunesFermees->count())
                    <details class="mt-4 border-t bord pt-3">
                        <summary class="cursor-pointer list-none text-xs texte-doux hover:underline">
                            <span class="inline-flex items-center gap-1.5">
                                <x-icone name="check" class="size-3.5" style="color: var(--color-acquis-fort)" />
                                {{ $lacunesFermees->count() }} refermée{{ $lacunesFermees->count() > 1 ? 's' : '' }}
                                — afficher pour rouvrir
                            </span>
                        </summary>

                        <div class="mt-2.5 space-y-2">
                            @foreach ($lacunesFermees as $lacune)
                                <div class="flex items-start gap-2 border-l-2 pl-3"
                                     style="border-color: var(--color-acquis)">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs texte-doux">{{ $lacune->title }}</p>
                                        @if ($lacune->resolved_at)
                                            <p class="mt-0.5 text-[10px] texte-faible">
                                                refermée le {{ $lacune->resolved_at->translatedFormat('j M à H\hi') }}
                                            </p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('lacunes.statut', $lacune) }}" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="status" value="ouverte">
                                        <button class="btn btn-fantome !px-2 !py-1 text-[11px]">
                                            <x-icone name="refresh" class="size-3" /> Rouvrir
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif
            </section>

            {{-- Examens blancs --}}
            @if ($subject->mockExams->count())
                <section class="carte p-4">
                    <h2 class="text-sm font-semibold texte-fort">Examens blancs</h2>
                    <div class="mt-3 space-y-2">
                        @foreach ($subject->mockExams as $ex)
                            <a href="{{ route('examens.show', $ex) }}"
                               class="flex items-center gap-2 rounded-md px-2 py-1.5 text-xs hover:bg-[var(--surface-survol)]">
                                <x-icone name="clock" class="size-3.5 shrink-0 texte-faible" />
                                <span class="min-w-0 flex-1 truncate texte-doux">{{ $ex->title }}</span>
                                @if ($best = $ex->bestScore())
                                    <span class="tabulaire texte-fort">{{ rtrim(rtrim(number_format($best, 1, ',', ''), '0'), ',') }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Ressources --}}
            <section class="carte p-4">
                <h2 class="text-sm font-semibold texte-fort">Documents source</h2>
                <div class="mt-3 space-y-2.5">
                    @foreach ($ressources as $kind => $liste)
                        <div class="flex items-center justify-between text-xs">
                            <span class="texte-doux">{{ \App\Models\Resource::KINDS[$kind] ?? $kind }}</span>
                            <span class="tabulaire texte-faible">{{ $liste->count() }}</span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('bibliotheque.index', ['matiere' => $subject->slug]) }}"
                   class="mt-3.5 flex items-center gap-1.5 text-xs texte-doux hover:underline">
                    Ouvrir dans la bibliothèque <x-icone name="arrow-r" class="size-3.5" />
                </a>
            </section>
        </div>
    </div>

@endsection