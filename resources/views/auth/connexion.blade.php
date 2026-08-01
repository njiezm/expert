<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · Méridien</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center px-5">

    <div class="w-full max-w-sm">

        <div class="mb-8 flex flex-col items-center text-center">
            <x-logo :lit="1" :size="52" class="texte-doux" />
            <h1 class="mt-5 text-lg font-semibold tracking-[0.2em] texte-fort">MÉRIDIEN</h1>
            <p class="mt-2 text-xs leading-relaxed texte-doux">
                Cinq matières, une ligne, une date.<br>
                Dernière épreuve le 28 août 2026.
            </p>
        </div>

        <form method="POST" action="{{ route('connexion') }}" class="carte-haute space-y-4 p-6">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-xs font-medium texte-doux">Adresse e-mail</label>
                <input id="email" name="email" type="email" required autofocus
                       value="{{ old('email') }}" class="champ" autocomplete="username">
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-xs font-medium texte-doux">Mot de passe</label>
                <input id="password" name="password" type="password" required
                       class="champ" autocomplete="current-password">
            </div>

            @error('email')
                <p class="text-xs" style="color: var(--color-lacune-fort)">{{ $message }}</p>
            @enderror

            <label class="flex items-center gap-2 text-xs texte-doux">
                <input type="checkbox" name="memoriser" value="1" checked
                       class="size-3.5 rounded border bord" style="accent-color: var(--accent)">
                Rester connecté
            </label>

            <button type="submit" class="btn btn-accent w-full">Entrer</button>
        </form>

        <p class="mt-6 text-center text-[11px] texte-faible">Accès personnel — un seul compte.</p>
    </div>

</body>
</html>