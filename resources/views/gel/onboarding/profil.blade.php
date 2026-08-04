{{-- ============================================ --}}
{{-- Vue : Complétion de profil après invitation   --}}
{{-- Contrôleur : App\Http\Controllers\Gel\Onboarding\ProfilController --}}
{{-- Route : onboarding.profil                     --}}
{{-- Variables attendues :                        --}}
{{--   $account_type -> string ('entreprise'|'cabinet') --}}
{{--   $token       -> string (jeton d'invitation)  --}}
{{--   $user        -> objet User                    --}}
{{-- ============================================ --}}

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compléter mon profil — ComptaSaaS</title>
    @vite('resources/css/app.css')
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-primary to-blue-700 px-8 py-6 text-white text-center">
            <div class="text-4xl mb-2">
                @if ($account_type === 'entreprise') ðŸ¢ @else ðŸ“Š @endif
            </div>
            <h1 class="text-2xl font-bold">
                @if ($account_type === 'entreprise')
                    Complétez votre profil entreprise
                @else
                    Complétez votre profil cabinet
                @endif
            </h1>
            <p class="text-blue-200 mt-1">
                @if ($account_type === 'entreprise')
                    Dites-nous en plus sur votre entreprise pour commencer
                @else
                    Dites-nous en plus sur votre cabinet comptable
                @endif
            </p>
        </div>

        <form method="POST" action="{{ route('onboarding.profil.submit', ['token' => $token]) }}"
              class="px-8 py-6 space-y-5">
            @csrf

            {{-- Messages d'erreur de validation --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Champ : Nom / Raison sociale --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    @if ($account_type === 'entreprise')
                        Raison sociale / Nom de l'entreprise *
                    @else
                        Nom du cabinet *
                    @endif
                </label>
                <input type="text" name="nom" value="{{ old('nom') }}" required
                       class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                       placeholder="@if($account_type === 'entreprise')Ma SARL @elseMon Cabinet Comptable @endif">
            </div>

            {{-- Contact : téléphone + email --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone') }}"
                           class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                           placeholder="+229 01 23 45 67">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email de contact
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                           placeholder="contact@exemple.com">
                </div>
            </div>

            {{-- Adresse --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                <input type="text" name="adresse" value="{{ old('adresse') }}"
                       class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                       placeholder="Adresse physique">
            </div>

            {{-- Ville --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                    <input type="text" name="ville" value="{{ old('ville') }}"
                           class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                           placeholder="Cotonou">
                </div>

                @if ($account_type === 'entreprise')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Secteur d'Activité</label>
                        <input type="text" name="secteur" value="{{ old('secteur') }}"
                               class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                               placeholder="Commerce, Service, ...">
                    </div>
                @endif
            </div>

            {{-- Documents fiscaux : IFU et Registre de Commerce --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IFU</label>
                    <input type="text" name="ifu" value="{{ old('ifu') }}"
                           class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                           placeholder="NÂ° IFU">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RC</label>
                    <input type="text" name="rc" value="{{ old('rc') }}"
                           class="w-full border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                           placeholder="NÂ° Registre de Commerce">
                </div>
            </div>

            @if ($account_type === 'entreprise')
                {{-- Choix des espaces : Comptabilité / Secrétariat --}}
                <div class="pt-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vos espaces de travail</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 border border-gray-300 rounded-lg px-4 py-3 cursor-pointer hover:border-primary">
                            <input type="checkbox" name="wants_accounting" value="1"
                                   @checked(old('wants_accounting', $wants_accounting ?? false))
                                   class="w-4 h-4 accent-blue-600">
                            <span class="text-sm text-gray-700">Comptabilité</span>
                        </label>
                        <label class="flex items-center gap-2 border border-gray-300 rounded-lg px-4 py-3 cursor-pointer hover:border-primary">
                            <input type="checkbox" name="wants_secretary" value="1"
                                   @checked(old('wants_secretary', $wants_secretary ?? false))
                                   class="w-4 h-4 accent-blue-600">
                            <span class="text-sm text-gray-700">Secrétariat</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        Vous pourrez ensuite inviter un comptable / une secrétaire selon votre choix.
                    </p>
                </div>
            @endif

            {{-- Bouton --}}
            <div class="pt-2">
                <button type="submit"
                        class="w-full bg-primary text-white py-3 rounded-lg hover:bg-blue-700 font-semibold text-lg transition-all duration-200 transform active:scale-[0.98]">
                    @if ($account_type === 'entreprise')
                        âœ… Créer mon entreprise
                    @else
                        âœ… Créer mon cabinet
                    @endif
                </button>
            </div>

            <p class="text-center text-xs text-gray-400">
                Les informations renseignées seront utilisées pour votre dossier comptable.
            </p>
        </form>
    </div>
</body>
</html>

