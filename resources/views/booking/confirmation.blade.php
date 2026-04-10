<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation réservation</title>
    <style>
        body {
            margin: 0;
            font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .wrap {
            max-width: 760px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .row {
            margin-bottom: 10px;
        }

        .label {
            color: #64748b;
            font-size: 13px;
        }

        .value {
            font-weight: 600;
        }

        .btn {
            margin-top: 18px;
            display: inline-block;
            padding: 10px 14px;
            border-radius: 10px;
            text-decoration: none;
            background: #991b1b;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Réservation confirmée</h1>
        <p>Merci {{ $booking->customer_name }}, votre rendez-vous est bien enregistré.</p>

        <div class="row"><div class="label">Business</div><div class="value">{{ $business->name }}</div></div>
        <div class="row"><div class="label">Service</div><div class="value">{{ $booking->service?->name ?? '-' }}</div></div>
        <div class="row"><div class="label">Prestataire</div><div class="value">{{ $booking->staff?->name ?? '-' }}</div></div>
        <div class="row"><div class="label">Date</div><div class="value">{{ $booking->date }}</div></div>
        <div class="row"><div class="label">Heure</div><div class="value">{{ $booking->start_time }} - {{ $booking->end_time }}</div></div>
        <div class="row"><div class="label">Statut</div><div class="value">{{ $booking->status }}</div></div>

        @if($booking->status !== 'canceled')
            <a class="btn" href="{{ $cancelUrl }}">Annuler cette réservation</a>
        @endif
    </div>
</div>
</body>
</html>
