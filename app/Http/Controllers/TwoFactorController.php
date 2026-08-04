<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PragmaRX\Google2FALaravel\Google2FA;

/**
 * Contrôleur d'activation/désactivation de l'authentification à deux facteurs (2FA).
 */
class TwoFactorController extends Controller
{
    public function __construct(
        private readonly Google2FA $google2fa
    ) {}

    /**
     * Affiche le QR code et les codes de secours pour activer le 2FA.
     * Si le 2FA est déjà activé, redirige vers la page de sécurité.
     *
     * @return \Illuminate\View\View
     */
    public function show(): View
    {
        $user = auth()->user();

        if ($user->two_factor_confirmed_at) {
            return view('app', ['page' => 'gel-security', 'props' => ['twoFactorEnabled' => true]]);
        }

        // Générer le secret et le QR code
        $secret = $this->google2fa->generateSecretKey(32);
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            'GEL Cabinet',
            $user->email,
            $secret
        );

        // Générer les codes de secours
        $recoveryCodes = collect(range(1, 8))->map(fn() => strtoupper(
            implode('-', [
                substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4),
                substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4),
                substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4),
            ])
        ))->toArray();

        // Stocker temporairement le secret en session
        session(['2fa_pending_secret' => $secret]);
        session(['2fa_pending_recovery_codes' => $recoveryCodes]);

        return view('2fa.setup', compact('qrCodeUrl', 'secret', 'recoveryCodes'));
    }

    /**
     * Confirme l'activation du 2FA en validant un code TOTP.
     * Sauvegarde le secret, les codes de secours hachés et la date de confirmation.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function confirm(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $secret = session('2fa_pending_secret');
        if (!$secret) {
            return back()->withErrors(['Le secret 2FA est introuvable. Veuillez recommencer.']);
        }

        // Vérifier le code TOTP
        $valid = $this->google2fa->verifyGoogle2FA($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['Le code TOTP est invalide.']);
        }

        /** @var User $user */
        $user = auth()->user();
        $recoveryCodes = session('2fa_pending_recovery_codes', []);

        // Hacher les codes de secours
        $hashedCodes = array_map(fn($code) => bcrypt($code), $recoveryCodes);

        $user->update([
            'two_factor_secret'          => encrypt($secret),
            'two_factor_recovery_codes'   => json_encode($hashedCodes),
            'two_factor_confirmed_at'    => now(),
        ]);

        session()->forget(['2fa_pending_secret', '2fa_pending_recovery_codes']);

        return redirect()->route('securite.index')
            ->with('success', 'Authentification à deux facteurs activée avec succès.')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Désactive le 2FA pour l'utilisateur connecté.
     * Supprime le secret, les codes de secours et la date de confirmation.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disable(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $user->update([
            'two_factor_secret'          => null,
            'two_factor_recovery_codes'  => null,
            'two_factor_confirmed_at'    => null,
        ]);

        return redirect()->route('securite.index')
            ->with('success', 'Authentification à deux facteurs désactivée.');
    }
}
