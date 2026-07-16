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
        <div class="inline-flex rounded-full px-4 py-2 text-sm font-semibold {{ $status === 'success' ? 'bg-emerald-100 text-emerald-800' : ($status === 'confirm' ? 'bg-amber-100 text-amber-900' : 'bg-red-100 text-red-800') }}">
            {{ $status === 'success' ? 'Annulation confirmée' : ($status === 'confirm' ? 'Confirmation requise' : 'Erreur d’annulation') }}
        </div>

        <h1 class="mt-4 text-3xl font-semibold tracking-tight">
            {{ $status === 'success' ? 'Votre réservation est annulée' : ($status === 'confirm' ? 'Annuler cette réservation ?' : 'Impossible d’annuler') }}
        </h1>

        <p class="mt-3 text-sm leading-6 text-stone-600 sm:text-base">{{ $message }}</p>

        @if ($booking)
            <div class="mt-8 grid gap-4 rounded-3xl border border-stone-200 bg-stone-50 p-5 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Business</p>
                    <p class="mt-1 text-base font-medium text-stone-900">{{ $business->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Service</p>
                    <p class="mt-1 text-base font-medium text-stone-900">{{ $booking->service?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Prestataire</p>
                    <p class="mt-1 text-base font-medium text-stone-900">{{ $booking->staff?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Date et heure</p>
                    <p class="mt-1 text-base font-medium text-stone-900">{{ $booking->date }} · {{ $booking->start_time }}</p>
                </div>
            </div>
        @endif

        @if ($status === 'confirm' && $cancelAction)
            <form class="mt-8" method="POST" action="{{ $cancelAction }}">
                @csrf
                <button
                    class="inline-flex items-center justify-center rounded-full bg-red-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-700 focus:ring-offset-2"
                    type="submit"
                >
                    Confirmer l’annulation
                </button>
            </form>
        @endif
    </section>
</main>
</body>
</html>
