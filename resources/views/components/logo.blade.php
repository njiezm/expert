@props([
    /* Nombre de nœuds allumés (0 à 5) : une matière maîtrisée allume son nœud. */
    'lit' => 0,
    'size' => 40,
    'wordmark' => false,
])

@php
    /* Les cinq nœuds descendent du zénith vers le nadir, le long de l'axe. */
    $nodes = [7, 15.5, 24, 32.5, 41];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg
        width="{{ $size }}" height="{{ $size * 1.2 }}" viewBox="0 0 40 48"
        fill="none" xmlns="http://www.w3.org/2000/svg"
        role="img" aria-label="Méridien"
        class="shrink-0"
    >
        {{-- L'horizon : la ligne que le méridien franchit au zénith --}}
        <path d="M2.5 24H37.5" stroke="currentColor" stroke-opacity=".22" stroke-width="1.1" stroke-linecap="round"/>

        {{-- Le globe vu par la tranche --}}
        <ellipse cx="20" cy="24" rx="11.5" ry="21" stroke="currentColor" stroke-opacity=".45" stroke-width="1.4"/>
        <circle cx="20" cy="24" r="21" stroke="currentColor" stroke-opacity=".16" stroke-width="1.1"/>

        {{-- L'axe méridien --}}
        <path d="M20 3V45" stroke="currentColor" stroke-opacity=".3" stroke-width="1.1" stroke-linecap="round"/>

        {{-- Les cinq nœuds : les matières. Ils s'allument à mesure que la maîtrise monte. --}}
        @foreach ($nodes as $i => $y)
            @php $on = $i < $lit; @endphp
            <circle
                cx="20" cy="{{ $y }}" r="{{ $i === 0 ? 3.1 : 2.5 }}"
                fill="{{ $on ? 'var(--accent)' : 'var(--fond)' }}"
                stroke="{{ $on ? 'var(--accent)' : 'currentColor' }}"
                stroke-opacity="{{ $on ? 1 : .55 }}"
                stroke-width="1.5"
            />
        @endforeach
    </svg>

    @if ($wordmark)
        <span class="flex flex-col leading-none">
            <span class="text-[15px] font-semibold tracking-[0.18em] texte-fort">MÉRIDIEN</span>
            <span class="mt-1 text-[9.5px] tracking-[0.14em] uppercase texte-faible">Rattrapage M1 · Août 2026</span>
        </span>
    @endif
</span>