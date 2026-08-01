@extends('layouts.app')

@section('titre', $examen->title)
@section('sous-titre', $examen->subject->code.' · '.$examen->duration_min.' minutes')

@section('contenu')

    <div class="mx-auto max-w-2xl">

        <section class="carte-haute mb-6 overflow-hidden">
            <div class="h-1" style="background: {{ $examen->subject->color }}"></div>
            <div class="p-6">
                <h2 class="text-lg font-semibold texte-fort">{{ $examen->title }}</h2>

                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="puce">{{ $examen->questions->count() }} questions</span>
                    <span class="puce">{{ $examen->duration_min }} minutes</span>
                    <span class="puce">{{ rtrim(rtrim(number_format((float) $examen->total_points, 1, ',', ''), '0'), ',') }} points</span>
                    @if ($examen->year)<span class="puce">{{ $examen->year }}</span>@endif
                </div>

                @if ($examen->instructions)
                    <div class="prose-cours mt-4 !text-sm">{!! Str::markdown($examen->instructions) !!}</div>
                @endif
            </div>
        </section>

        {{-- Choix du mode : reproduire les conditions réellement vécues --}}
        <form method="POST" action="{{ route('examens.demarrer', $examen) }}">
            @csrf

            <h3 class="mb-3 text-sm font-semibold texte-fort">Conditions de passage</h3>

            <div class="space-y-2">
                <label class="carte flex cursor-pointer items-start gap-3 px-4 py-3.5 hover:bg-[var(--surface-survol)]">
                    <input type="radio" name="mode" value="amphi" class="mt-1 size-4 shrink-0"
                           style="accent-color: var(--accent)" @checked($modeSuggere === 'amphi')>
                    <span class="flex-1">
                        <span class="flex items-center gap-2 text-sm font-medium texte-fort">
                            <x-icone name="sun" class="size-4" /> Amphi — Besançon, en journée
                        </span>
                        <span class="mt-1 block text-xs leading-relaxed texte-doux">
                            Interface normale. Reproduit la session de janvier : amphithéâtre, plein jour, en présentiel.
                        </span>
                    </span>
                </label>

                <label class="carte flex cursor-pointer items-start gap-3 px-4 py-3.5 hover:bg-[var(--surface-survol)]">
                    <input type="radio" name="mode" value="distance_nuit" class="mt-1 size-4 shrink-0"
                           style="accent-color: var(--accent)" @checked($modeSuggere === 'distance_nuit')>
                    <span class="flex-1">
                        <span class="flex items-center gap-2 text-sm font-medium texte-fort">
                            <x-icone name="moon" class="size-4" /> À distance — épreuve nocturne
                        </span>
                        <span class="mt-1 block text-xs leading-relaxed texte-doux">
                            Contraste abaissé, dominante chaude, aucune distraction à l’écran. Reproduit
                            la session de mai : salle réduite, horaire décalé, fatigue.
                        </span>
                    </span>
                </label>
            </div>

            <div class="carte mt-5 border-l-2 px-4 py-3" style="border-left-color: var(--color-alerte)">
                <p class="text-xs leading-relaxed texte-doux">
                    Le chronomètre part au clic et ne s’arrête pas. Recharger la page ne rend pas de temps.
                    À l’échéance, la copie est rendue automatiquement — exactement comme en salle.
                </p>
            </div>

            <button type="submit" class="btn btn-accent mt-5 w-full">
                <x-icone name="play" class="size-4" /> Démarrer — {{ $examen->duration_min }} minutes
            </button>
        </form>

        @if ($examen->sessions->count())
            <section class="mt-8">
                <h3 class="mb-3 text-sm font-semibold texte-fort">Passages précédents</h3>
                @foreach ($examen->sessions as $s)
                    <a href="{{ route('examens.correction', $s) }}"
                       class="carte mb-2 flex items-center gap-3 px-4 py-2.5 text-xs hover:bg-[var(--surface-survol)]">
                        <span class="tabulaire texte-faible">{{ $s->started_at->translatedFormat('d/m à H\hi') }}</span>
                        <span class="puce">{{ $s->mode === 'distance_nuit' ? 'Nuit' : 'Amphi' }}</span>
                        <span class="ml-auto tabulaire texte-faible">
                            {{ floor($s->elapsed_sec / 60) }} min
                        </span>
                        @if ($s->score !== null)
                            <span class="font-semibold tabulaire texte-fort">
                                {{ rtrim(rtrim(number_format((float) $s->score, 1, ',', ''), '0'), ',') }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </section>
        @endif
    </div>

@endsection