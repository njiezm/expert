@extends('layouts.app')

@section('titre', 'Diagnostic — '.$subject->code)
@section('sous-titre', $subject->name)

@section('contenu')

    <div class="mx-auto max-w-4xl">

        @if ($paper)
            <section class="carte-haute mb-6 overflow-hidden">
                <div class="h-1" style="background: {{ $subject->color }}"></div>
                <div class="grid gap-5 p-5 sm:grid-cols-[auto_1fr] sm:items-center">
                    <div class="text-center sm:text-left">
                        <p class="chrono text-4xl font-semibold"
                           style="color: {{ $paper->grade < 5 ? 'var(--color-lacune-fort)' : 'var(--color-alerte)' }}">
                            {{ rtrim(rtrim(number_format((float) $paper->grade, 2, ',', ''), '0'), ',') }}
                        </p>
                        <p class="text-xs texte-faible">sur 20</p>
                    </div>
                    <div>
                        <p class="text-sm texte-doux">
                            {{ $paper->session_label }} · {{ $paper->centre }}
                            @if ($paper->sat_on) · {{ $paper->sat_on->translatedFormat('j F Y') }} @endif
                        </p>
                        @if ($paper->appreciation)
                            <p class="mt-2 text-sm italic" style="color: var(--color-lacune-fort)">
                                Appréciation du correcteur : « {{ $paper->appreciation }} »
                            </p>
                        @endif
                        @if ($paper->resource)
                            <a href="{{ route('bibliotheque.show', $paper->resource) }}"
                               class="mt-3 inline-flex items-center gap-1.5 text-xs texte-doux hover:underline">
                                <x-icone name="file" class="size-3.5" /> Ouvrir ma copie ({{ $paper->pages }} pages)
                            </a>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @forelse ($lacunes as $kind => $groupe)
            <section class="mb-6">
                <h2 class="mb-3 text-sm font-semibold texte-fort">
                    {{ \App\Models\Gap::KINDS[$kind] ?? $kind }}
                    <span class="ml-1 texte-faible">({{ $groupe->count() }})</span>
                </h2>

                @foreach ($groupe as $lacune)
                    <div class="carte mb-2 px-4 py-3.5">
                        <div class="flex items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-medium texte-fort">{{ $lacune->title }}</p>
                                    @if ($lacune->chapter)
                                        <a href="{{ route('chapitres.show', [$subject, $lacune->chapter]) }}"
                                           class="puce hover:underline">{{ $lacune->chapter->title }}</a>
                                    @endif
                                    @if ($lacune->status === 'maitrisee')
                                        <span class="puce" style="border-color: var(--color-acquis); color: var(--color-acquis-fort)">
                                            Refermée
                                        </span>
                                    @endif
                                </div>

                                @if ($lacune->evidence)
                                    <p class="mt-1.5 text-xs italic texte-faible">Sur la copie : « {{ $lacune->evidence }} »</p>
                                @endif
                                @if ($lacune->explanation)
                                    <p class="mt-1.5 text-xs leading-relaxed texte-doux">{{ $lacune->explanation }}</p>
                                @endif
                                @if ($lacune->remedy)
                                    <p class="mt-1.5 text-xs leading-relaxed" style="color: var(--color-acquis-fort)">
                                        → {{ $lacune->remedy }}
                                    </p>
                                @endif
                            </div>

                            @php $fermee = $lacune->status === 'maitrisee'; @endphp
                            <form method="POST" action="{{ route('lacunes.statut', $lacune) }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="status" value="{{ $fermee ? 'ouverte' : 'maitrisee' }}">
                                <button class="btn btn-fantome whitespace-nowrap text-xs"
                                        @if ($fermee) style="border-color: var(--color-acquis); color: var(--color-acquis-fort)" @endif>
                                    <x-icone :name="$fermee ? 'refresh' : 'check'" class="size-3.5" />
                                    {{ $fermee ? 'Rouvrir' : 'Refermer' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </section>
        @empty
            <x-vide icon="pulse" titre="Analyse détaillée à venir pour {{ $subject->code }}">
                La copie est un scan : chaque page doit être relue visuellement. Les erreurs seront
                listées ici, rattachées à leur chapitre, avec l'annotation exacte du correcteur.
            </x-vide>
        @endforelse
    </div>

@endsection