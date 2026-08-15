<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditTrail;
use App\Models\UserClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use PragmaRX\Google2FALaravel\Google2FA;

/**
 * Contrôleur de gestion des sessions authentifiées.
 *
 * Gère la connexion, la déconnexion, la vérification en 8 étapes
 * (credentials, suspension, email, mot de passe forcé, 2FA,
 *  métadonnées, audit, redirection) et le challenge 2FA.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function create(): View
    {
        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * 8-étapes de vérification de connexion :
     * 1. Credentials
     * 2. Suspension
     * 3. Email vérifié
     * 4. must_change_password
     * 5. 2FA
     * 6. Metadata (last_login_at, ip, count)
     * 7. Audit trail
     * 8. Redirection
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // ─── 1. Authentification ────────────────────────────────────────
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // ─── 2. Vérification statut ─────────────────────────────────
        if ($user->statut !== 'actif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte a été suspendu ou est inactif. Veuillez contacter l\'administrateur.',
            ]);
        }

        // Redirection vers le tableau de bord unique
        return redirect()->route('dashboard');
    }

    /**
     * Redirige un utilisateur business (company_admin, manager, employee)
     * vers le portail entreprise avec le contexte client_id correct.
     *
     * @param  mixed  $user  L'utilisateur authentifié
     * @return RedirectResponse
     */
    private function redirectBusinessUser($user): RedirectResponse
    {
        $clientId = $user->active_client_id
                 ?? $user->client_id
                 ?? null;

        if (!$clientId) {
            $clientId = \DB::table('user_clients')
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->value('client_id');

            if ($clientId) {
                $user->update(['active_client_id' => $clientId]);
            }
        }

        if (!$clientId) {
            auth()->logout();
            return redirect('/login')->withErrors([
                'email' => 'Votre compte n\'est associé à aucune entreprise.',
            ]);
        }

        session(['active_client_id' => $clientId]);

        return redirect('/gel-business/dashboard');
    }

    /**
     * Afficher le formulaire de vérification 2FA.
     */
    public function challengeForm(): View
    {
        if (!session('2fa_pending')) {
            return view('auth.login');
        }
        return view('auth.2fa-challenge');
    }

    /**
     * Vérifier le code TOTP 2FA.
     */
    public function verifyChallenge(Request $request, Google2FA $google2fa): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:10',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['Session expirée. Veuillez vous reconnecter.']);
        }

        $user = \App\Models\User::find($userId);
        if (!$user || !$user->two_factor_confirmed_at) {
            return redirect()->route('login')->withErrors(['Configuration 2FA introuvable.']);
        }

        $secret = decrypt($user->two_factor_secret);

        // Vérifier code TOTP
        if ($google2fa->verifyGoogle2FA($secret, $request->code)) {
            Auth::login($user);
            session()->forget(['2fa_pending', '2fa_user_id']);
            $request->session()->regenerate();

            // Metadata
            $user->recordLogin($request->ip());

            // Audit trail
            AuditTrail::create([
                'user_id'       => $user->id,
                'client_id'     => $user->active_client_id ?? $user->client_id,
                'event'         => 'login_2fa',
                'auditable_type' => get_class($user),
                'auditable_id'   => $user->id,
                'description'    => 'Connexion via 2FA',
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);

            // Vérifier must_change_password après 2FA
            if ($user->must_change_password) {
                session(['must_change_password' => true]);
            }

            return $this->redirectUser($user, $request);
        }

        // Vérifier si c'est un code de secours
        $recoveryCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);
        foreach ($recoveryCodes as $index => $hashedCode) {
            if (password_verify($request->code, $hashedCode)) {
                unset($recoveryCodes[$index]);
                $user->update(['two_factor_recovery_codes' => json_encode(array_values($recoveryCodes))]);

                Auth::login($user);
                session()->forget(['2fa_pending', '2fa_user_id']);
                $request->session()->regenerate();

                $user->recordLogin($request->ip());

                return $this->redirectUser($user, $request)
                    ->with('info', 'Code de secours utilisé. Il ne reste que ' . count($recoveryCodes) . ' codes.');
            }
        }

        return back()->withErrors(['Code invalide. Veuillez réessayer.']);
    }

    /**
     * Rediriger l'utilisateur selon son rôle (utilisé aussi après 2FA).
     *
     * @param  mixed  $user    L'utilisateur authentifié
     * @param  Request  $request  La requête HTTP entrante
     * @return RedirectResponse
     */
    private function redirectUser($user, $request): RedirectResponse
    {
        // Onboarding incomplet ?
        if (!$user->hasCompletedOnboarding() && $user->onboarding_token && $user->account_type) {
            return redirect()->route('onboarding.profil', ['token' => $user->onboarding_token]);
        }

        // Commande en cours ? Priorité absolue
        if (session('order_service_id')) {
            return redirect()->route('commande.step', [
                'service' => session('order_service_id'),
            ]);
        }

        // Vérification RBAC : Si l'utilisateur est un indépendant (autonome), il a une affectation principale
        if ($user->is_autonomous) {
            $affectation = \App\Models\Affectation::where('utilisateur_id', $user->id)
                            ->where('statut', 'active')
                            ->with('role')
                            ->first();

            if ($affectation && $affectation->role) {
                // Définir l'entreprise active pour son espace
                session(['active_entreprise_id' => $affectation->entreprise_id]);

                $roleCode = $affectation->role->code;
                return match ($roleCode) {
                    'accountant' => redirect('/gel-accountant/dashboard'),
                    'secretary'  => redirect('/gel-secretary/dashboard'),
                    'rh'         => redirect('/gel-rh/dashboard'),
                    'legal'      => redirect('/gel-legal/dashboard'),
                    default      => redirect('/dashboard'),
                };
            }
        }

        return match (true) {
            $user->role === 'super_admin'
                => redirect('/dashboard'),

            $user->role === 'comptable'
                => redirect('/gel-accountant/dashboard'),

            in_array($user->role, ['secretaire', 'secretary']) || $user->role_secretaire
                => redirect('/gel-secretary/dashboard'),

            $user->role === 'client'
                => redirect('/mes-commandes'),

            $user->role === 'company_admin'
                => redirect()->route('company.dashboard'),

            in_array($user->role, ['company_manager', 'company_employee'])
                => $this->redirectBusinessUser($user),

            default => redirect('/login'),
        };
    }

    /**
     * Déconnecte l'utilisateur, invalide la session et enregistre l'audit.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditTrail::create([
                'user_id'       => $user->id,
                'client_id'     => $user->active_client_id ?? $user->client_id,
                'event'         => 'logout',
                'auditable_type' => get_class($user),
                'auditable_id'   => $user->id,
                'description'    => 'Déconnexion utilisateur',
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
