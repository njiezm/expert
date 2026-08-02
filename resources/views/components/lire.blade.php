@props([
    /* Identifiant de l'élément dont le texte doit être lu. */
    'cible',
    'label' => 'Écouter',
    'vitesse' => false,
])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }} data-voix-bloc>
    <button type="button" data-lire="{{ $cible }}" data-lire-actif="0" data-lire-label="{{ $label }}"
            class="btn btn-fantome !px-2 !py-1 text-[11px]"
            title="Lecture vocale — pause avec P, arrêt avec Échap">
        <x-icone name="play" class="size-3" />
        <span data-lire-libelle>{{ $label }}</span>
    </button>

    @if ($vitesse)
        <select data-voix-vitesse
                class="rounded-md border bord bg-transparent px-1.5 py-1 text-[11px] texte-doux"
                title="Vitesse de lecture">
            <option value="0.8">0,8×</option>
            <option value="1">1×</option>
            <option value="1.25">1,25×</option>
            <option value="1.5">1,5×</option>
            <option value="1.75">1,75×</option>
            <option value="2">2×</option>
        </select>
    @endif
</span>