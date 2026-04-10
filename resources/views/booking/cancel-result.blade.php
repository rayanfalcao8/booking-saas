<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annulation réservation</title>
    <style>
        body {
            margin: 0;
            font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            max-width: 520px;
            width: 100%;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .ok {
            color: #166534;
        }

        .error {
            color: #991b1b;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1 class="{{ $status === 'success' ? 'ok' : 'error' }}">
            {{ $status === 'success' ? 'Annulation confirmée' : 'Erreur d’annulation' }}
        </h1>
        <p>{{ $message }}</p>
    </div>
</div>
</body>
</html>
