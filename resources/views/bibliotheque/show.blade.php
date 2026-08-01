@extends('layouts.app')

@section('titre', $resource->title)
@section('sous-titre')
    {{ $resource->subject?->code }} · {{ $resource->kind_label }}
    @if ($resource->page_count) · {{ $resource->page_count }} pages @endif
    · {{ $resource->size_human }}
@endsection

@section('actions-entete')
    <button type="button" data-plein-ecran class="btn btn-fantome text-xs" title="Basculer en pleine page">
        <x-icone name="eye" class="size-3.5" /> <span data-libelle-plein-ecran>Pleine page</span>
    </button>
    <a href="{{ $resource->url }}" target="_blank" rel="noopener" class="btn btn-fantome text-xs">
        Nouvel onglet
    </a>
@endsection

@section('contenu')

    <div class="mb-4 flex flex-wrap items-center gap-2" data-masquer-en-plein-ecran>
        <a href="{{ route('bibliotheque.index', array_filter(['matiere' => $resource->subject?->slug])) }}"
           class="btn btn-fantome !px-2.5 !py-1.5">
            <x-icone name="arrow-l" class="size-4" />
        </a>
        @if ($resource->subject)
            <span class="puce" style="border-color: {{ $resource->subject->color }}; color: {{ $resource->subject->color }}">
                {{ $resource->subject->code }}
            </span>
        @endif
        <span class="puce">{{ $resource->kind_label }}</span>
        @if ($resource->is_solution)
            <span class="puce" style="border-color: var(--color-acquis); color: var(--color-acquis-fort)">Corrigé</span>
        @endif
        @if ($resource->is_scan)
            <span class="puce">Document scanné — texte non extractible</span>
        @endif
        @if ($resource->chapter)
            <a href="{{ route('chapitres.show', [$resource->subject, $resource->chapter]) }}" class="puce hover:underline">
                {{ $resource->chapter->title }}
            </a>
        @endif
    </div>

    {{-- Extraits de recherche --}}
    @if ($extraits)
        <section class="carte mb-4 px-5 py-4" data-masquer-en-plein-ecran>
            <h2 class="mb-2.5 text-xs font-semibold uppercase tracking-[0.1em] texte-faible">
                Occurrences de « {{ $terme }} »
            </h2>
            <div class="space-y-2.5">
                @foreach ($extraits as $extrait)
                    <p class="border-l-2 pl-3 font-mono text-[12px] leading-relaxed texte-doux"
                       style="border-color: var(--accent)">
                        …{!! str_ireplace(
                            e($terme),
                            '<mark style="background: var(--accent-doux); color: var(--texte-fort)">'.e($terme).'</mark>',
                            e($extrait)
                        ) !!}…
                    </p>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Visionneuse pleine largeur. Un PDF de cours se lit sur toute la page :
         la liste des documents voisins passe dessous plutôt qu'en colonne. --}}
    <section id="cadre-lecteur" class="carte overflow-hidden"
             style="height: calc(100vh - 11rem); min-height: 38rem">
        @if ($resource->is_viewable)
            <iframe src="{{ $resource->url }}#zoom=page-width&amp;navpanes=0"
                    class="block size-full border-0" title="{{ $resource->title }}"></iframe>
        @else
            <x-vide icon="file" titre="Aperçu indisponible pour ce format" class="h-full !border-0">
                Ce fichier ({{ strtoupper($resource->extension) }}) ne s’affiche pas dans le navigateur.
                Utilisez « Nouvel onglet » pour le télécharger.
            </x-vide>
        @endif
    </section>

    @if ($voisins->count())
        <section class="mt-5" data-masquer-en-plein-ecran>
            <h2 class="mb-2.5 text-sm font-semibold texte-fort">
                Même catégorie <span class="ml-1 texte-faible">({{ $voisins->count() }})</span>
            </h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($voisins as $v)
                    <a href="{{ route('bibliotheque.show', $v) }}"
                       class="carte max-w-xs truncate px-3 py-1.5 text-xs texte-doux hover:bg-[var(--surface-survol)]">
                        {{ $v->title }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @push('scripts')
        <script>
            /* Pleine page : le lecteur sort du flux et couvre la fenêtre.
               Échap ou un second clic ramène à la mise en page normale. */
            (function () {
                const cadre = document.getElementById('cadre-lecteur');
                if (!cadre) return;

                const basculer = (actif) => {
                    cadre.classList.toggle('fixed', actif);
                    cadre.classList.toggle('inset-0', actif);
                    cadre.classList.toggle('z-50', actif);
                    cadre.classList.toggle('rounded-none', actif);
                    cadre.style.height = actif ? '100vh' : 'calc(100vh - 11rem)';
                    document.body.style.overflow = actif ? 'hidden' : '';
                    document.querySelectorAll('[data-masquer-en-plein-ecran]')
                        .forEach((el) => { el.hidden = actif; });
                    document.querySelectorAll('[data-libelle-plein-ecran]')
                        .forEach((el) => { el.textContent = actif ? 'Réduire' : 'Pleine page'; });
                };

                document.querySelector('[data-plein-ecran]')?.addEventListener('click', () => {
                    basculer(!cadre.classList.contains('fixed'));
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && cadre.classList.contains('fixed')) basculer(false);
                });
            })();
        </script>
    @endpush

@endsection