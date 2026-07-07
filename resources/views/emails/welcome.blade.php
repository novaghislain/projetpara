<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur ComptaSaaS</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: #4f46e5; color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 30px; }
        .body p { line-height: 1.6; color: #374151; }
        .btn { display: inline-block; background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; }
        .steps { margin: 20px 0; padding: 0; list-style: none; }
        .steps li { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .steps li:before { content: "✅ "; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue sur ComptaSaaS</h1>
            <p>Votre comptabilité SYSCOHADA en ligne</p>
        </div>
        <div class="body">
            <p>Bonjour <strong>{{ $userName }}</strong>,</p>
            <p>Votre compte pour l'entreprise <strong>{{ $companyName }}</strong> a été créé avec succès.</p>
            <p>Vous avez maintenant accès à votre espace comptable complet :</p>

            <ul class="steps">
                <li>Plan comptable SYSCOHADA installé (105 comptes)</li>
                <li>Journaux comptables prêts à l'emploi</li>
                <li>Gestion des écritures et factures</li>
                <li>Suivi bancaire et rapprochement</li>
                <li>États financiers : Bilan, P&L, Balance</li>
            </ul>

            <p style="text-align: center;">
                <a href="{{ $loginUrl }}" class="btn">Se connecter à ComptaSaaS</a>
            </p>

            <p><strong>Premiers pas :</strong></p>
            <ol>
                <li>Connectez-vous avec votre email et mot de passe</li>
                <li>Créez votre première écriture (apport en capital)</li>
                <li>Ajoutez votre compte bancaire</li>
                <li>Consultez le bilan en temps réel</li>
            </ol>

            <p>Besoin d'aide ? Contactez-nous : <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></p>
        </div>
        <div class="footer">
            <p>ComptaSaaS - Solution comptable SYSCOHADA</p>
            <p>{{ config('app.url') }}</p>
        </div>
    </div>
</body>
</html>
