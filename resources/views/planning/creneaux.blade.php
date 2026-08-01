@extends('layouts.app')

@section('titre', 'Mes disponibilités')
@section('sous-titre', 'Déclarez le type de chaque journée — le moteur en déduit les plages horaires')

@section('contenu')

    <div class="mx-auto max-w-3xl">

        <div class="carte mb-5 px-5 py-4">
            <h2 class="text-sm font-semibold texte-fort">Plages générées par type de journée</h2>
            <div class="mt-3 grid gap-2 text-xs sm:grid-cols-2">
                <p class="texte-doux"><strong class="texte-fort">Entreprise</strong> — soirée 19h30–22h30 <span class="texte-faible">(3 h)</span></p>
                <p class="texte-doux"><strong class="texte-fort">Télétravail</strong> — 7h–8h45, 12h15–13h45, 18h30–22h30 <span class="texte-faible">(7 h 15)</span></p>
                <p class="texte-doux"><strong class="texte-fort">Week-end</strong> — 9h–12h30, 14h–18h, 20h–22h30 <span class="texte-faible">(10 h)</span></p>
                <p class="texte-doux"><strong class="texte-fort">Congé</strong> — 9h–12h30, 14h–18h, 20h–22h30 <span class="texte-faible">(10 h)</span></p>
            </div>
        </div>

        <form method="POST" action="{{ route('planning.creneaux.enregistrer') }}">
            @csrf

            <div class="carte overflow-hidden">
                @foreach ($jours as $cle => $jour)
                    @php $examensDuJour = $examens[$cle] ?? collect(); @endphp

                    <div class="flex flex-wrap items-center gap-3 border-b bord px-4 py-2.5 last:border-b-0
                                {{ $jour['date']->isToday() ? 'bg-[var(--accent-doux)]' : '' }}">

                        <div class="w-40 shrink-0">
                            <p class="text-sm {{ $examensDuJour->count() ? 'font-semibold' : '' }} texte-fort">
                                {{ $jour['date']->translatedFormat('D j M') }}
                            </p>
                            @if ($jour['date']->isToday())
                                <p class="text-[10px]" style="color: var(--accent)">Aujourd’hui</p>
                            @endif
                        </div>

                        @if ($examensDuJour->count())
                            <div class="flex flex-1 flex-wrap gap-1.5">
                                @foreach ($examensDuJour as $e)
                                    <span class="puce" style="border-color: {{ $e->color }}; color: {{ $e->color }}">
                                        ÉPREUVE {{ $e->code }} · {{ $e->exam_at->format('H\hi') }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="ml-auto flex gap-1">
                            @foreach (['soiree' => 'Entreprise', 'teletravail' => 'Télétravail', 'weekend' => 'Week-end', 'conge' => 'Congé'] as $valeur => $libelle)
                                <label class="cursor-pointer">
                                    <input type="radio" name="type[{{ $cle }}]" value="{{ $valeur }}"
                                           class="peer sr-only" @checked($jour['type'] === $valeur)>
                                    <span class="block rounded-md border bord px-2.5 py-1 text-[11px] texte-doux
                                                 transition-colors hover:bg-[var(--surface-survol)]
                                                 peer-checked:border-[var(--accent)] peer-checked:bg-[var(--accent-doux)]
                                                 peer-checked:font-semibold peer-checked:text-[var(--texte-fort)]">
                                        {{ $libelle }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="sticky bottom-4 mt-5">
                <button type="submit" class="btn btn-accent w-full shadow-lg">
                    Enregistrer et recalculer le planning
                </button>
            </div>
        </form>

        <p class="mt-4 text-center text-[11px] leading-relaxed texte-faible">
            Les créneaux d’épreuve sont verrouillés automatiquement et ne peuvent pas recevoir de travail.
        </p>
    </div>

@endsection