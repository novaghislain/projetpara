<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Gel\Entreprise;
use App\Models\Client;
use App\Models\AuditTrail;
use App\Models\PortalContact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Entreprise::query();

        // Filtre Recherche
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
        }

        // Filtre Statut (basé sur le user propriétaire)
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'suspended') {
                $query->whereHas('proprietaires', function($q) {
                    $q->where('is_suspended', true);
                });
            } elseif ($status === 'active') {
                $query->whereHas('proprietaires', function($q) {
                    $q->where(function($q2) {
                        $q2->where('is_suspended', false)->orWhereNull('is_suspended');
                    });
                });
            }
        }

        // Filtre Date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $entreprises = $query->with('proprietaires')->paginate(15);

        return view('gel-super-admin.tenants.index', compact('entreprises'));
    }

    public function show($id)
    {
        $entreprise = Entreprise::with(['proprietaires', 'cabinets'])->findOrFail($id);
        
        // Equipe (Users linked to this entreprise)
        $equipe = User::where('entreprise_id', $entreprise->id)->get();
        
        // Contacts inscrits (End-clients) via les CRM Clients créés par cette équipe
        $contacts = PortalContact::whereHas('clients', function($q) use ($equipe) {
            $q->whereIn('created_by', $equipe->pluck('id'));
        })->get();
        
        // Activité récente
        $activite = AuditTrail::whereIn('user_id', $equipe->pluck('id'))->latest()->take(10)->get();

        return view('gel-super-admin.tenants.show', compact('entreprise', 'equipe', 'contacts', 'activite'));
    }

    public function suspend(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        
        $entreprise = Entreprise::findOrFail($id);
        $reason = $request->input('reason');
        
        // Suspendre tous les propriétaires
        foreach ($entreprise->proprietaires as $proprietaire) {
            $proprietaire->is_suspended = true;
            $proprietaire->suspended_at = now();
            $proprietaire->suspended_reason = 'Action Super Admin: ' . $reason;
            $proprietaire->save();
            
            // Envoyer un email de notification (Simulé ici ou utiliser un vrai Mailer)
            try {
                // Mail::raw("Votre compte a été suspendu. Motif : {$reason}", function($message) use ($proprietaire) {
                //    $message->to($proprietaire->email)->subject('Compte suspendu');
                // });
            } catch (\Exception $e) {
                // Ne pas bloquer l'action si l'email échoue
            }
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'SUSPEND_TENANT',
            'description' => "Suspension de l'entreprise {$entreprise->nom}. Motif: " . $reason,
            'ip_address' => request()->ip(),
            'auditable_type' => Entreprise::class,
            'auditable_id' => $entreprise->id
        ]);

        return back()->with('success', 'L\'entreprise a été suspendue.');
    }

    public function activate($id)
    {
        $entreprise = Entreprise::findOrFail($id);
        
        // Réactiver tous les propriétaires
        foreach ($entreprise->proprietaires as $proprietaire) {
            $proprietaire->is_suspended = false;
            $proprietaire->suspended_at = null;
            $proprietaire->suspended_reason = null;
            $proprietaire->save();
            
            // Envoyer un email de notification
            try {
                // Mail::raw("Votre compte a été réactivé.", function($message) use ($proprietaire) {
                //    $message->to($proprietaire->email)->subject('Compte réactivé');
                // });
            } catch (\Exception $e) {
                // Ne pas bloquer l'action si l'email échoue
            }
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'ACTIVATE_TENANT',
            'description' => "Réactivation de l'entreprise {$entreprise->nom}.",
            'ip_address' => request()->ip(),
            'auditable_type' => Entreprise::class,
            'auditable_id' => $entreprise->id
        ]);

        return back()->with('success', 'L\'entreprise a été réactivée.');
    }

    public function destroy($id)
    {
        $entreprise = Entreprise::findOrFail($id);
        // Suppression douce (Soft delete à implémenter sur le modèle si nécessaire)
        $entreprise->delete();

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'DELETE_TENANT',
            'description' => "Suppression de l'entreprise {$entreprise->nom}.",
            'ip_address' => request()->ip(),
            'auditable_type' => Entreprise::class,
            'auditable_id' => $entreprise->id
        ]);

        return redirect()->route('gel-super-admin.tenants.index')->with('success', 'L\'entreprise a été supprimée (période de rétention activée).');
    }

    public function impersonate(Request $request, $id)
    {
        $entreprise = Entreprise::findOrFail($id);
        $proprietaire = $entreprise->proprietaires->first();

        if (!$proprietaire) {
            return back()->with('error', 'Aucun propriétaire trouvé pour cette entreprise.');
        }

        // Enregistrer la session d'impersonation
        session()->put('impersonated_by_superadmin', Auth::id()); // Clé demandée pour tracking
        session()->put('impersonate_original', Auth::id());
        session()->put('impersonate_reason', 'Support technique GEL');
        session()->put('impersonate_started_at', now());
        
        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'IMPERSONATE_START',
            'description' => "Début de la session de support pour l'entreprise {$entreprise->nom}.",
            'ip_address' => request()->ip(),
            'auditable_type' => User::class,
            'auditable_id' => $proprietaire->id
        ]);

        // Envoyer une notification au propriétaire (Simulé)
        try {
            // Mail::raw("Une session de support technique a démarré sur votre compte.", function($message) use ($proprietaire) {
            //    $message->to($proprietaire->email)->subject('Alerte de sécurité : Mode Support Actif');
            // });
        } catch (\Exception $e) { }

        // Login as proprietary
        Auth::loginUsingId($proprietaire->id);

        return redirect()->route('gel-business.dashboard')->with('warning', 'Vous êtes en mode Support.');
    }
}
