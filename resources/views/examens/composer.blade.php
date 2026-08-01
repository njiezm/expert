{{-- Écran de composition : volontairement hors du layout habituel.
     Pas de barre latérale, pas de navigation, pas de compteur de cartes —
     rien d'autre que le sujet, la copie et le temps qui tourne. --}}
<!DOCTYPE html>
<html lang="fr" class="{{ $session->mode === 'distance_nuit' ? 'mode-nuit-profonde' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Composition · {{ $examen->subject->code }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">

<form method="POST" action="{{ route('examens.rendre', $session) }}" id="copie">
    @csrf

    {{-- Bandeau fixe : le chronomètre ne quitte jamais l'écran --}}
    <header class="sticky top-0 z-30 border-b bord backdrop-blur"
            style="background-color: color-mix(in oklab, var(--fond) 92%, transparent)">
        <div class="mx-auto flex max-w-4xl items-center gap-4 px-5 py-3">
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold texte-fort">{{ $examen->title }}</p>
                <p class="text-[11px] texte-faible">
                    {{ $examen->subject->code }} ·
                    {{ $session->mode === 'distance_nuit' ? 'Épreuve nocturne à distance' : 'Amphi, en journée' }}
                </p>
            </div>

            <div class="text-right">
                <p class="chrono text-2xl font-semibold leading-none"
                   data-chrono-fin="{{ $finTimestamp }}" data-chrono-formulaire="copie"
                   style="color: var(--accent)">--:--:--</p>
                <p class="mt-0.5 text-[10px] uppercase tracking-[0.1em] texte-faible">temps restant</p>
            </div>

            <button type="submit" class="btn btn-fantome shrink-0 text-xs">
                <x-icone name="flag" class="size-3.5" /> Rendre
            </button>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-5 py-6">

        @if ($examen->instructions)
            <div class="carte mb-6 px-5 py-4">
                <div class="prose-cours !text-sm">{!! Str::markdown($examen->instructions) !!}</div>
            </div>
        @endif

        @foreach ($questions as $question)
            <section class="mb-8">
                <div class="mb-3 flex items-baseline gap-3">
                    <span class="text-sm font-semibold texte-fort">{{ $question->number }}</span>
                    <span class="text-xs texte-faible">
                        {{ rtrim(rtrim(number_format((float) $question->points, 1, ',', ''), '0'), ',') }} point{{ $question->points > 1 ? 's' : '' }}
                    </span>
                </div>

                <div class="carte mb-3 px-5 py-4">
                    <div class="prose-cours">{!! Str::markdown($question->statement) !!}</div>
                </div>

                <textarea name="reponses[{{ $question->id }}]" rows="8"
                          class="champ font-mono text-[13px] leading-relaxed"
                          data-brouillon="exam-{{ $session->id }}-q{{ $question->id }}"
                          data-auto-hauteur
                          placeholder="Votre réponse…">{{ $reponses[$question->id]->answer ?? '' }}</textarea>
            </section>
        @endforeach

        <div class="border-t bord pt-6">
            <button type="submit" class="btn btn-accent w-full">
                <x-icone name="flag" class="size-4" /> Rendre la copie
            </button>
            <p class="mt-3 text-center text-[11px] texte-faible">
                Vos réponses sont sauvegardées localement au fil de la frappe.
            </p>
        </div>
    </main>
</form>

</body>
</html>