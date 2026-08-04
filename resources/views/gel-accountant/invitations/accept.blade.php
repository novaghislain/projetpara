{{-- ============================================ --}}
{{-- VUE : Formulaire d'acceptation d'invitation  --}}
{{-- Appelée par : GelAccountant\InvitationController@showAcceptForm --}}
{{-- Variables attendues : $invitation (token, email, client) --}}
{{-- ============================================ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accepter l'invitation — ComptaSaaS</title>
    @vite('resources/css/app.css')
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-blue-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-primary to-indigo-700 px-8 py-6 text-white text-center">
            <div class="text-4xl mb-2">ðŸ“‹</div>
            <h1 class="text-xl font-bold">Invitation À  rejoindre</h1>
            <p class="text-indigo-200 mt-1">{{ $invitation->client?->nom_entreprise ?? 'une entreprise' }}</p>
        </div>

        <div class="px-8 py-6">
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg text-sm mb-6">
                @if(($invitation->role_invite ?? '') === 'secretaire')
                    Vous avez été invité à gérer le <strong>secrétariat</strong> de
                    <strong>{{ $invitation->client?->nom_entreprise ?? 'cette entreprise' }}</strong>.
                    Pour accepter, créez votre compte.
                @else
                    Vous avez été invité à gérer la <strong>comptabilité</strong> de
                    <strong>{{ $invitation->client?->nom_entreprise ?? 'cette entreprise' }}</strong>.
                    Pour accepter, créez votre compte.
                @endif
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('gel.invitation.accept', ['token' => $invitation->token]) }}">
                @csrf

                <div class="space-y-4">
                    {{-- Email (lecture seule) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" value="{{ $invitation->email }}" readonly disabled
                               class="w-full bg-gray-50 border-gray-200 rounded-lg text-gray-500 cursor-not-allowed">
                    </div>

                    {{-- Prénom --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                            <input type="text" name="prenom" value="{{ old('prenom') }}"
                                   class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                   placeholder="Jean">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                   placeholder="Dupont">
                        </div>
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe *</label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                               placeholder="Min. 8 caractères">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe *</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                               placeholder="Confirmer">
                    </div>

                    <button type="submit"
                            class="w-full bg-primary text-white py-3 rounded-lg hover:bg-blue-700 font-semibold transition-all duration-200">
                        âœ… Accepter et créer mon compte
                    </button>
                </div>
            </form>

            <p class="text-center text-xs text-gray-400 mt-4">
                En acceptant, vous rejoignez l'équipe de cette entreprise sur ComptaSaaS.
                Votre compte sera actif immédiatement.
            </p>
        </div>
    </div>
</body>
</html>

