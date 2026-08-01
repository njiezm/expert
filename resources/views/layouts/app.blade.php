<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titre', 'Tableau de bord') · Méridien</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Appliqué avant le premier rendu pour éviter le flash de thème.
        // Le clair est la valeur par défaut ; le sombre est un choix explicite.
        (function () {
            if (localStorage.getItem('meridien-theme') === 'sombre') {
                document.documentElement.classList.add('theme-sombre');
            }
        })();
    </script>
</head>
<body class="min-h-screen">

<div class="flex min-h-screen">

    {{-- ================= Barre latérale ================= --}}
    <aside class="hidden w-64 shrink-0 flex-col border-r bord lg:flex"
           style="background-color: var(--fond-voile);">

        <div class="px-5 py-5">
            <a href="{{ route('tableau-de-bord') }}" class="block">
                <x-logo :lit="$naveMasteredCount ?? 0" :size="34" wordmark class="texte-doux" />
            </a>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-6">

            <div class="space-y-0.5">
                <x-nav-lien route="tableau-de-bord" icon="grid">Tableau de bord</x-nav-lien>
                <x-nav-lien route="planning.index" icon="calendar">Planning</x-nav-lien>
                <x-nav-lien route="diagnostic.index" icon="pulse">Diagnostic</x-nav-lien>
                <x-nav-lien route="drill.index" icon="layers">
                    Drill
                    @if (($navDueCards ?? 0) > 0)
                        <span class="ml-auto rounded-full px-1.5 py-0.5 text-[10px] font-bold tabulaire"
                              style="background: var(--accent); color: var(--accent-contraste);">{{ $navDueCards }}</span>
                    @endif
                </x-nav-lien>
                <x-nav-lien route="examens.index" icon="clock">Examens blancs</x-nav-lien>
                <x-nav-lien route="bibliotheque.index" icon="book">Bibliothèque</x-nav-lien>
            </div>

            <div>
                <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.14em] texte-faible">
                    Matières
                </p>
                <div class="space-y-0.5">
                    @foreach ($navSubjects ?? [] as $s)
                        <a href="{{ route('matieres.show', $s->slug) }}"
                           class="nav-lien"
                           @if (request()->routeIs('matieres.*') && request()->route('subject')?->id === $s->id) aria-current="page" @endif>
                            <span class="size-2 shrink-0 rounded-full" style="background: {{ $s->color }}"></span>
                            <span class="truncate">{{ $s->code }}</span>
                            <span class="ml-auto text-[11px] tabulaire texte-faible">{{ $s->mastery }}%</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </nav>

        <div class="border-t bord p-3">
            <button type="button" data-bascule-theme
                    class="nav-lien w-full text-left">
                <x-icone name="contrast" class="size-4" />
                <span data-libelle-theme>Thème clair</span>
            </button>
        </div>
    </aside>

    {{-- ================= Contenu ================= --}}
    <div class="flex min-w-0 flex-1 flex-col">

        <header class="sticky top-0 z-30 border-b bord backdrop-blur"
                style="background-color: color-mix(in oklab, var(--fond) 88%, transparent);">
            <div class="flex h-14 items-center gap-4 px-5 sm:px-7">

                <a href="{{ route('tableau-de-bord') }}" class="lg:hidden">
                    <x-logo :size="26" class="texte-doux" />
                </a>

                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-[15px] font-semibold texte-fort">@yield('titre', 'Tableau de bord')</h1>
                    @hasSection('sous-titre')
                        <p class="truncate text-xs texte-doux">@yield('sous-titre')</p>
                    @endif
                </div>

                @yield('actions-entete')

                {{-- Compte à rebours vers la prochaine épreuve --}}
                @if ($navNextExam ?? null)
                    <a href="{{ route('matieres.show', $navNextExam->slug) }}"
                       class="hidden items-center gap-2.5 rounded-lg border bord px-3 py-1.5 sm:flex"
                       style="background-color: var(--surface);">
                        <span class="size-2 rounded-full" style="background: {{ $navNextExam->color }}"></span>
                        <span class="text-xs texte-doux">{{ $navNextExam->code }}</span>
                        <span class="chrono text-sm font-semibold"
                              style="color: {{ $navNextExam->days_until_exam <= 3 ? 'var(--color-lacune-fort)' : 'var(--accent)' }}">
                            J−{{ max(0, $navNextExam->days_until_exam) }}
                        </span>
                    </a>
                @endif
            </div>
        </header>

        <main class="flex-1 px-5 py-6 sm:px-7">
            @if (session('succes'))
                <div class="mb-5 rounded-lg border px-4 py-2.5 text-sm"
                     style="border-color: var(--color-acquis); background: color-mix(in oklab, var(--color-acquis) 12%, transparent);">
                    {{ session('succes') }}
                </div>
            @endif

            @yield('contenu')
        </main>
    </div>
</div>

{{-- Navigation mobile --}}
<nav class="fixed inset-x-0 bottom-0 z-40 flex border-t bord lg:hidden"
     style="background-color: var(--fond-voile);">
    @foreach ([
        ['tableau-de-bord', 'grid', 'Bord'],
        ['planning.index', 'calendar', 'Planning'],
        ['drill.index', 'layers', 'Drill'],
        ['examens.index', 'clock', 'Examens'],
        ['bibliotheque.index', 'book', 'Docs'],
    ] as [$route, $icon, $label])
        <a href="{{ route($route) }}"
           class="flex flex-1 flex-col items-center gap-0.5 py-2 text-[10px]"
           style="color: {{ request()->routeIs($route) ? 'var(--accent)' : 'var(--texte-faible)' }}">
            <x-icone :name="$icon" class="size-[18px]" />
            {{ $label }}
        </a>
    @endforeach
</nav>
<div class="h-14 lg:hidden"></div>

@stack('scripts')
</body>
</html>