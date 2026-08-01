@props(['name'])

@php
    /* Jeu d'icônes au trait, 24×24, stroke currentColor. */
    $paths = [
        'grid'      => '<path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/>',
        'calendar'  => '<path d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path d="M4 9h16M9 3v3M15 3v3"/>',
        'pulse'     => '<path d="M3 12h4l2.5-7 4 14L16 12h5"/>',
        'layers'    => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
        'clock'     => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'book'      => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H19v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 18.5A2.5 2.5 0 0 1 6.5 16H19v5H6.5A2.5 2.5 0 0 1 4 18.5z"/>',
        'contrast'  => '<circle cx="12" cy="12" r="8.5"/><path d="M12 3.5v17a8.5 8.5 0 0 0 0-17z" fill="currentColor" stroke="none"/>',
        'bulb'      => '<path d="M9 17.5h6M10 21h4"/><path d="M12 3a6 6 0 0 0-3.5 10.9c.5.4.8.9.9 1.5l.1.6h5l.1-.6c.1-.6.4-1.1.9-1.5A6 6 0 0 0 12 3"/>',
        'sigma'     => '<path d="M18 5H6l6 7-6 7h12"/>',
        'steps'     => '<path d="M4 20h4v-5h4v-5h4V5h4"/>',
        'alert'     => '<path d="M12 4.5 2.8 20h18.4z"/><path d="M12 10v4M12 17h.01"/>',
        'target'    => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r=".8" fill="currentColor" stroke="none"/>',
        'search'    => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
        'arrow-r'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-l'   => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        'check'     => '<path d="m5 12.5 4.5 4.5L19 7"/>',
        'x'         => '<path d="m6 6 12 12M18 6 6 18"/>',
        'eye'       => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12"/><circle cx="12" cy="12" r="3"/>',
        'lock'      => '<rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/>',
        'moon'      => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5"/>',
        'sun'       => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'file'      => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/>',
        'flag'      => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
        'play'      => '<path d="M7 4.5v15l13-7.5z"/>',
        'refresh'   => '<path d="M20 11a8 8 0 1 0-.6 4"/><path d="M20 5v6h-6"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'trend'     => '<path d="m3 17 6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
        'scale'     => '<path d="M12 4v16M7 8h10"/><path d="m4 16 3-8 3 8a3 3 0 0 1-6 0M14 16l3-8 3 8a3 3 0 0 1-6 0"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'size-5']) }}
     viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true">
    {!! $paths[$name] ?? $paths['file'] !!}
</svg>