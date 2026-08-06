<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmation de réception</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: #163A5E;">Bonjour {{ $clientRequest->name }},</h2>
    </div>

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p>Nous avons bien reçu votre demande de contact intitulée :</p>
        <p style="font-weight: bold; font-size: 16px;">"{{ $clientRequest->title }}"</p>
        
        <p>Notre équipe la traitera dans les plus brefs délais. Vous recevrez une réponse prochainement.</p>
    </div>

    <div style="margin-bottom: 30px;">
        <h3 style="color: #FF7900; font-size: 16px;">Récapitulatif de votre message :</h3>
        <p style="background-color: #fff; border: 1px solid #e9ecef; padding: 15px; border-left: 4px solid #FF7900; font-style: italic;">
            {{ $clientRequest->description }}
        </p>
    </div>

    <div style="border-top: 1px solid #e9ecef; padding-top: 20px; font-size: 12px; color: #6c757d; text-align: center;">
        <p>Ceci est un message automatique, merci de ne pas y répondre directement.</p>
        <p>&copy; {{ date('Y') }} GEL Cabinet. Tous droits réservés.</p>
    </div>

</body>
</html>
