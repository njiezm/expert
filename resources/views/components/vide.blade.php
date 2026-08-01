@props(['icon' => 'file', 'titre'])

<div {{ $attributes->merge(['class' => 'carte flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <x-icone :name="$icon" class="size-7 texte-faible" />
    <p class="mt-3 text-sm font-medium texte-fort">{{ $titre }}</p>
    @if (trim($slot))
        <p class="mt-1.5 max-w-md text-xs leading-relaxed texte-doux">{{ $slot }}</p>
    @endif
</div>