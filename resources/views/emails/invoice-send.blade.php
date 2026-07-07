<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .email-header {
            background: #000;
            color: #FF7900;
            padding: 24px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 22px;
            color: #FF7900;
        }
        .email-body {
            padding: 24px;
            color: #333;
            line-height: 1.6;
        }
        .email-body p {
            margin: 0 0 12px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #FF7900;
            color: #000 !important;
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
            margin: 16px 0;
        }
        .email-footer {
            padding: 16px 24px;
            background: #f9fafb;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>GEL Cabinet</h1>
        </div>
        <div class="email-body">
            <p>Bonjour <strong>{{ $recipientName }}</strong>,</p>

            <p>Veuillez trouver ci-joint votre facture.</p>

            <p>Pour toute question, n'hésitez pas à nous contacter.</p>

            <p>Cordialement,<br><strong>L'équipe GEL Cabinet</strong></p>
        </div>
        <div class="email-footer">
            GEL Cabinet — www.gelcabinet.com<br>
            Ce message est automatique, merci de ne pas y répondre.
        </div>
    </div>
</body>
</html>
