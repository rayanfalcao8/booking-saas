@props([
    'title' => 'Reservix',
    'description' => 'Reservix simplifie la prise de rendez-vous et l’organisation de votre équipe.',
    'bodyClass' => '',
])

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="{{ $description }}">
        <meta name="color-scheme" content="light">
        <meta name="theme-color" content="#f7f8fc">
        <title>{{ $title }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="{{ $bodyClass }}">
        {{ $slot }}
    </body>
</html>
