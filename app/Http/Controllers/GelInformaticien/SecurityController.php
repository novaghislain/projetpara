<?php

namespace App\Http\Controllers\GelInformaticien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditTrail;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Gel\ItTemporaryAccess;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecurityController extends Controller
{
    /**
     * Affiche le panneau de sécurité (blocages, requêtes de reset, alertes)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = User::where(function($q) {
            $q->whereIn('account_type', ['informaticien', 'internal', 'cabinet'])
              ->orWhereIn('role', ['secretaire', 'comptable', 'super_admin']);
        });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $internalUsers = $query->paginate(20);
        $internalUsers->appends(['search' => $search]);
        
        $securityAlerts = AuditTrail::whereIn('event', ['LOGIN_FAILED', '2FA_FAILED'])
                                  ->orderBy('created_at', 'desc')
                                  ->take(50)
                                  ->get();

        $activeAccesses = ItTemporaryAccess::with(['informaticien', 'client'])
                                           ->whereNull('revoked_at')
                                           ->where('expires_at', '>', now())
                                           ->get();

        return view('gel-informaticien.security.index', compact('internalUsers', 'securityAlerts', 'activeAccesses'));
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
     * Bloque / suspend un compte utilisateur
     */
    public function suspendUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $reason = $request->input('reason', 'Suspension par le service informatique');

        $user->update([
            'is_suspended' => true,
            'suspended_at' => now(),
            'suspended_reason' => $reason
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'event' => 'ACCOUNT_SUSPENDED',
            'description' => "Le compte de {$user->email} a été suspendu par le support informatique. Motif: {$reason}",
            'ip_address' => request()->ip(),
            'auditable_type' => User::class,
            'auditable_id' => $user->id
        ]);

        return back()->with('success', 'Le compte a été suspendu avec succès.');
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
     * Marque une alerte comme résolue/traitée
     */
    public function resolveAlert($id)
    {
        $alert = AuditTrail::findOrFail($id);
        $alert->update(['is_resolved' => true]);

        return back()->with('success', 'Alerte marquée comme traitée.');
    }

    /**
     * Télécharge l'historique des actions (logs) d'un utilisateur au format CSV
     */
    public function downloadUserLogs($userId)
    {
        $user = User::findOrFail($userId);
        $logs = AuditTrail::where('user_id', $user->id)
                          ->orderBy('created_at', 'desc')
                          ->get();

        $response = new StreamedResponse(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($handle, ['Date', 'IP', 'Evenement', 'Description'], ';');

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->ip_address,
                    $log->event,
                    $log->description
                ], ';');
            }

            fclose($handle);
        });

        $filename = "historique_" . Str::slug($user->name . "_" . $user->prenom) . "_" . date('Ymd_His') . ".csv";

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
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
        
        // Persist in DB for tracking and revocation
        ItTemporaryAccess::create([
            'informaticien_id' => auth()->id(),
            'client_id' => $clientId,
            'reason' => $request->reason,
            'expires_at' => now()->addHour(),
        ]);

        // Switch the user to the client context
        auth()->user()->switchToClient($clientId);

        return redirect()->route('gel-secretary.dashboard')->with('warning', 'ATTENTION : Vous êtes en mode d\'intervention technique. Toutes vos actions sont tracées.');
    }

    /**
     * Révoque un accès temporaire avant son expiration
     */
    public function revokeTemporaryAccess($id)
    {
        $access = ItTemporaryAccess::findOrFail($id);
        $access->update(['revoked_at' => now()]);

        AuditLog::create([
            'client_id' => $access->client_id,
            'user_id' => auth()->id(),
            'action' => 'it_temporary_access_revoked',
            'description' => "Accès technique temporaire de {$access->informaticien->name} révoqué manuellement.",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Accès temporaire révoqué avec succès.');
    }
}
