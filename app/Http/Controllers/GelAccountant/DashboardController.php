<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\Cabinet;
use App\Models\Gel\Client;
use App\Models\Gel\EcritureComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du tableau de bord du module comptable (GelAccountant).
 *
 * Ce contrôleur centralise les indicateurs clés de performance (KPI)
 * destinés à la vue d'ensemble d'un cabinet comptable : nombre de clients
 * actifs, volume d'écritures, état de la balance, etc. Il adapte les
 * statistiques en fonction du cabinet connecté et d'un éventuel filtre
 * par client.
 */
class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord du cabinet comptable.
     *
     * Calcule et renvoie les statistiques consolidées (clients actifs,
     * écritures du mois, éléments en attente, équilibre de la balance)
     * ainsi que les listes des clients et écritures récentes. Si
     * l'utilisateur n'est rattaché à aucun cabinet ($cabinetId nul),
     * un tableau de bord vide avec des valeurs par défaut est affiché.
     *
     * @param  Request $request La requête entrante, contenant
     *                          optionnellement le filtre `client_id`.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        // Si l'utilisateur n'est pas rattaché à un cabinet, on affiche
        // un tableau de bord basique avec des valeurs par défaut.
        if (!$cabinetId) {
            $stats = [
                'clients_actifs' => 0,
                'ecritures_mois' => 0,
                'en_attente' => 0,
                'balance_equilibree' => false,
                'derniere_ecriture' => null,
                'derniere_ecriture_date' => null,
            ];
            $recentClients = collect([]);
            $recentEcritures = collect([]);
            $clients = collect([]);
            $cabinet = null;

            $currentSection = 'dashboard';
            return view('gel-accountant.dashboard', ['currentSection' => $currentSection, 'currentPage' => 'dashboard'] + compact(
                'stats', 'recentClients', 'recentEcritures', 'clients', 'cabinet'
            ));
        }

        $cabinet = Cabinet::find($cabinetId);
        $clientId = $request->get('client_id');

        // --- Statistiques générales ---
        // Requête de base sur les clients du cabinet, avec filtre optionnel
        $clientsQuery = Client::where('cabinet_id', $cabinetId);
        if ($clientId) {
            $clientsQuery->where('id', $clientId);
        }
        $clientsActifs = (clone $clientsQuery)->where('statut', 'actif')->count();

        // Requête de base sur les écritures comptables du cabinet
        $ecrituresQuery = EcritureComptable::where('cabinet_id', $cabinetId);
        if ($clientId) {
            $ecrituresQuery->where('client_id', $clientId);
        }

        // Nombre d'écritures saisies dans le mois en cours
        $ecrituresMois = (clone $ecrituresQuery)
            ->whereMonth('date_ecriture', now()->month)
            ->whereYear('date_ecriture', now()->year)
            ->count();

        // Écritures en attente de validation (non approuvées)
        $enAttente = (clone $ecrituresQuery)
            ->where('valide', false)
            ->count();

        // Dernière écriture comptabilisée (date la plus récente)
        $lastEcriture = (clone $ecrituresQuery)
            ->latest('date_ecriture')
            ->first();

        // Vérification de l'équilibre de la balance (total débit = total crédit)
        $totalDebit = (clone $ecrituresQuery)->sum('total_debit');
        $totalCredit = (clone $ecrituresQuery)->sum('total_credit');
        $balanceEquilibree = ($totalDebit === $totalCredit) && $totalDebit > 0;

        $recentClients = (clone $clientsQuery)
            ->latest()
            ->take(5)
            ->get();

        $recentEcritures = (clone $ecrituresQuery)
            ->with(['journal:id,code', 'client:id,nom_entreprise'])
            ->latest('date_ecriture')
            ->take(5)
            ->get();

        // 1. Déclarations TVA imminentes (échéance dans les 15 jours)
        $declarationsQuery = \App\Models\Gel\GelDeclaration::where('cabinet_id', $cabinetId)
            ->whereIn('statut', ['brouillon', 'a_soumettre', 'en_retard']);
        if ($clientId) {
            $declarationsQuery->where('client_id', $clientId);
        }
        $declarationsTvaImminentes = $declarationsQuery
            ->where('date_echeance', '<=', now()->addDays(15))
            ->count();

        // 2. Calcul des Revenus (Classe 7) et Dépenses (Classe 6)
        $ecrituresIds = (clone $ecrituresQuery)
            ->whereYear('date_ecriture', now()->year)
            ->pluck('id');

        $lignesClasse7 = \App\Models\Gel\LigneEcriture::whereIn('ecriture_id', $ecrituresIds)
            ->whereHas('compte', function ($q) {
                $q->where('code', 'like', '7%');
            })->get();

        $revenus = $lignesClasse7->where('sens', 'credit')->sum('montant') - $lignesClasse7->where('sens', 'debit')->sum('montant');

        $lignesClasse6 = \App\Models\Gel\LigneEcriture::whereIn('ecriture_id', $ecrituresIds)
            ->whereHas('compte', function ($q) {
                $q->where('code', 'like', '6%');
            })->get();

        $depenses = $lignesClasse6->where('sens', 'debit')->sum('montant') - $lignesClasse6->where('sens', 'credit')->sum('montant');

        $benefice = $revenus - $depenses;

        // Comparatif N-1 (Revenus de l'année précédente)
        $ecrituresIdsN1 = (clone $ecrituresQuery)
            ->whereYear('date_ecriture', now()->subYear()->year)
            ->pluck('id');

        $lignesClasse7N1 = \App\Models\Gel\LigneEcriture::whereIn('ecriture_id', $ecrituresIdsN1)
            ->whereHas('compte', function ($q) {
                $q->where('code', 'like', '7%');
            })->get();

        $revenusN1 = $lignesClasse7N1->where('sens', 'credit')->sum('montant') - $lignesClasse7N1->where('sens', 'debit')->sum('montant');
        $evolutionRevenus = $revenusN1 > 0 ? (($revenus - $revenusN1) / $revenusN1) * 100 : 0;

        $stats = [
            'clients_actifs' => $clientsActifs,
            'ecritures_mois' => $ecrituresMois,
            'en_attente' => $enAttente,
            'balance_equilibree' => $balanceEquilibree,
            'derniere_ecriture' => $lastEcriture?->libelle,
            'derniere_ecriture_date' => $lastEcriture?->date_ecriture?->format('Y-m-d'),
            'revenus' => $revenus,
            'depenses' => $depenses,
            'benefice' => $benefice,
            'declarations_tva_imminentes' => $declarationsTvaImminentes,
            'evolution_revenus' => round($evolutionRevenus, 2),
            'revenus_n1' => $revenusN1,
        ];

        $clients = (clone $clientsQuery)->get(['id', 'nom_entreprise']);

        // 3. Bilans en attente (Exercices dont la date de fin est dépassée mais non clôturés)
        $bilansQuery = \App\Models\Gel\ExerciceComptable::where('cabinet_id', $user->cabinet_id)
            ->where('cloture', false)
            ->where('date_fin', '<', now());
        if ($clientId) {
            $bilansQuery->where('client_id', $clientId);
        }
        $bilansEnAttente = $bilansQuery->count();

        // 4. Messages non lus
        $messagesQuery = \App\Models\Gel\GelMessage::where('receiver_id', $user->id)
            ->where('est_lu', false);
        $messagesNonLus = $messagesQuery->count();

        $stats['bilans_en_attente'] = $bilansEnAttente;
        $stats['messages_non_lus'] = $messagesNonLus;

        return view('gel-accountant.dashboard', ['currentSection' => 'dashboard', 'currentPage' => 'dashboard'] + compact(
            'stats', 'recentClients', 'recentEcritures', 'clients', 'cabinet'
        ));
    }
}
