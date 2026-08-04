<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation Confirmée</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3B82F6;
            --bg: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --border: #E2E8F0;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0; padding: 0;
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
        }
        .container {
            width: 100%; max-width: 450px; padding: 20px;
        }
        .card {
            background: var(--card-bg); border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            padding: 40px; border: 1px solid var(--border); text-align: center;
        }
        .icon-header {
            width: 80px; height: 80px; background: #EFF6FF; color: var(--primary);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 36px; margin: 0 auto 24px;
        }
        h1 { margin: 0 0 12px; font-size: 22px; font-weight: 700; color: var(--text-main); }
        p { margin: 0; color: var(--text-muted); font-size: 15px; line-height: 1.5; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="icon-header">
            <i class="fas fa-calendar-check"></i>
        </div>
        <h1>Rendez-vous confirmé !</h1>
        <p>Votre rendez-vous a bien été enregistré dans l'agenda de votre cabinet comptable. Vous recevrez un rappel prochainement.</p>
    </div>
</div>

</body>
</html>
