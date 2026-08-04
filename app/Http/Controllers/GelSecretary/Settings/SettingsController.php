<?php

namespace App\Http\Controllers\GelSecretary\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;
use App\Models\ClientEmailConfig;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class SettingsController extends Controller
{
    /**
     * Affiche la page des paramètres (Sécurité & Webmail)
     */
    public function index()
    {
        $user = Auth::user();
        
        $clients = Client::orderBy('company_name')->get();
        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        $activeClient = $activeClientId ? Client::find($activeClientId) : $clients->first();
        
        $emailConfig = null;
        if ($activeClient) {
            $emailConfig = ClientEmailConfig::where('client_id', $activeClient->id)->first();
        }

        // Vérifier le statut 2FA
        $twoFactorEnabled = !empty($user->two_factor_secret);
        
        $qrCodeSvg = null;
        $secretKey = null;

        if (!$twoFactorEnabled) {
            $google2fa = app('pragmarx.google2fa');
            
            // Generate secret if not in session
            $secretKey = session('2fa_secret');
            if (!$secretKey) {
                $secretKey = $google2fa->generateSecretKey();
                session(['2fa_secret' => $secretKey]);
            }
            
            $qrCodeSvg = $google2fa->getQRCodeInline(
                'GEL_Cabinet',
                $user->email,
                $secretKey
            );
        }

        return view('gel-secretary.settings.index', compact(
            'clients', 'activeClient', 'emailConfig', 'twoFactorEnabled', 'qrCodeSvg', 'secretKey'
        ));
    }

    /**
     * Mise à jour du mot de passe
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->save();

        return back()->with('success', 'Votre mot de passe a été modifié avec succès.');
    }

    /**
     * Confirmer et activer la double authentification (2FA)
     */
    public function confirm2FA(Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        $user = Auth::user();
        $secretKey = session('2fa_secret');

        if (!$secretKey) {
            return back()->with('error', 'Session expirée. Veuillez recharger la page.');
        }

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($secretKey, $request->otp);

        if ($valid) {
            $user->forceFill([
                'two_factor_secret' => $secretKey,
                'two_factor_confirmed_at' => now(),
            ])->save();

            // Valider la session pour ne pas lui redemander tout de suite
            app(Authenticator::class)->boot($request);

            session()->forget('2fa_secret');

            return back()->with('success', 'La double authentification a été activée avec succès.');
        }

        return back()->with('error', 'Code invalide. Veuillez réessayer.');
    }

    /**
     * Désactiver la double authentification
     */
    public function disable2FA(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Mot de passe incorrect.');
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return back()->with('success', 'La double authentification a été désactivée.');
    }

    /**
     * Mettre à jour la configuration IMAP (Webmail) pour le client
     */
    public function updateWebmail(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'imap_host' => 'required|string',
            'imap_port' => 'required|integer',
            'imap_encryption' => 'required|in:ssl,tls,false',
            'imap_username' => 'required|string',
            'imap_password' => 'required|string',
        ]);

        $user = Auth::user();

        $config = ClientEmailConfig::firstOrNew(['client_id' => $request->client_id]);
        
        $config->fill([
            'imap_host' => $request->imap_host,
            'imap_port' => $request->imap_port,
            'imap_encryption' => $request->imap_encryption,
            'imap_username' => $request->imap_username,
            'imap_password' => $request->imap_password,
            'is_active' => true,
        ]);
        
        // Enregistrer également SMTP par défaut (souvent même hôte) pour pas que ça casse si manquant
        if (!$config->exists) {
            $config->smtp_host = $request->imap_host;
            $config->smtp_port = 587;
            $config->smtp_encryption = 'tls';
            $config->smtp_username = $request->imap_username;
            $config->smtp_password = $request->imap_password;
            $config->from_address = $request->imap_username;
            $config->from_name = 'Webmail';
        }

        $config->save();

        return back()->with('success', 'Configuration Webmail enregistrée avec succès.');
    }
}
