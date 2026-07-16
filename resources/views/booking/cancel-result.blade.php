<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annulation réservation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-stone-50 text-stone-950">
<main class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-8 sm:px-6">
    <section class="w-full rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_80px_rgba(28,25,23,0.08)] sm:p-8">
        <div class="inline-flex rounded-full px-4 py-2 text-sm font-semibold {{ $status === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
            {{ $status === 'success' ? 'Annulation confirmée' : 'Erreur d’annulation' }}
        </div>

        <h1 class="mt-4 text-3xl font-semibold tracking-tight">
            {{ $status === 'success' ? 'Annulation confirmée' : 'Erreur d’annulation' }}
        </h1>

        <p class="mt-3 text-sm leading-6 text-stone-600 sm:text-base">{{ $message }}</p>
    </section>
</main>
</body>
</html>
