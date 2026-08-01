@extends('layouts.app')

@section('titre', 'Drill mémoire')
@section('sous-titre', 'Répétition espacée — les cartes reviennent juste avant que vous ne les oubliiez')

@section('contenu')

    <div class="mx-auto max-w-3xl">

        <section class="carte-haute mb-6 p-6 text-center">
            <p class="chrono text-5xl font-semibold" style="color: {{ $total > 0 ? 'var(--accent)' : 'var(--color-acquis-fort)' }}">
                {{ $total }}
            </p>
            <p class="mt-2 text-sm texte-doux">
                {{ $total > 0
                    ? 'carte'.($total > 1 ? 's' : '').' à réviser maintenant'
                    : 'Aucune carte à échéance. La mémoire tient.' }}
            </p>

            @if ($total > 0)
                <a href="{{ route('drill.session') }}" class="btn btn-accent mx-auto mt-5">
                    <x-icone name="play" class="size-4" /> Toutes matières
                </a>
            @endif

            <p class="mt-4 text-[11px] texte-faible">
                {{ $revuesAujourdhui }} carte{{ $revuesAujourdhui > 1 ? 's' : '' }} révisée{{ $revuesAujourdhui > 1 ? 's' : '' }} aujourd’hui
            </p>
        </section>

        <h2 class="mb-3 text-sm font-semibold texte-fort">Par matière</h2>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ($subjects as $s)
                @php $n = $due[$s->id] ?? 0; @endphp
                <div class="carte flex items-center gap-3 px-4 py-3.5">
                    <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $s->color }}"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium texte-fort">{{ $s->code }}</p>
                        <p class="truncate text-[11px] texte-faible">{{ $s->name }}</p>
                    </div>
                    @if ($n > 0)
                        <a href="{{ route('drill.session', ['matiere' => $s->id]) }}" class="btn btn-doux shrink-0 text-xs">
                            {{ $n }} <x-icone name="arrow-r" class="size-3.5" />
                        </a>
                    @else
                        <span class="shrink-0 text-xs texte-faible">À jour</span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="carte mt-6 px-5 py-4">
            <h3 class="text-sm font-semibold texte-fort">Comment ça marche</h3>
            <ul class="mt-2.5 space-y-1.5 text-xs leading-relaxed texte-doux">
                <li>— Une carte notée <strong class="texte-fort">Raté</strong> revient dès demain, et son compteur repart de zéro.</li>
                <li>— Les intervalles sont plafonnés à 10 jours : rien ne doit sortir de la fenêtre avant le 28 août.</li>
                <li>— Les cartes rattachées à une erreur commise en examen passent toujours en premier.</li>
                <li>— Au clavier : <kbd class="font-mono">Espace</kbd> retourne la carte, <kbd class="font-mono">1</kbd> à <kbd class="font-mono">4</kbd> la notent.</li>
            </ul>
        </div>
    </div>

@endsection