@extends('layouts.app')

@section('titre', 'Tableau de bord')
@section('sous-titre')
    @if ($prochaine)
        Prochaine épreuve : {{ $prochaine->code }} le {{ $prochaine->exam_at->translatedFormat('l j F à H\hi') }}
    @else
        Toutes les épreuves sont passées.
    @endif
@endsection

@section('contenu')

    {{-- ============ Bandeau de compte à rebours ============ --}}
    <section class="carte-haute mb-6 overflow-hidden">
        <div class="flex flex-wrap items-stretch divide-x divide-y sm:divide-y-0" style="border-color: var(--bordure)">
            @foreach ($matieres->whereNotNull('exam_at')->sortBy('exam_at') as $m)
                @php
                    $j = max(0, $m->days_until_exam);
                    $passe = $m->exam_at->isPast();
                @endphp
                <a href="{{ route('matieres.show', $m->slug) }}"
                   class="min-w-[9.5rem] flex-1 px-4 py-3.5 transition-colors hover:bg-[var(--surface-survol)]"
                   style="border-color: var(--bordure); {{ $passe ? 'opacity:.45' : '' }}">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full" style="background: {{ $m->color }}"></span>
                        <span class="text-xs font-semibold texte-fort">{{ $m->code }}</span>
                    </div>
                    <p class="mt-2 chrono text-2xl font-semibold leading-none"
                       style="color: {{ $j <= 3 ? 'var(--color-lacune-fort)' : ($j <= 7 ? 'var(--color-alerte)' : 'var(--texte-fort)') }}">
                        J−{{ $j }}
                    </p>
                    <p class="mt-1.5 text-[11px] texte-doux">
                        {{ $m->exam_at->translatedFormat('D j M · H\h') }}
                    </p>
                    <x-jauge :value="$m->mastery" :color="$m->color" class="mt-2.5" />
                    <p class="mt-1.5 text-[11px] tabulaire texte-faible">
                        {{ $m->mastery }} % · départ {{ rtrim(rtrim(number_format((float) $m->initial_grade, 2, ',', ''), '0'), ',') }}/20
                    </p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============ Chiffres clés ============ --}}
    <section class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <x-stat label="Cartes à réviser" :value="$dueTotal" tone="accent"
                hint="{{ $dueTotal > 0 ? 'À faire aujourd’hui' : 'File vide' }}" />
        <x-stat label="Lacunes ouvertes" :value="$lacunesOuvertes" tone="lacune"
                hint="sur {{ $lacunesTotal }} identifiées" />
        <x-stat label="Travail sur 7 j"
                :value="floor($minutes7j / 60).' h '.str_pad($minutes7j % 60, 2, '0', STR_PAD_LEFT)" />
        <x-stat label="Série" :value="$serie.' j'" tone="{{ $serie >= 3 ? 'acquis' : null }}"
                hint="jours consécutifs" />
        <x-stat label="Fin des épreuves" :value="'J−'.max(0, $joursRestants)"
                hint="28 août 2026" />
    </section>

    <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">

        {{-- ============ Le programme du jour ============ --}}
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold texte-fort">Aujourd’hui — {{ now()->translatedFormat('l j F') }}</h2>
                <a href="{{ route('planning.index') }}" class="text-xs texte-doux hover:underline">Tout le planning</a>
            </div>

            @forelse ($planToday as $bloc)
                <div class="carte mb-2 flex items-center gap-3 px-4 py-3">
                    <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $bloc->subject->color }}"></span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline gap-x-2">
                            <span class="text-sm font-medium texte-fort">{{ $bloc->activity_label }}</span>
                            <span class="text-xs texte-doux">{{ $bloc->subject->code }}</span>
                            @if ($bloc->chapter)
                                <span class="truncate text-xs texte-faible">· {{ $bloc->chapter->title }}</span>
                            @endif
                        </div>
                        <p class="mt-0.5 truncate text-[11px] texte-faible">{{ $bloc->rationale }}</p>
                    </div>

                    <span class="shrink-0 text-xs tabulaire texte-doux">{{ $bloc->planned_minutes }} min</span>

                    <form method="POST" action="{{ route('planning.bloc', $bloc) }}" class="shrink-0">
                        @csrf
                        <input type="hidden" name="status" value="{{ $bloc->status === 'fait' ? 'planifie' : 'fait' }}">
                        <button class="btn {{ $bloc->status === 'fait' ? 'btn-doux' : 'btn-fantome' }} !px-2 !py-1.5"
                                title="{{ $bloc->status === 'fait' ? 'Annuler' : 'Marquer fait' }}">
                            <x-icone name="check" class="size-4"
                                     style="color: {{ $bloc->status === 'fait' ? 'var(--color-acquis-fort)' : 'inherit' }}" />
                        </button>
                    </form>
                </div>
            @empty
                <x-vide icon="calendar" titre="Aucun bloc planifié aujourd’hui">
                    Renseignez vos disponibilités, puis lancez le calcul : le moteur répartira le temps
                    restant sur les cinq matières en remontant depuis chaque date d’épreuve.
                </x-vide>
                <a href="{{ route('planning.creneaux') }}" class="btn btn-accent mt-3">
                    Renseigner mes disponibilités
                </a>
            @endforelse
        </section>

        {{-- ============ Colonne de droite ============ --}}
        <div class="space-y-6">

            {{-- Drill --}}
            <section class="carte p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold texte-fort">Drill mémoire</h2>
                        <p class="mt-1 text-xs texte-doux">
                            {{ $dueTotal > 0
                                ? $dueTotal.' carte'.($dueTotal > 1 ? 's' : '').' arrivée'.($dueTotal > 1 ? 's' : '').' à échéance.'
                                : 'Rien à réviser dans l’immédiat.' }}
                        </p>
                    </div>
                    <x-icone name="layers" class="size-5 shrink-0 texte-faible" />
                </div>

                @if ($dueTotal > 0)
                    <div class="mt-3 space-y-1.5">
                        @foreach ($matieres as $m)
                            @continue(($dueBySubject[$m->id] ?? 0) === 0)
                            <a href="{{ route('drill.session', ['matiere' => $m->id]) }}"
                               class="flex items-center gap-2 rounded-md px-2 py-1.5 text-xs hover:bg-[var(--surface-survol)]">
                                <span class="size-1.5 rounded-full" style="background: {{ $m->color }}"></span>
                                <span class="texte-doux">{{ $m->code }}</span>
                                <span class="ml-auto tabulaire texte-fort">{{ $dueBySubject[$m->id] }}</span>
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('drill.session') }}" class="btn btn-accent mt-3 w-full">
                        <x-icone name="play" class="size-4" /> Lancer la session
                    </a>
                @endif
            </section>

            {{-- Rigueur --}}
            @if ($rigueur)
                <section class="carte overflow-hidden">
                    <div class="px-4 py-3.5" style="background: color-mix(in oklab, var(--color-lacune) 10%, transparent)">
                        <div class="flex items-center gap-2">
                            <x-icone name="scale" class="size-4" style="color: var(--color-lacune-fort)" />
                            <h2 class="text-sm font-semibold texte-fort">Rigueur de rédaction</h2>
                        </div>
                        <p class="mt-1.5 text-xs leading-relaxed texte-doux">
                            La lacune transversale : trois copies perdent des points non par ignorance,
                            mais parce que la réponse décrit au lieu de démontrer.
                        </p>
                    </div>
                    <div class="px-4 py-3">
                        <x-jauge :value="$rigueur->mastery" />
                        <a href="{{ route('matieres.show', $rigueur->slug) }}"
                           class="mt-3 flex items-center gap-1.5 text-xs texte-doux hover:underline">
                            Travailler la rédaction <x-icone name="arrow-r" class="size-3.5" />
                        </a>
                    </div>
                </section>
            @endif

            {{-- Diagnostic --}}
            <section class="carte p-4">
                <h2 class="text-sm font-semibold texte-fort">Point de départ</h2>
                <div class="mt-3 space-y-2">
                    @foreach ($matieres->whereNotNull('initial_grade')->sortBy('initial_grade') as $m)
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 shrink-0 text-[11px] font-semibold texte-doux">{{ $m->code }}</span>
                            <x-jauge :value="(float) $m->initial_grade * 5" :height="'0.3rem'" class="flex-1" />
                            <span class="w-12 shrink-0 text-right text-[11px] tabulaire texte-faible">
                                {{ rtrim(rtrim(number_format((float) $m->initial_grade, 2, ',', ''), '0'), ',') }}/20
                            </span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('diagnostic.index') }}"
                   class="mt-3.5 flex items-center gap-1.5 text-xs texte-doux hover:underline">
                    Voir le diagnostic complet <x-icone name="arrow-r" class="size-3.5" />
                </a>
            </section>
        </div>
    </div>

@endsection