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

class AuthenticatedSessionController extends Controller
{
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

        // ─── 2. Vérification suspension ─────────────────────────────────
        if ($user->isSuspended()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte a été suspendu. Veuillez contacter l\'administrateur.',
            ]);
        }

        // ─── 3. Vérification email (sauf super admin) ───────────────────
        // if (!$user->isSuperAdmin() && !$user->email_verified_at) {
        //     Auth::logout();
        //     $request->session()->invalidate();
        //     $request->session()->regenerateToken();
        //
        //     return redirect()->route('login')->withErrors([
        //         'email' => 'Veuillez vérifier votre adresse email avant de vous connecter.',
        //     ]);
        // }

        // ─── 4. must_change_password ────────────────────────────────────
        if ($user->must_change_password) {
            // Ne pas déconnecter, stocker en session pour forcer le changement
            session(['must_change_password' => true]);
        }

        // ─── 5. 2FA ────────────────────────────────────────────────────
        if ($user->two_factor_confirmed_at) {
            session(['2fa_pending' => true, '2fa_user_id' => $user->id]);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('2fa.challenge');
        }

        // ─── 6. Metadata de connexion ──────────────────────────────────
        $user->recordLogin($request->ip());

        // ─── 7. Audit trail ────────────────────────────────────────────
        AuditTrail::create([
            'user_id'       => $user->id,
            'client_id'     => $user->active_client_id ?? $user->client_id,
            'event'         => 'login',
            'auditable_type' => get_class($user),
            'auditable_id'   => $user->id,
            'description'    => 'Connexion utilisateur',
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        // ─── 8. Redirection ───────────────────────────────────────────
        return $this->redirectUser($user, $request);
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
     * Rediriger l'utilisateur selon son profil.
     * Gère : commande en attente, multi-entreprise, super_admin, comptable,
     * company_admin, client, secretaire.
     */
    private function redirectUser($user, $request): RedirectResponse
    {
        // Si une commande est en attente (client venant du catalogue)
        if (session('order_service_id')) {
            return redirect()->route('commande.step');
        }

        // Super Admin → dashboard GEL
        if ($user->isSuperAdmin()) {
            return redirect()->intended(route('dashboard'));
        }

        // Comptable → CPA dashboard
        if ($user->isComptable()) {
            return redirect()->intended(route('cpa.dashboard'));
        }

        // Vérifier si l'utilisateur a plusieurs entreprises → sélecteur de contexte
        $userClientCount = UserClient::where('user_id', $user->id)
            ->where('is_active', true)
            ->count();

        if ($userClientCount > 1 && !$user->active_client_id) {
            return redirect()->route('select.context');
        }

        // Company admin / manager / employee → company dashboard
        if ($user->isCompanyAdmin() || $user->isCompanyManager() || $user->roleModel?->slug === 'company_employee') {
            return redirect()->intended(route('company.dashboard'));
        }

        // Clients purs (role=client)
        if ($user->isClient() || $user->role === 'client') {
            return redirect()->intended(route('client.orders.index'));
        }

        // Secrétaire DAE
        if ($user->role_secretaire) {
            return redirect()->intended(route('dae.dashboard'));
        }

        // Fallback → GEL dashboard
        return redirect()->intended(route('dashboard'));
    }

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
