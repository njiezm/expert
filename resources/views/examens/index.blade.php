@extends('layouts.app')

@section('titre', 'Examens blancs')
@section('sous-titre', 'En conditions réelles — chronomètre, mode amphi ou épreuve nocturne')

@section('contenu')

    @if ($enCours)
        <section class="carte-haute mb-6 border-l-2 px-5 py-4" style="border-left-color: var(--color-alerte)">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1">
                    <p class="text-sm font-semibold texte-fort">Composition en cours</p>
                    <p class="mt-0.5 text-xs texte-doux">
                        {{ $enCours->mockExam->subject->code }} · {{ $enCours->mockExam->title }} ·
                        démarrée à {{ $enCours->started_at->format('H\hi') }}
                    </p>
                </div>
                <a href="{{ route('examens.composer', $enCours) }}" class="btn btn-accent">Reprendre</a>
            </div>
        </section>
    @endif

    @forelse ($subjects as $s)
        <section class="mb-6">
            <div class="mb-3 flex flex-wrap items-baseline gap-2">
                <span class="size-2 rounded-full" style="background: {{ $s->color }}"></span>
                <h2 class="text-sm font-semibold texte-fort">{{ $s->code }} — {{ $s->name }}</h2>
                @if ($s->exam_at)
                    <span class="text-xs texte-faible">
                        Épreuve : {{ $s->exam_at->translatedFormat('j F, H\hi') }}
                        ({{ $s->exam_duration_min }} min) · J−{{ max(0, $s->days_until_exam) }}
                    </span>
                @endif
            </div>

            @forelse ($s->mockExams as $ex)
                @php $best = $ex->bestScore(); @endphp
                <a href="{{ route('examens.show', $ex) }}"
                   class="carte mb-2 flex flex-wrap items-center gap-3 px-4 py-3.5 transition-colors hover:bg-[var(--surface-survol)]">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium texte-fort">{{ $ex->title }}</p>
                        <p class="mt-0.5 text-[11px] texte-faible">
                            {{ $ex->questions->count() }} question{{ $ex->questions->count() > 1 ? 's' : '' }} ·
                            {{ $ex->duration_min }} min ·
                            {{ rtrim(rtrim(number_format((float) $ex->total_points, 1, ',', ''), '0'), ',') }} pts
                            @if ($ex->year) · {{ $ex->year }} @endif
                        </p>
                    </div>

                    @if ($ex->sessions->count())
                        <span class="text-[11px] texte-faible">{{ $ex->sessions->count() }} passage(s)</span>
                    @endif

                    @if ($best !== null)
                        <span class="chrono text-sm font-semibold"
                              style="color: {{ $best >= $ex->total_points * 0.5 ? 'var(--color-acquis-fort)' : 'var(--color-lacune-fort)' }}">
                            {{ rtrim(rtrim(number_format((float) $best, 1, ',', ''), '0'), ',') }}/{{ rtrim(rtrim(number_format((float) $ex->total_points, 1, ',', ''), '0'), ',') }}
                        </span>
                    @else
                        <span class="puce">Jamais passé</span>
                    @endif
                </a>
            @empty
                <p class="carte px-4 py-3 text-xs texte-doux">
                    Les annales de {{ $s->code }} sont indexées ; leur mise en forme d’examen blanc chronométré est à venir.
                </p>
            @endforelse
        </section>
    @empty
        <x-vide icon="clock" titre="Aucun examen blanc disponible" />
    @endforelse

    @if ($passees->count())
        <section class="mt-8">
            <h2 class="mb-3 text-sm font-semibold texte-fort">Historique</h2>
            @foreach ($passees as $p)
                <a href="{{ route('examens.correction', $p) }}"
                   class="carte mb-2 flex flex-wrap items-center gap-3 px-4 py-2.5 text-xs hover:bg-[var(--surface-survol)]">
                    <span class="tabulaire texte-faible">{{ $p->finished_at->translatedFormat('d/m à H\hi') }}</span>
                    <span class="texte-doux">{{ $p->mockExam->subject->code }}</span>
                    <span class="min-w-0 flex-1 truncate texte-fort">{{ $p->mockExam->title }}</span>
                    <span class="puce">{{ $p->mode === 'distance_nuit' ? 'Nuit' : 'Amphi' }}</span>
                    @if ($p->was_timed_out)
                        <span class="puce" style="border-color: var(--color-lacune); color: var(--color-lacune-fort)">Temps écoulé</span>
                    @endif
                    @if ($p->score !== null)
                        <span class="font-semibold tabulaire texte-fort">
                            {{ rtrim(rtrim(number_format((float) $p->score, 1, ',', ''), '0'), ',') }}/{{ rtrim(rtrim(number_format((float) $p->max_score, 1, ',', ''), '0'), ',') }}
                        </span>
                    @else
                        <span class="puce" style="border-color: var(--color-alerte); color: var(--color-alerte)">À corriger</span>
                    @endif
                </a>
            @endforeach
        </section>
    @endif

@endsection