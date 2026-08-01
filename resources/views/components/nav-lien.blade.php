@props(['route', 'icon' => null])

<a href="{{ route($route) }}"
   class="nav-lien"
   @if (request()->routeIs(str_replace('.index', '.*', $route))) aria-current="page" @endif>
    @if ($icon)
        <x-icone :name="$icon" class="size-4 shrink-0" />
    @endif
    {{ $slot }}
</a>