@props([
    'value' => 0,
    'color' => null,
    'height' => '0.375rem',
])

@php
    $v = max(0, min(100, (int) $value));
    /* Sans teinte imposée, la couleur suit le niveau : rouge → ambre → vert. */
    $teinte = $color ?? match (true) {
        $v >= 80 => 'var(--color-acquis)',
        $v >= 50 => 'var(--accent)',
        $v >= 25 => 'var(--color-alerte)',
        default  => 'var(--color-lacune)',
    };
@endphp

<div {{ $attributes->merge(['class' => 'jauge']) }}
     style="height: {{ $height }}"
     role="progressbar" aria-valuenow="{{ $v }}" aria-valuemin="0" aria-valuemax="100">
    <span style="width: {{ $v }}%; background: {{ $teinte }}"></span>
</div>