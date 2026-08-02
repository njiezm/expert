@extends('layouts.app')

@section('titre', 'Suivre le cours — '.$subject->code)
@section('sous-titre', $subject->name)

@section('contenu')

    <div class="mx-auto max-w-3xl">

        {{-- Bandeau de progression --}}
        <section class="carte-haute mb-6 overflow-hidden">
            <div class="h-1" style="background: {{ $subject->color }}"></div>
            <div class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold texte-fort">Le cours, de A à Z</h2>
                        <p class="mt-1.5 max-w-xl text-sm leading-relaxed texte-doux">
                            Ces séances se suivent <strong class="texte-fort">dans l'ordre</strong>.
                            Chacune ne suppose connue que la précédente, et rien d'autre.
                            Aucune notion n'est employée avant d'avoir été expliquée.
                        </p>
                    </div>

                    @if ($seances->count())
                        <div class="text-right">
                            <p class="chrono text-3xl font-semibold leading-none"
                               style="color: {{ count($suivies) === $seances->count() ? 'var(--color-acquis-fort)' : 'var(--accent)' }}">
                                {{ count($suivies) }}<span class="text-lg texte-faible">/{{ $seances->count() }}</span>
                            </p>
                            <p class="mt-1 text-[11px] texte-faible">séances suivies</p>
                        </div>
                    @endif
                </div>

                @if ($seances->count())
                    <x-jauge :value="round(count($suivies) / max(1, $seances->count()) * 100)"
                             :color="$subject->color" class="mt-4" height="0.5rem" />

                    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs texte-faible">
                        <span>{{ floor($minutesTotal / 60) }} h {{ str_pad($minutesTotal % 60, 2, '0', STR_PAD_LEFT) }} au total</span>
                        @if ($minutesRestantes > 0)
                            <span>
                                {{ floor($minutesRestantes / 60) }} h {{ str_pad($minutesRestantes % 60, 2, '0', STR_PAD_LEFT) }} restantes
                            </span>
                        @endif
                        @if ($subject->exam_at)
                            <span>Épreuve dans {{ max(0, $subject->days_until_exam) }} jours</span>
                        @endif
                    </div>

                    @if ($reprise)
                        <a href="{{ route('cours-suivi.show', [$subject, $reprise]) }}"
                           class="btn btn-accent mt-4">
                            <x-icone name="play" class="size-4" />
                            {{ count($suivies) ? 'Reprendre' : 'Commencer' }} — séance {{ $reprise->position }} · {{ Str::limit($reprise->title, 40) }}
                        </a>
                    @else
                        <p class="mt-4 flex items-center gap-2 text-sm" style="color: var(--color-acquis-fort)">
                            <x-icone name="check" class="size-4" /> Cours terminé.
                        </p>
                    @endif
                @endif
            </div>
        </section>

        {{-- Le sommaire --}}
        @forelse ($seances as $seance)
            @php $faite = in_array($seance->id, $suivies, true); @endphp

            <a href="{{ route('cours-suivi.show', [$subject, $seance]) }}"
               class="carte mb-2 flex items-start gap-4 px-4 py-3.5 transition-colors hover:bg-[var(--surface-survol)]">

                <span class="mt-0.5 grid size-7 shrink-0 place-items-center rounded-full text-[11px] font-semibold tabulaire"
                      style="{{ $faite
                          ? 'background: var(--color-acquis); color: #fff'
                          : 'border: 1px solid var(--bordure); color: var(--texte-faible)' }}">
                    @if ($faite)
                        <x-icone name="check" class="size-3.5" />
                    @else
                        {{ $seance->position }}
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium texte-fort {{ $faite ? 'opacity-70' : '' }}">
                        {{ $seance->title }}
                    </p>
                    @if ($seance->intro)
                        <p class="mt-1 line-clamp-2 text-xs leading-relaxed texte-doux">
                            {{ Str::limit(strip_tags($seance->intro), 160) }}
                        </p>
                    @endif
                    <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] texte-faible">
                        <span>{{ $seance->duree_min }} min</span>
                        @if ($seance->chapter)
                            <span>· {{ $seance->chapter->title }}</span>
                        @endif
                    </div>
                </div>

                <x-icone name="arrow-r" class="mt-1 size-4 shrink-0 texte-faible" />
            </a>
        @empty
            <x-vide icon="book" titre="Le cours de {{ $subject->code }} reste à écrire">
                Les fiches de révision et les exercices sont disponibles dès maintenant
                depuis la page de la matière.
            </x-vide>
        @endforelse

        @if ($seances->count())
            <p class="mt-5 text-center text-[11px] leading-relaxed texte-faible">
                Suivre le cours ne suffit pas à obtenir la note. Une fois une séance terminée,
                les cartes et les exercices du chapitre correspondant sont ce qui la transforme
                en points.
            </p>
        @endif
    </div>

@endsection