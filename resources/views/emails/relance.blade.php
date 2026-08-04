<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 20px; }
        .wrapper { max-width: 600px; margin: 0 auto; }
        .card { background: #fff; border-radius: 10px; padding: 40px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }
        .header { border-bottom: 3px solid #3B82F6; padding-bottom: 20px; margin-bottom: 28px; }
        .logo { font-size: 22px; font-weight: 800; color: #3B82F6; letter-spacing: -0.5px; }
        h2 { margin: 0 0 4px; font-size: 18px; color: #1e293b; }
        p { color: #475569; line-height: 1.7; font-size: 15px; }
        .highlight { background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 14px 18px; border-radius: 6px; margin: 20px 0; }
        .highlight strong { color: #1e293b; }
        .footer { margin-top: 32px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 13px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="logo">GEL Cabinet</div>
        </div>

        <p>Bonjour <strong>{{ $clientName }}</strong>,</p>

        @if($type === 'invoice')
            <p>
                Sauf erreur de notre part, nous constatons que la facture ci-dessous reste impayée à ce jour. 
                Nous vous remercions de bien vouloir régulariser cette situation dans les meilleurs délais.
            </p>
            <div class="highlight">
                <p style="margin:0;"><strong>Facture :</strong> {{ $reference }}</p>
                <p style="margin:4px 0 0;"><strong>Montant restant dû :</strong> {{ $amount }} €</p>
                <p style="margin:4px 0 0;"><strong>Échéance :</strong> {{ $dueDate }}</p>
            </div>
            <p>
                Si votre règlement a déjà été effectué, nous vous prions de ne pas tenir compte de ce message. 
                N'hésitez pas à nous contacter pour toute question.
            </p>
        @else
            <p>
                Nous nous permettons de vous relancer concernant l'action suivante, 
                qui nécessite votre attention pour la bonne poursuite de votre dossier.
            </p>
            <div class="highlight">
                <p style="margin:0;"><strong>Action requise :</strong> {{ $reference }}</p>
                <p style="margin:4px 0 0;"><strong>Échéance :</strong> {{ $dueDate }}</p>
            </div>
            <p>
                Merci de nous transmettre les éléments nécessaires ou de prendre contact avec nous 
                afin de débloquer le traitement de votre dossier.
            </p>
        @endif

        <p>Cordialement,<br><strong>Le Cabinet</strong></p>

        <div class="footer">
            Cet e-mail a été généré automatiquement depuis la plateforme GEL. Merci de ne pas répondre directement.
        </div>
    </div>
</div>
</body>
</html>
