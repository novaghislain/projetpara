{{-- ============================================ --}}
{{-- Vue : Email d'invation comptable              --}}
{{-- Contrôleur : App\Http\Controllers\Gel\InvitationController --}}
{{-- Route : gel.invitation.show                  --}}
{{-- Variables attendues :                        --}}
{{--   $invitation -> objet Invitation (nom, email,--}}
{{--                  token, message, expire_at,   --}}
{{--                  client)                      --}}
{{-- ============================================ --}}

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation comptable — {{ $invitation->client?->nom_entreprise ?? 'ComptaSaaS' }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        {{-- En-tête de l'invitation --}}
        <div class="bg-primary px-8 py-6 text-white text-center">
            <div class="text-4xl mb-2">ðŸ“§</div>
            <h1 class="text-2xl font-bold">Invitation À  rejoindre</h1>
            <p class="text-blue-200 mt-1">{{ $invitation->client?->nom_entreprise ?? 'une entreprise' }}</p>
        </div>

        {{-- Corps du message et statut de l'invitation --}}
        <div class="px-8 py-6 text-center">
            <p class="text-gray-600 mb-2">
                Bonjour <strong>{{ $invitation->nom ?? $invitation->email }}</strong>,
            </p>
            <p class="text-gray-600 mb-6">
                Vous avez été invité À  gérer la comptabilité de
                <strong>{{ $invitation->client?->nom_entreprise ?? 'cette entreprise' }}</strong>
                sur <strong>ComptaSaaS</strong>.
            </p>

            {{-- Message personnel optionnel --}}
            @if ($invitation->message)
                <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left text-sm text-gray-600 italic">
                    "{{ $invitation->message }}"
                </div>
            @endif

            {{-- Statut de l'invitation : expirée, acceptée ou active --}}
            @if ($invitation->estExpiree())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    Cette invitation a expiré. Veuillez demander une nouvelle invitation.
                </div>
            @elseif ($invitation->estAcceptee())
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                    âœ… Cette invitation a déjÀ  été acceptée.
                </div>
            @else
                <a href="{{ route('gel.invitation.accept', ['token' => $invitation->token]) }}"
                   class="inline-block bg-primary text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-semibold transition-all duration-200">
                    âœ… Accepter l'invitation
                </a>
                <p class="text-xs text-gray-400 mt-3">
                    Cette invitation expire le {{ $invitation->expire_at->format('d/m/Y À  H:i') }}.
                </p>
            @endif
        </div>
    </div>
</body>
</html>

