<?php

namespace App\Http\Controllers\GelInformaticien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditTrail;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SecurityController extends Controller
{
    /**
     * Affiche le panneau de sécurité (blocages, requêtes de reset, alertes)
     */
    public function index()
    {
        $suspendedUsers = User::where('is_suspended', true)->get();
        
        $securityAlerts = AuditTrail::whereIn('event', ['LOGIN_FAILED', '2FA_FAILED'])
                                  ->orderBy('created_at', 'desc')
                                  ->take(50)
                                  ->get();

        return view('gel-informaticien.security.index', compact('suspendedUsers', 'securityAlerts'));
    }

    /**
     * Débloque un compte utilisateur suspendu
     */
    public function unlockUser($id)
    {
        $user = User::findOrFail($id);
        
        $user->update([
            'is_suspended' => false,
            'suspended_at' => null,
            'suspended_reason' => null,
            'login_count' => 0 // Reset attempts
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'event' => 'ACCOUNT_UNLOCKED',
            'description' => "Le compte de {$user->email} a été débloqué par le support informatique.",
            'ip_address' => request()->ip(),
            'auditable_type' => User::class,
            'auditable_id' => $user->id
        ]);

        return back()->with('success', 'Le compte a été débloqué avec succès.');
    }

    /**
     * Force la réinitialisation du mot de passe
     */
    public function forceResetPassword($id)
    {
        $user = User::findOrFail($id);
        $newPassword = Str::random(12);
        
        $user->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => true
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'event' => 'FORCE_PASSWORD_RESET',
            'description' => "Le mot de passe de {$user->email} a été réinitialisé par le support informatique.",
            'ip_address' => request()->ip(),
            'auditable_type' => User::class,
            'auditable_id' => $user->id
        ]);

        return back()->with('success', "Mot de passe réinitialisé. Le mot de passe temporaire est : {$newPassword}");
    }

    /**
     * Demande un accès temporaire à un dossier client pour débogage
     */
    public function requestTemporaryAccess(Request $request, $clientId)
    {
        $request->validate([
            'reason' => 'required|string|min:10'
        ]);
        
        // Log the exceptional access request
        AuditLog::create([
            'client_id' => $clientId,
            'user_id' => auth()->id(),
            'action' => 'it_temporary_access',
            'description' => "Accès technique temporaire accordé à l'informaticien " . auth()->user()->name . ". Motif : " . $request->reason,
            'ip_address' => request()->ip(),
        ]);
        
        // Setup session for temporary access
        session(['it_temporary_access_client_id' => $clientId]);
        session(['it_temporary_access_expires_at' => now()->addHour()]);
        
        // Switch the user to the client context
        auth()->user()->switchToClient($clientId);

        return redirect()->route('gel-secretary.dashboard')->with('warning', 'ATTENTION : Vous êtes en mode d\'intervention technique. Toutes vos actions sont tracées.');
    }
}
