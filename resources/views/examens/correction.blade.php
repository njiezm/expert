@extends('layouts.app')

@section('titre', 'Correction — '.$examen->title)
@section('sous-titre')
    {{ $examen->subject->code }} ·
    composé le {{ $session->started_at->translatedFormat('j F à H\hi') }} ·
    {{ $session->mode_label }}
@endsection

@section('contenu')

    <div class="mx-auto max-w-3xl">

        {{-- Résultat --}}
        <section class="carte-haute mb-6 grid gap-5 p-5 sm:grid-cols-[auto_1fr] sm:items-center">
            <div class="text-center sm:text-left">
                @if ($session->score !== null)
                    @php $sur20 = $examen->total_points > 0 ? round((float) $session->score / (float) $examen->total_points * 20, 1) : 0; @endphp
                    <p class="chrono text-4xl font-semibold"
                       style="color: {{ $sur20 >= 10 ? 'var(--color-acquis-fort)' : ($sur20 >= 7 ? 'var(--color-alerte)' : 'var(--color-lacune-fort)') }}">
                        {{ rtrim(rtrim(number_format($sur20, 1, ',', ''), '0'), ',') }}
                    </p>
                    <p class="text-xs texte-faible">sur 20</p>
                @else
                    <p class="chrono text-4xl font-semibold texte-faible">—</p>
                    <p class="text-xs texte-faible">à corriger</p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="puce">{{ floor($session->elapsed_sec / 60) }} min sur {{ $examen->duration_min }}</span>
                <span class="puce">{{ $session->mode === 'distance_nuit' ? 'Épreuve nocturne' : 'Amphi' }}</span>
                @if ($session->was_timed_out)
                    <span class="puce" style="border-color: var(--color-lacune); color: var(--color-lacune-fort)">
                        Temps écoulé
                    </span>
                @endif
                @if ($session->score !== null)
                    <span class="puce">
                        {{ rtrim(rtrim(number_format((float) $session->score, 1, ',', ''), '0'), ',') }} /
                        {{ rtrim(rtrim(number_format((float) $examen->total_points, 1, ',', ''), '0'), ',') }} pts
                    </span>
                @endif
            </div>
        </section>

        <div class="carte mb-6 border-l-2 px-5 py-4" style="border-left-color: var(--accent)">
            <p class="text-xs leading-relaxed texte-doux">
                <strong class="texte-fort">Règle d’auto-correction.</strong> Cochez un attendu uniquement
                s’il est écrit noir sur blanc dans votre réponse. Pas « j’y avais pensé », pas « c’est
                sous-entendu ». Le correcteur ne lit que ce qui est sur la copie — c’est précisément
                ce qui a coûté des points en janvier et en mai.
            </p>
        </div>

        <form method="POST" action="{{ route('examens.noter', $session) }}">
            @csrf

            @foreach ($questions as $question)
                @php $reponse = $reponses[$question->id] ?? null; @endphp

                <section class="mb-8">
                    <div class="mb-3 flex flex-wrap items-baseline gap-3">
                        <span class="text-sm font-semibold texte-fort">{{ $question->number }}</span>
                        <span class="text-xs texte-faible">
                            {{ rtrim(rtrim(number_format((float) $question->points, 1, ',', ''), '0'), ',') }} pt
                        </span>
                        @if ($question->chapter)
                            <a href="{{ route('chapitres.show', [$examen->subject, $question->chapter]) }}"
                               class="puce hover:underline">{{ $question->chapter->title }}</a>
                        @endif
                        @if ($reponse?->points_awarded !== null)
                            <span class="ml-auto text-sm font-semibold tabulaire"
                                  style="color: {{ $reponse->points_awarded >= $question->points * 0.5 ? 'var(--color-acquis-fort)' : 'var(--color-lacune-fort)' }}">
                                {{ rtrim(rtrim(number_format((float) $reponse->points_awarded, 2, ',', ''), '0'), ',') }} pt
                            </span>
                        @endif
                    </div>

                    <div class="carte mb-3 px-5 py-4">
                        <div class="prose-cours !text-sm">{!! Str::markdown($question->statement) !!}</div>
                    </div>

                    {{-- Ce que vous avez écrit --}}
                    <div class="carte mb-3 px-5 py-4">
                        <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.1em] texte-faible">Votre copie</p>
                        @if (filled($reponse?->answer))
                            <pre class="whitespace-pre-wrap font-mono text-[13px] leading-relaxed texte-doux">{{ $reponse->answer }}</pre>
                        @else
                            <p class="text-xs italic" style="color: var(--color-lacune-fort)">Question non traitée.</p>
                        @endif
                    </div>

                    {{-- Grille --}}
                    @if ($question->rubric)
                        <div class="carte mb-3 overflow-hidden">
                            <div class="px-5 py-2.5" style="background: var(--accent-doux)">
                                <p class="text-xs font-semibold texte-fort">Attendus du barème</p>
                            </div>
                            <div class="divide-y" style="border-color: var(--bordure)">
                                @foreach ($question->rubric as $i => $attendu)
                                    <label class="flex cursor-pointer items-start gap-3 px-5 py-2.5 hover:bg-[var(--surface-survol)]">
                                        <input type="checkbox" name="grille[{{ $question->id }}][{{ $i }}]" value="1"
                                               class="mt-0.5 size-4 shrink-0 rounded" style="accent-color: var(--accent)"
                                               @checked(in_array($i, $reponse?->rubric_check ?? [], true))>
                                        <span class="flex-1 text-sm leading-relaxed texte-doux">{{ $attendu['label'] }}</span>
                                        <span class="shrink-0 text-xs tabulaire texte-faible">{{ $attendu['points'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Corrigé --}}
                    @if ($question->solution)
                        <button type="button" data-devoiler="sol-{{ $question->id }}" class="btn btn-fantome text-xs">
                            <x-icone name="eye" class="size-3.5" /> Voir le corrigé
                        </button>
                        <div id="sol-{{ $question->id }}" hidden
                             class="carte mt-3 border-l-2 px-5 py-4" style="border-left-color: var(--color-acquis)">
                            <div class="prose-cours !text-sm">{!! Str::markdown($question->solution) !!}</div>
                        </div>
                    @endif
                </section>
            @endforeach

            <button type="submit" class="btn btn-accent w-full">Enregistrer la note</button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('examens.show', $examen) }}" class="text-xs texte-doux hover:underline">Repasser cet examen</a>
        </div>
    </div>

@endsection