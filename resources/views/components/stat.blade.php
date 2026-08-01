@props([
    'label',
    'value',
    'hint' => null,
    'tone' => null,   /* accent | acquis | lacune */
])

@php
    $couleur = match ($tone) {
        'accent' => 'var(--accent)',
        'acquis' => 'var(--color-acquis-fort)',
        'lacune' => 'var(--color-lacune-fort)',
        default  => 'var(--texte-fort)',
    };
@endphp

<div {{ $attributes->merge(['class' => 'carte px-4 py-3.5']) }}>
    <p class="text-[10.5px] font-semibold uppercase tracking-[0.12em] texte-faible">{{ $label }}</p>
    <p class="mt-1.5 text-2xl font-semibold tabulaire leading-none" style="color: {{ $couleur }}">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1.5 text-xs texte-doux">{{ $hint }}</p>
    @endif
</div>