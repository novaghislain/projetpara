<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Gel\ClientInvitation;
use App\Models\Gel\EcritureComptable;
use App\Models\UserClient;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du tableau de bord de l'espace Gel Business.
 *
 * Ce contrôleur fournit les indicateurs clés de performance (KPI)
 * pour l'entreprise connectée : chiffre d'affaires mensuel, charges,
 * dernières écritures comptables et informations du cabinet comptable associé.
 */
class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord principal.
     *
     * Récupère et calcule les statistiques financières du mois en cours
     * pour l'utilisateur authentifié : CA (classe 7), charges (classe 6),
     * les 5 dernières écritures et les coordonnées du cabinet comptable.
     *
     * @return \Illuminate\View\View
     */
    public function index(\Illuminate\Http\Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Switch context if a specific client is requested (usually by accountants)
        if ($request->has('client_id')) {
            $user->switchToClient($request->client_id);
            $user->refresh();
        }

        // Déterminer l'ID client à partir de l'utilisateur connecté
        $clientId = $user->client_id ?? $user->active_client_id;

        // Aucun client associé -> retourner des statistiques vides
        if (!$clientId) {
            $stats = [
                'entreprise' => 'Mon Entreprise',
                'ca_mensuel' => 0,
                'charges_mensuelles' => 0,
                'comptable_nom' => 'Non assigné',
                'comptable_email' => null,
                'comptable_telephone' => null,
            ];
            $recentEcritures = collect([]);
            return view('gel-business.dashboard', compact('stats', 'recentEcritures') + ['currentSection' => 'dashboard']);
        }

        // Client introuvable -> retourner des statistiques vides
        $client = Client::find($clientId);
        if (!$client) {
            $stats = [
                'entreprise' => 'Mon Entreprise',
                'ca_mensuel' => 0,
                'charges_mensuelles' => 0,
                'comptable_nom' => 'Non assigné',
                'comptable_email' => null,
                'comptable_telephone' => null,
            ];
            $recentEcritures = collect([]);
            return view('gel-business.dashboard', compact('stats', 'recentEcritures') + ['currentSection' => 'dashboard']);
        }

        // Récupérer les 5 dernières écritures comptables liées à ce client
        $recentEcritures = EcritureComptable::where('client_id', $clientId)
            ->with('journal:id,code')
            ->latest()
            ->take(5)
            ->get();

        // Calcul du chiffre d'affaires mensuel (comptes de la classe 7 — produits)
        $caMensuel = EcritureComptable::where('client_id', $clientId)
            ->where('valide', true)
            ->whereMonth('date_ecriture', now()->month)
            ->whereYear('date_ecriture', now()->year)
            ->whereHas('lignes.compte', function ($q) {
                $q->where('classe', '7');
            })
            ->sum('total_credit');

        // Calcul des charges mensuelles (comptes de la classe 6)
        $chargesMensuelles = EcritureComptable::where('client_id', $clientId)
            ->where('valide', true)
            ->whereMonth('date_ecriture', now()->month)
            ->whereYear('date_ecriture', now()->year)
            ->whereHas('lignes.compte', function ($q) {
                $q->where('classe', '6');
            })
            ->sum('total_debit');

        // Récupération des informations du cabinet comptable associé au client
        $cabinetNom = 'Non assigné';
        $cabinetEmail = null;
        $cabinetTelephone = null;

        // Tentative de récupération via la table gel_clients (qui contient cabinet_id)
        try {
            $gelClient = \App\Models\Gel\Client::find($clientId);
            if ($gelClient && $gelClient->cabinet_id) {
                $cabinet = $gelClient->cabinet;
                if ($cabinet) {
                    $cabinetNom = $cabinet->nom ?? $cabinet->name ?? 'Cabinet comptable';
                    $cabinetEmail = $cabinet->email ?? null;
                    $cabinetTelephone = $cabinet->telephone ?? $cabinet->phone ?? null;
                }
            }
        } catch (\Exception $e) {
            // Échec silencieux : la table gel_clients peut ne pas exister
            // ou avoir une structure différente (clients démo / migration en cours)
        }

        // ─── PERSONNEL AFFECTÉ (MODÈLE 2 - SERVICE GÉRÉ) ───
        $secretaireNom = 'Non assigné';
        $comptableNom = 'Non assigné';
        
        if ($client->service_mode === 'service_gere') {
            if ($client->assignedSecretary) {
                $secretaireNom = $client->assignedSecretary->prenom . ' ' . $client->assignedSecretary->name;
            }
            if ($client->assignedAccountant) {
                $comptableNom = $client->assignedAccountant->prenom . ' ' . $client->assignedAccountant->name;
            }
        } else {
            // Logiciel seul : on utilise les infos du cabinet (existant) ou on affiche le collab
            $comptableNom = $cabinetNom;
        }

        // ─── ACTIVITÉ RÉCENTE (SERVICE GÉRÉ) ───
        $recentActivities = collect([]);
        if ($client->service_mode === 'service_gere') {
            $assignedUserIds = [];
            if ($client->assigned_secretary_id) $assignedUserIds[] = $client->assigned_secretary_id;
            if ($client->assigned_accountant_id) $assignedUserIds[] = $client->assigned_accountant_id;
            
            if (!empty($assignedUserIds)) {
                $recentActivities = \App\Models\AuditLog::where('client_id', $clientId)
                    ->whereIn('user_id', $assignedUserIds)
                    ->latest()
                    ->take(10)
                    ->get();
            }
        }

        // Assemblage des statistiques à passer à la vue
        $stats = [
            'entreprise' => $client->company_name ?? 'Mon Entreprise',
            'ca_mensuel' => $caMensuel,
            'charges_mensuelles' => $chargesMensuelles,
            'comptable_nom' => $comptableNom,
            'secretaire_nom' => $secretaireNom,
            'service_mode' => $client->service_mode,
            'comptable_email' => $cabinetEmail,
            'comptable_telephone' => $cabinetTelephone,
        ];

        // ─── Logique de sélection d'espace (Comptabilité / Secrétariat / Gestion) ───
        // Après l'onboarding, les 3 fonctionnalités sont toujours disponibles :
        // on affiche le sélecteur tant que l'utilisateur n'a pas choisi d'espace.
        if ($request->has('clear_workspace')) {
            session()->forget('active_workspace');
        }

        if (!session()->has('active_workspace')) {
            return view('gel-business.workspace-selector', compact('client') + ['currentSection' => 'dashboard']);
        }

        // Données pour le panneau "Accès collaborateurs" du dashboard
        $invitations = ClientInvitation::where('client_id', $clientId)
            ->latest()
            ->get();
        $collaborators = UserClient::where('client_id', $clientId)
            ->with('user')
            ->get();

        return view('gel-business.dashboard', compact('client', 'stats', 'recentEcritures', 'invitations', 'collaborators', 'recentActivities') + ['currentSection' => 'dashboard']);
    }

    /**
     * Change l'espace de travail actif (comptabilite, secretariat ou gestion).
     */
    public function setWorkspace($type)
    {
        if (\in_array($type, ['comptabilite', 'secretariat', 'gestion'])) {
            session(['active_workspace' => $type]);
        }
        return redirect()->route('gel-business.dashboard');
    }
}
