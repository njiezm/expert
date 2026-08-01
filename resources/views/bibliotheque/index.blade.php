@extends('layouts.app')

@section('titre', 'Bibliothèque')
@section('sous-titre', $total.' documents indexés — recherche dans le texte intégral')

@section('contenu')

    <form method="GET" class="mb-5 space-y-3">
        <div class="flex gap-2">
            <div class="relative flex-1">
                <x-icone name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 texte-faible" />
                <input type="search" name="q" value="{{ $recherche }}"
                       class="champ !pl-9" placeholder="Rechercher dans les titres et le contenu des PDF…">
            </div>
            <button class="btn btn-accent">Chercher</button>
        </div>

        <div class="flex flex-wrap gap-1.5">
            <a href="{{ route('bibliotheque.index') }}"
               class="btn {{ ! request('matiere') && ! request('type') ? 'btn-accent' : 'btn-fantome' }} !py-1 text-[11px]">
                Tout
            </a>
            @foreach ($subjects as $s)
                <a href="{{ route('bibliotheque.index', array_filter(['matiere' => $s->slug, 'q' => $recherche])) }}"
                   class="btn {{ request('matiere') === $s->slug ? 'btn-accent' : 'btn-fantome' }} !py-1 text-[11px]">
                    <span class="size-1.5 rounded-full" style="background: {{ $s->color }}"></span>{{ $s->code }}
                </a>
            @endforeach
            <span class="mx-1 w-px" style="background: var(--bordure)"></span>
            @foreach ($kinds as $valeur => $libelle)
                <a href="{{ route('bibliotheque.index', array_filter(['type' => $valeur, 'matiere' => request('matiere'), 'q' => $recherche])) }}"
                   class="btn {{ request('type') === $valeur ? 'btn-accent' : 'btn-fantome' }} !py-1 text-[11px]">
                    {{ $libelle }}
                </a>
            @endforeach
        </div>
    </form>

    @if ($recherche !== '')
        <p class="mb-3 text-xs texte-doux">
            {{ $resources->total() }} résultat{{ $resources->total() > 1 ? 's' : '' }} pour « {{ $recherche }} »
        </p>
    @endif

    @forelse ($resources as $doc)
        <a href="{{ route('bibliotheque.show', array_filter(['resource' => $doc->id, 'q' => $recherche])) }}"
           class="carte mb-2 flex flex-wrap items-center gap-3 px-4 py-3 transition-colors hover:bg-[var(--surface-survol)]">

            @if ($doc->subject)
                <span class="size-2 shrink-0 rounded-full" style="background: {{ $doc->subject->color }}"
                      title="{{ $doc->subject->code }}"></span>
            @else
                <span class="size-2 shrink-0 rounded-full" style="background: var(--bordure)"></span>
            @endif

            <div class="min-w-0 flex-1">
                <p class="truncate text-sm texte-fort">{{ $doc->title }}</p>
                <p class="truncate text-[11px] texte-faible">{{ $doc->filename }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-1.5">
                @if ($doc->subject)
                    <span class="puce">{{ $doc->subject->code }}</span>
                @endif
                <span class="puce" @if ($doc->kind === 'copie') style="border-color: var(--color-lacune); color: var(--color-lacune-fort)" @endif>
                    {{ $doc->kind_label }}
                </span>
                @if ($doc->is_solution)
                    <span class="puce" style="border-color: var(--color-acquis); color: var(--color-acquis-fort)">Corrigé</span>
                @endif
                @if ($doc->is_scan)
                    <span class="puce">Scan</span>
                @endif
                @if ($doc->page_count)
                    <span class="text-[11px] tabulaire texte-faible">{{ $doc->page_count }} p.</span>
                @endif
                <span class="w-16 text-right text-[11px] tabulaire texte-faible">{{ $doc->size_human }}</span>
            </div>
        </a>
    @empty
        <x-vide icon="search" titre="Aucun document ne correspond">
            Essayez un autre terme, ou retirez les filtres de matière et de type.
        </x-vide>
    @endforelse

    <div class="mt-5">{{ $resources->links() }}</div>

@endsection