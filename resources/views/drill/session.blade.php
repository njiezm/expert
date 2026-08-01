@extends('layouts.app')

@section('titre', 'Drill')
@section('sous-titre', $restantes.' carte'.($restantes > 1 ? 's' : '').' dans la file')

@section('contenu')

    <div class="mx-auto max-w-2xl" data-carte-drill>

        {{-- Contexte de la carte --}}
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <span class="puce" style="border-color: {{ $carte->chapter->subject->color }}; color: {{ $carte->chapter->subject->color }}">
                {{ $carte->chapter->subject->code }}
            </span>
            <span class="puce">{{ \App\Models\Flashcard::KINDS[$carte->kind] ?? $carte->kind }}</span>
            @if ($carte->gap)
                <span class="puce" style="border-color: var(--color-lacune); color: var(--color-lacune-fort)">
                    Erreur d’examen
                </span>
            @endif
            <span class="ml-auto text-xs texte-faible">{{ $carte->chapter->title }}</span>
            <x-lire cible="carte-recto" />
        </div>

        {{-- Recto --}}
        <section class="carte-haute px-6 py-10 text-center">
            <div id="carte-recto" class="prose-cours mx-auto max-w-xl !text-left">{!! Str::markdown($carte->front) !!}</div>

            @if ($carte->hint)
                <button type="button" data-devoiler="indice-carte"
                        class="btn btn-fantome mx-auto mt-6 text-xs">
                    <x-icone name="bulb" class="size-3.5" /> Un indice
                </button>
                <p id="indice-carte" hidden class="mx-auto mt-5 max-w-xl text-left text-xs leading-relaxed texte-doux">
                    {{ $carte->hint }}
                </p>
            @endif
        </section>

        {{-- Verso --}}
        <button type="button" data-retourner data-devoiler="verso-carte"
                class="btn btn-accent mt-4 w-full">
            Retourner <span class="text-[11px] opacity-70">(Espace)</span>
        </button>

        <section id="verso-carte" hidden class="mt-4">
            <div class="carte px-6 py-6" style="border-color: var(--accent)">
                <div id="carte-verso" class="prose-cours">{!! Str::markdown($carte->back) !!}</div>
                <div class="mt-3 border-t bord pt-2.5">
                    <x-lire cible="carte-verso" />
                </div>
            </div>

            <p class="mt-5 text-center text-xs texte-doux">Sans complaisance : avez-vous produit cette réponse, ou l'avez-vous reconnue ?</p>

            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach ($notes as $valeur => $note)
                    @php
                        $teinte = match ($note['tone']) {
                            'lacune' => 'var(--color-lacune)',
                            'alerte' => 'var(--color-alerte)',
                            'acquis' => 'var(--color-acquis)',
                            default  => 'var(--accent)',
                        };
                    @endphp
                    <form method="POST" action="{{ route('drill.noter', $carte) }}">
                        @csrf
                        <input type="hidden" name="note" value="{{ $valeur }}">
                        @if ($subjectId)
                            <input type="hidden" name="matiere" value="{{ $subjectId }}">
                        @endif
                        <button data-note="{{ $valeur }}" class="btn btn-doux w-full flex-col !py-2.5"
                                style="border-color: {{ $teinte }}">
                            <span class="text-sm font-semibold" style="color: {{ $teinte }}">{{ $note['label'] }}</span>
                            <span class="text-[10px] texte-faible">{{ $valeur }}</span>
                        </button>
                    </form>
                @endforeach
            </div>
        </section>

        <div class="mt-6 text-center">
            <a href="{{ route('drill.index') }}" class="text-xs texte-faible hover:underline">Interrompre la session</a>
        </div>
    </div>

@endsection