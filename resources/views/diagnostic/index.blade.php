@extends('layouts.app')

@section('titre', 'Diagnostic')
@section('sous-titre', 'Ce que les copies de la session initiale disent, erreur par erreur')

@section('contenu')

    {{-- Les cinq notes --}}
    <section class="mb-6">
        <h2 class="mb-3 text-sm font-semibold texte-fort">Point de départ</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($papers as $paper)
                <a href="{{ route('diagnostic.show', $paper->subject->slug) }}"
                   class="carte px-4 py-3.5 transition-colors hover:bg-[var(--surface-survol)]">
                    <div class="flex items-baseline justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full" style="background: {{ $paper->subject->color }}"></span>
                            <span class="text-sm font-semibold texte-fort">{{ $paper->subject->code }}</span>
                        </div>
                        <span class="chrono text-xl font-semibold"
                              style="color: {{ $paper->grade < 5 ? 'var(--color-lacune-fort)' : ($paper->grade < 10 ? 'var(--color-alerte)' : 'var(--color-acquis-fort)') }}">
                            {{ rtrim(rtrim(number_format((float) $paper->grade, 2, ',', ''), '0'), ',') }}<span class="text-xs texte-faible">/20</span>
                        </span>
                    </div>

                    <p class="mt-1.5 text-[11px] texte-faible">
                        {{ $paper->session_label }} · {{ $paper->centre }}
                        @if ($paper->sat_on) · {{ $paper->sat_on->format('d/m/Y') }} @endif
                    </p>

                    @if ($paper->appreciation)
                        <p class="mt-2 text-xs italic" style="color: var(--color-lacune-fort)">
                            « {{ $paper->appreciation }} »
                        </p>
                    @endif

                    @if ($paper->score_breakdown)
                        <div class="mt-2.5 flex flex-wrap gap-1.5">
                            @foreach ($paper->score_breakdown as $label => $pts)
                                <span class="puce">{{ $label }} : {{ $pts }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-3 flex items-center justify-between text-[11px] texte-faible">
                        <span>{{ $paper->analysed_pages }}/{{ $paper->pages }} pages analysées</span>
                        @if ($paper->resource)
                            <span class="flex items-center gap-1"><x-icone name="file" class="size-3" /> Copie scannée</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <p class="mt-3 text-xs texte-doux">
            Moyenne de la session : <span class="font-semibold texte-fort tabulaire">{{ number_format($moyenne, 2, ',', ' ') }} / 20</span>
        </p>
    </section>

    <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">

        {{-- La faiblesse transversale --}}
        <section class="carte overflow-hidden">
            <div class="px-5 py-4" style="background: color-mix(in oklab, var(--color-lacune) 12%, transparent)">
                <div class="flex items-center gap-2">
                    <x-icone name="scale" class="size-4" style="color: var(--color-lacune-fort)" />
                    <h2 class="text-sm font-semibold texte-fort">La faiblesse transversale</h2>
                </div>
                <p class="mt-2 text-xs leading-relaxed texte-doux">
                    Les annotations du correcteur convergent sur trois copies indépendantes.
                    Le problème n'est pas seulement de savoir : c'est de <strong class="texte-fort">rédiger
                    ce que le barème peut compter</strong>.
                </p>
            </div>

            <div class="divide-y" style="border-color: var(--bordure)">
                @foreach ([
                    ['Décrire au lieu de démontrer', '« justifier », « évaluation ? »', 'Aucune complexité chiffrée, aucun contre-exemple, aucune règle nommée. Une phrase qui explique ce qu’une structure « permet » ne vaut aucun point.'],
                    ['Ne pas trancher', '« faux, choisir, pas équivalent »', 'Deux formalisations proposées en espérant qu’une soit bonne. Le correcteur sanctionne l’indécision : une réponse, une seule.'],
                    ['Sortir du référentiel', '« pas vu dans le cours »', 'Une notion inventée ou importée d’ailleurs n’est pas au barème. Le vocabulaire du polycopié est le seul qui compte.'],
                ] as [$titre, $preuve, $detail])
                    <div class="px-5 py-3.5">
                        <p class="text-sm font-medium texte-fort">{{ $titre }}</p>
                        <p class="mt-1 text-[11px] font-mono" style="color: var(--color-lacune-fort)">{{ $preuve }}</p>
                        <p class="mt-1.5 text-xs leading-relaxed texte-doux">{{ $detail }}</p>
                    </div>
                @endforeach
            </div>

            @if ($rigueur)
                <div class="border-t bord px-5 py-3.5">
                    <a href="{{ route('matieres.show', $rigueur->slug) }}"
                       class="flex items-center gap-1.5 text-xs texte-doux hover:underline">
                        Travailler la rigueur de rédaction <x-icone name="arrow-r" class="size-3.5" />
                    </a>
                </div>
            @endif
        </section>

        {{-- Lacunes les plus graves --}}
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold texte-fort">Lacunes prioritaires</h2>
                <div class="flex gap-1.5">
                    @foreach ($parNature as $kind => $ligne)
                        <span class="puce">
                            {{ \App\Models\Gap::KINDS[$kind] ?? $kind }} : {{ $ligne->total - $ligne->fermees }}/{{ $ligne->total }}
                        </span>
                    @endforeach
                </div>
            </div>

            @forelse ($lacunesGraves as $lacune)
                <div class="carte mb-2 px-4 py-3">
                    <div class="flex items-start gap-2.5">
                        <span class="mt-1 flex shrink-0 gap-0.5">
                            @for ($i = 0; $i < 5; $i++)
                                <span class="size-1 rounded-full"
                                      style="background: {{ $i < $lacune->severity ? 'var(--color-lacune)' : 'var(--bordure)' }}"></span>
                            @endfor
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium texte-fort">{{ $lacune->title }}</p>
                            <p class="mt-0.5 text-[11px] texte-faible">
                                {{ $lacune->subject->code }}
                                @if ($lacune->chapter) · {{ $lacune->chapter->title }} @endif
                                · {{ $lacune->kind_label }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <x-vide icon="pulse" titre="Analyse détaillée en cours">
                    Les copies sont des scans : chaque page est relue visuellement pour en extraire
                    les erreurs une à une, puis les rattacher aux chapitres concernés.
                </x-vide>
            @endforelse
        </section>
    </div>

@endsection