@extends('layouts.app')

@section('titre', 'Planning')
@section('sous-titre', 'Rétroplanning jusqu’au '.$fin->translatedFormat('j F Y'))

@section('actions-entete')
    <a href="{{ route('planning.creneaux') }}" class="btn btn-fantome text-xs">Mes disponibilités</a>
    <form method="POST" action="{{ route('planning.recalculer') }}">
        @csrf
        <button class="btn btn-accent text-xs"><x-icone name="refresh" class="size-3.5" /> Recalculer</button>
    </form>
@endsection

@section('contenu')

    {{-- Les cinq échéances --}}
    <section class="carte mb-6 px-5 py-4">
        <h2 class="mb-3 text-sm font-semibold texte-fort">Les cinq échéances</h2>
        <div class="space-y-2">
            @foreach ($examens as $e)
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <span class="size-2 shrink-0 rounded-full" style="background: {{ $e->color }}"></span>
                    <span class="w-10 shrink-0 font-semibold texte-fort">{{ $e->code }}</span>
                    <span class="w-40 shrink-0 texte-doux">
                        {{ $e->exam_at->translatedFormat('l j F') }}
                    </span>
                    <span class="w-28 shrink-0 tabulaire texte-doux">
                        {{ $e->exam_at->format('H\hi') }}–{{ $e->exam_at->copy()->addMinutes($e->exam_duration_min)->format('H\hi') }}
                    </span>
                    <span class="chrono shrink-0 font-semibold"
                          style="color: {{ $e->days_until_exam <= 3 ? 'var(--color-lacune-fort)' : 'var(--accent)' }}">
                        J−{{ max(0, $e->days_until_exam) }}
                    </span>
                    <x-jauge :value="$e->mastery" :color="$e->color" class="ml-auto w-32 shrink-0" height="0.25rem" />
                </div>
            @endforeach
        </div>

        <p class="mt-4 border-t bord pt-3 text-xs leading-relaxed texte-doux">
            <strong class="texte-fort">Le 26 août est le point critique :</strong> AGC de 15 h à 18 h,
            puis SPP de 20 h à 23 h. Six heures d’épreuve dans la journée, dont la matière la plus
            faible en fin de soirée. Le planning réserve la veille aux deux et bloque la journée elle-même.
        </p>
    </section>

    {{-- Équilibre du temps --}}
    @if ($totalMinutes > 0)
        <section class="carte mb-6 px-5 py-4">
            <div class="mb-3 flex items-baseline justify-between">
                <h2 class="text-sm font-semibold texte-fort">Répartition du temps planifié</h2>
                <span class="text-xs tabulaire texte-faible">{{ floor($totalMinutes / 60) }} h au total</span>
            </div>

            <div class="flex h-2.5 overflow-hidden rounded-full" style="background: var(--fond-voile)">
                @foreach ($subjects as $s)
                    @php $min = $repartition[$s->id] ?? 0; @endphp
                    @if ($min > 0)
                        <span style="width: {{ round($min / $totalMinutes * 100, 2) }}%; background: {{ $s->color }}"
                              title="{{ $s->code }} : {{ floor($min / 60) }} h"></span>
                    @endif
                @endforeach
            </div>

            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5">
                @foreach ($subjects as $s)
                    @php $min = $repartition[$s->id] ?? 0; @endphp
                    @continue($min === 0)
                    <span class="flex items-center gap-1.5 text-[11px]">
                        <span class="size-1.5 rounded-full" style="background: {{ $s->color }}"></span>
                        <span class="texte-doux">{{ $s->code }}</span>
                        <span class="tabulaire texte-faible">{{ floor($min / 60) }} h {{ str_pad($min % 60, 2, '0', STR_PAD_LEFT) }}</span>
                    </span>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Agenda jour par jour --}}
    @forelse ($jours as $date => $creneaux)
        @php $d = \Illuminate\Support\Carbon::parse($date); @endphp

        <section class="mb-5">
            <div class="mb-2 flex items-baseline gap-2.5">
                <h2 class="text-sm font-semibold {{ $d->isToday() ? '' : 'texte-doux' }}"
                    style="{{ $d->isToday() ? 'color: var(--accent)' : '' }}">
                    {{ $d->translatedFormat('l j F') }}
                </h2>
                @if ($d->isToday())
                    <span class="puce" style="border-color: var(--accent); color: var(--accent)">Aujourd’hui</span>
                @endif
                <span class="text-[11px] tabulaire texte-faible">
                    {{ $creneaux->sum('minutes') }} min disponibles
                </span>
            </div>

            @foreach ($creneaux as $creneau)
                <div class="carte mb-2 overflow-hidden">
                    <div class="flex items-center gap-3 border-b bord px-4 py-2"
                         style="{{ $creneau->is_locked ? 'background: color-mix(in oklab, var(--color-lacune) 12%, transparent)' : '' }}">
                        <span class="chrono text-xs texte-doux">
                            {{ substr($creneau->starts_at, 0, 5) }}–{{ substr($creneau->ends_at, 0, 5) }}
                        </span>
                        <span class="puce">{{ $creneau->label_text }}</span>
                        @if ($creneau->note)
                            <span class="text-xs font-semibold" style="color: var(--color-lacune-fort)">{{ $creneau->note }}</span>
                        @endif
                    </div>

                    @forelse ($creneau->blocks as $bloc)
                        <div class="flex items-center gap-3 px-4 py-2.5 {{ $bloc->status === 'fait' ? 'opacity-50' : '' }}">
                            <span class="size-2 shrink-0 rounded-full" style="background: {{ $bloc->subject->color }}"></span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-baseline gap-x-2">
                                    <span class="text-sm texte-fort {{ $bloc->status === 'fait' ? 'line-through' : '' }}">
                                        {{ $bloc->activity_label }}
                                    </span>
                                    <span class="text-xs texte-doux">{{ $bloc->subject->code }}</span>
                                    @if ($bloc->chapter)
                                        <span class="truncate text-xs texte-faible">· {{ $bloc->chapter->title }}</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 line-clamp-1 text-[11px] texte-faible">{{ $bloc->rationale }}</p>
                            </div>

                            <span class="shrink-0 text-[11px] tabulaire texte-faible">{{ $bloc->planned_minutes }}'</span>

                            <form method="POST" action="{{ route('planning.bloc', $bloc) }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="status" value="{{ $bloc->status === 'fait' ? 'planifie' : 'fait' }}">
                                <button class="btn btn-fantome !px-1.5 !py-1">
                                    <x-icone name="check" class="size-3.5"
                                             style="color: {{ $bloc->status === 'fait' ? 'var(--color-acquis-fort)' : 'inherit' }}" />
                                </button>
                            </form>
                        </div>
                    @empty
                        @unless ($creneau->is_locked)
                            <p class="px-4 py-2.5 text-xs texte-faible">Créneau libre — lancez le recalcul pour le remplir.</p>
                        @endunless
                    @endforelse
                </div>
            @endforeach
        </section>
    @empty
        <x-vide icon="calendar" titre="Aucun créneau défini">
            Commencez par déclarer le type de chaque journée d’ici au 28 août : entreprise, télétravail,
            congé ou week-end. Le moteur en déduira les plages horaires puis y répartira le travail.
        </x-vide>
        <a href="{{ route('planning.creneaux') }}" class="btn btn-accent mt-3">Renseigner mes disponibilités</a>
    @endforelse

@endsection