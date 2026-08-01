@extends('layouts.app')

@section('titre', $lesson->title)
@section('sous-titre', $subject->code.' · '.$chapter->title)

@section('contenu')

    <div class="mx-auto max-w-3xl">

        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('chapitres.show', [$subject, $chapter]) }}" class="btn btn-fantome !px-2.5 !py-1.5">
                <x-icone name="arrow-l" class="size-4" />
            </a>
            <span class="text-xs texte-faible">{{ $lesson->est_minutes }} min de lecture</span>
            @if ($dejaLue)
                <span class="puce" style="border-color: var(--color-acquis); color: var(--color-acquis-fort)">
                    <x-icone name="check" class="size-3" /> Déjà parcourue
                </span>
            @endif

            {{-- Lecture de la fiche entière, avec réglage de vitesse. --}}
            <x-lire cible="fiche-entiere" label="Écouter la fiche" :vitesse="true" class="ml-auto" />
        </div>

        <div id="fiche-entiere" class="contents">

        {{-- Les cinq temps de la fiche, dans l'ordre : on ne saute pas l'intuition
             pour aller au formalisme, et on ne quitte pas la fiche sans avoir lu
             ce que le correcteur attend. --}}
        @foreach ($lesson->sections() as $i => $section)
            @php $accent = $section['key'] === 'examiner_expects'; @endphp
            <section class="mb-4">
                <div class="mb-2.5 flex items-center gap-2">
                    <span class="grid size-6 shrink-0 place-items-center rounded-md"
                          style="background: {{ $accent ? 'var(--accent-doux)' : 'var(--surface-haute)' }}">
                        <x-icone :name="$section['icon']" class="size-3.5"
                                 style="color: {{ $accent ? 'var(--accent)' : 'var(--texte-doux)' }}" />
                    </span>
                    <h2 class="text-[13px] font-semibold uppercase tracking-[0.08em]"
                        style="color: {{ $accent ? 'var(--accent)' : 'var(--texte-doux)' }}">
                        {{ $section['label'] }}
                    </h2>
                    <x-lire :cible="'section-'.$section['key']" class="ml-auto" />
                </div>

                <div id="section-{{ $section['key'] }}"
                     class="carte px-5 py-4 {{ $accent ? 'border-l-2' : '' }}"
                     @if ($accent) style="border-left-color: var(--accent)" @endif>
                    <div class="prose-cours">{!! Str::markdown($section['body']) !!}</div>
                </div>
            </section>
        @endforeach

        </div>

        @if (empty($lesson->sections()))
            <x-vide icon="book" titre="Fiche vide">Le contenu de cette fiche reste à générer.</x-vide>
        @endif

        {{-- Sources --}}
        @if ($lesson->source_refs)
            <p class="mt-6 text-[11px] texte-faible">
                Source :
                @foreach ($lesson->source_refs as $ref)
                    {{ $ref['label'] ?? '' }}@if (! $loop->last), @endif
                @endforeach
            </p>
        @endif

        {{-- Navigation --}}
        <div class="mt-8 flex flex-wrap items-center gap-3 border-t bord pt-6">
            @if ($precedent)
                <a href="{{ route('cours.show', $precedent) }}" class="btn btn-fantome">
                    <x-icone name="arrow-l" class="size-4" /> {{ Str::limit($precedent->title, 28) }}
                </a>
            @endif

            <form method="POST" action="{{ route('cours.lu', $lesson) }}" class="ml-auto">
                @csrf
                <input type="hidden" name="minutes" value="{{ $lesson->est_minutes }}">
                <button class="btn btn-accent">
                    <x-icone name="check" class="size-4" />
                    {{ $suivant ? 'Compris — fiche suivante' : 'Compris — terminer le chapitre' }}
                </button>
            </form>
        </div>

        <p class="mt-4 text-center text-[11px] leading-relaxed texte-faible">
            Lire ne compte que pour 15 % de la maîtrise. Les 85 % restants viennent des cartes,
            des exercices et des examens blancs.
        </p>
    </div>

@endsection