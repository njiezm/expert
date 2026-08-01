@props([
    /* Nom du champ caché qui portera le JSON du diagramme. */
    'name' => 'diagram',
    'value' => null,
    'lecture' => false,
])

@php
    $champId = 'schema-champ-'.Str::random(6);
    $json = is_array($value) ? json_encode($value) : ($value ?: '');
@endphp

<div {{ $attributes }}>
    @unless ($lecture)
        <input type="hidden" id="{{ $champId }}" name="{{ $name }}" value="{{ $json }}">
    @endunless

    <div data-schema
         @unless ($lecture) data-schema-champ="{{ $champId }}" @else data-schema-lecture @endunless
         data-schema-valeur="{{ $json }}"></div>

    @unless ($lecture)
        <div class="mt-2 flex flex-wrap items-start gap-2 text-[11px] leading-relaxed texte-faible">
            <x-icone name="target" class="mt-0.5 size-3.5 shrink-0" style="color: var(--accent)" />
            <p class="flex-1">
                Le barème compte <strong class="texte-doux">les boîtes, les traits typés et le nom des patrons</strong>.
                En janvier, un plan indenté a valu zéro sur les quinze points de conception :
                « il était demandé un schéma ».
            </p>
        </div>
    @endunless
</div>