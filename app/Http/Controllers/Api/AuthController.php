<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\TwoFactorRequest;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FALaravel\Google2FA;

/**
 * Contrôleur API d'authentification.
 *
 * Gère l'inscription, la connexion, la vérification 2FA,
 * le rafraîchissement de token et la déconnexion via API.
 */
class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur avec création de token et refresh token.
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone ?? '',
            'is_active' => true,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $refreshToken = RefreshToken::createForUser($user, [
            'device_info' => $request->device_info ?? $request->userAgent() ?? 'unknown',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Inscription réussie.',
            'user' => $this->formatUser($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'refresh_token' => $refreshToken->raw_token,
            'expires_in' => 60 * 24, // 24 heures en minutes
        ], 201);
    }

    /**
     * Connexion utilisateur avec vérification 2FA si activée.
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        // Vérifier les credentials manuellement
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.',
            ], 401);
        }

        // Créer la session utilisateur (pour l'accès aux routes web après connexion)
        Auth::guard('web')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (!$user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            return response()->json([
                'message' => 'Ce compte est désactivé.',
            ], 403);
        }

        if ($user->is_suspended) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            return response()->json([
                'message' => 'Ce compte est suspendu.',
            ], 403);
        }

        $user->recordLogin($request->ip());

        // Vérifier si 2FA est activé
        if ($user->two_factor_confirmed_at) {
            session(['two_factor:auth:pending' => true]);
            return response()->json([
                'message' => 'Code 2FA requis.',
                'requires_two_factor' => true,
                'user_id' => $user->id,
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        $refreshToken = RefreshToken::createForUser($user, [
            'device_info' => $request->device_info ?? $request->userAgent() ?? 'unknown',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Connexion réussie.',
            'user' => $this->formatUser($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'refresh_token' => $refreshToken->raw_token,
            'expires_in' => 60 * 24,
        ]);
    }

    /**
     * Vérification du code 2FA après connexion (second facteur).
     *
     * @param  TwoFactorRequest  $request
     * @return JsonResponse
     */
    public function verifyTwoFactor(TwoFactorRequest $request): JsonResponse
    {
        $user = User::find($request->input('user_id'));

        if (!$user || !$user->two_factor_confirmed_at) {
            return response()->json(['message' => '2FA non configuré.'], 400);
        }

        $google2fa = app(Google2FA::class);
        $valid = $google2fa->verifyGoogle2FA($user->two_factor_secret, $request->code);

        if (!$valid) {
            return response()->json(['message' => 'Code 2FA invalide.'], 401);
        }

        session()->forget('two_factor:auth:pending');

        $token = $user->createToken('auth-token')->plainTextToken;

        $refreshToken = RefreshToken::createForUser($user, [
            'device_info' => $request->userAgent() ?? 'unknown',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Authentification 2FA réussie.',
            'user' => $this->formatUser($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'refresh_token' => $refreshToken->raw_token,
            'expires_in' => 60 * 24,
        ]);
    }

    /**
     * Activer 2FA (génère le secret et le QR code pour l'application d'authentification).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        $user = Auth::user();
        $google2fa = app(Google2FA::class);

        $secret = $google2fa->generateSecretKey();
        $user->two_factor_secret = $secret;
        $user->save();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'message' => 'Scannez le QR code avec votre application d\'authentification (Google Authenticator, Authy...).',
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    /**
     * Confirmer l'activation 2FA après vérification du code par l'utilisateur.
     *
     * @param  TwoFactorRequest  $request
     * @return JsonResponse
     */
    public function confirmTwoFactor(TwoFactorRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->two_factor_secret) {
            return response()->json(['message' => 'Veuillez d\'abord générer le secret 2FA.'], 400);
        }

        $google2fa = app(Google2FA::class);
        $valid = $google2fa->verifyGoogle2FA($user->two_factor_secret, $request->code);

        if (!$valid) {
            return response()->json(['message' => 'Code invalide. Veuillez réessayer.'], 401);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        return response()->json([
            'message' => 'Authentification à deux facteurs activée avec succès.',
            'two_factor_enabled' => true,
        ]);
    }

    /**
     * Désactiver 2FA pour l'utilisateur connecté.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        $user = Auth::user();

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return response()->json([
            'message' => 'Authentification à deux facteurs désactivée.',
        ]);
    }

    /**
     * Rafraîchir le token d'accès via un refresh token valide.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $hashedToken = hash('sha256', $request->refresh_token);
        $refreshToken = RefreshToken::valid()->where('token', $hashedToken)->first();

        if (!$refreshToken) {
            return response()->json(['message' => 'Refresh token invalide ou expiré.'], 401);
        }

        $refreshToken->revoke();

        $user = $refreshToken->user;
        $user->tokens()->where('name', 'auth-token')->delete();

        $newToken = $user->createToken('auth-token')->plainTextToken;

        $newRefreshToken = RefreshToken::createForUser($user, [
            'device_info' => $refreshToken->device_info,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'refresh_token' => $newRefreshToken->token,
            'expires_in' => 60 * 24,
        ]);
    }

    /**
     * Déconnexion : révoque le token d'accès et les refresh tokens valides.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        RefreshToken::byUser(Auth::id())->valid()->get()->each->revoke();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Profil de l'utilisateur connecté avec relations.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roleModel', 'activeClient']);

        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Formate les données de l'utilisateur pour les réponses API.
     *
     * @param  User  $user  L'utilisateur à formater
     * @return array
     */
    protected function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'role_name' => $user->roleModel?->name,
            'is_active' => $user->is_active,
            'is_company_admin' => $user->is_company_admin,
            'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
            'has_active_client' => $user->active_client_id !== null,
            'active_client_id' => $user->active_client_id,
            'onboarding_completed' => $user->hasCompletedOnboarding(),
            'onboarding_token' => $user->onboarding_token,
            'account_type' => $user->account_type,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
