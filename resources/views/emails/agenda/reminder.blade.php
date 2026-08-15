<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; color: #334155; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden;">
        <div style="background-color: #0F172A; padding: 20px; text-align: center;">
            <h2 style="color: #fff; margin: 0;">Rappel de Rendez-vous</h2>
        </div>
        <div style="padding: 30px;">
            <p>Bonjour {{ $event->guest_name ?? 'Monsieur/Madame' }},</p>
            <p>Ceci est un rappel pour votre rendez-vous de demain avec le cabinet <strong>{{ $cabinetName }}</strong>.</p>
            
            <div style="background-color: #F8FAFC; border-left: 4px solid #3B82F6; padding: 15px; margin: 20px 0;">
                <p style="margin: 0 0 10px 0;"><strong>Objet :</strong> {{ $event->title }}</p>
                <p style="margin: 0 0 10px 0;"><strong>Date et heure :</strong> {{ \Carbon\Carbon::parse($event->start_at)->translatedFormat('l d F Y à H:i') }}</p>
                
                @if($event->location)
                    <p style="margin: 0 0 10px 0;"><strong>Lieu :</strong> {{ $event->location }}</p>
                @endif
                
                @if($event->visio_link)
                    <p style="margin: 0 0 10px 0;"><strong>Visioconférence :</strong> <a href="{{ $event->visio_link }}" style="color: #3B82F6;">Rejoindre la réunion ({{ ucfirst($event->visio_type) }})</a></p>
                @endif
            </div>
            
            @if($event->description)
                <p><strong>Notes :</strong> {{ $event->description }}</p>
            @endif
            
            <p style="margin-top: 30px;">Si vous avez un empêchement, merci de nous prévenir au plus vite.</p>
            <p>Cordialement,<br>L'équipe du cabinet {{ $cabinetName }}</p>
        </div>
        <div style="background-color: #F1F5F9; padding: 15px; text-align: center; font-size: 12px; color: #64748B;">
            Cet email est généré automatiquement, merci de ne pas y répondre directement.
        </div>
    </div>
</body>
</html>
