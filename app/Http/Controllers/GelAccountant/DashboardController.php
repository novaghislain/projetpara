<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\EcritureComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Récupère TOUTES les entreprises où l'utilisateur a une affectation active.
        // Un comptable invité peut être rattaché à plusieurs cabinets.
        $entrepriseIds = $user->affectations()
            ->where('statut', 'active')
            ->pluck('entreprise_id')
            ->unique()
            ->values();

        // Gestion du dossier (client) actif depuis la session
        $activeClientId = session('active_client_id');
        $activeClient   = null;

        if ($activeClientId) {
            $activeClient = Client::whereIn('entreprise_id', $entrepriseIds)
                ->where('id', $activeClientId)
                ->first();
        }

        // Si pas de client actif, on ne sélectionne rien par défaut (vue globale cabinet)
        // L'utilisateur choisira dans le menu déroulant.

        // Tous les clients accessibles (toutes les entreprises affectées)
        $allClients = Client::whereIn('entreprise_id', $entrepriseIds)
            ->where('statut', 'actif')
            ->orderBy('nom_entreprise')
            ->get();

        // KPIs RÉELS — contextualisés selon le dossier actif ou toute la portée du cabinet
        $kpis = $this->computeKpis($entrepriseIds, $activeClient?->id);

        return view('gel-accountant.dashboard', compact('activeClient', 'allClients', 'kpis'));
    }

    /**
     * Calcule les KPIs réels à partir de la base de données.
     *
     * @param  \Illuminate\Support\Collection $entrepriseIds
     * @param  string|null                    $clientId
     * @return array
     */
    private function computeKpis($entrepriseIds, ?string $clientId = null): array
    {
        // -- Trésorerie : solde total des comptes bancaires actifs
        $tresorerieQuery = DB::table('bank_accounts')
            ->whereIn('client_id', function ($q) use ($entrepriseIds, $clientId) {
                $q->select('id')->from('clients')
                    ->whereIn('entreprise_id', $entrepriseIds)
                    ->where('statut', 'actif');
                if ($clientId) {
                    $q->where('id', $clientId);
                }
            })
            ->where('is_active', true);
        $tresorerie = $tresorerieQuery->sum('current_balance');

        // -- TVA estimée : total des écritures non validées sur les comptes TVA collectée
        // Utilise gel_ecritures / gel_lignes_ecriture
        $tvaEstimee = 0;
        if (class_exists(\App\Models\GelEcriture::class)) {
            $tvaEstimee = DB::table('gel_lignes_ecriture as gl')
                ->join('gel_ecritures as ge', 'gl.ecriture_id', '=', 'ge.id')
                ->join('gel_account_types as gat', 'gl.compte_id', '=', 'gat.id')
                ->whereIn('ge.client_id', function ($q) use ($entrepriseIds, $clientId) {
                    $q->select('id')->from('clients')
                        ->whereIn('entreprise_id', $entrepriseIds)
                        ->where('statut', 'actif');
                    if ($clientId) {
                        $q->where('id', $clientId);
                    }
                })
                ->where('ge.valide', false)
                ->where('gl.sens', 'credit')
                ->sum('gl.montant');
        }

        // -- Écritures non validées (= "brouillons") en attente
        $ecrituresEnAttente = DB::table('gel_ecritures')
            ->whereIn('client_id', function ($q) use ($entrepriseIds, $clientId) {
                $q->select('id')->from('clients')
                    ->whereIn('entreprise_id', $entrepriseIds)
                    ->where('statut', 'actif');
                if ($clientId) {
                    $q->where('id', $clientId);
                }
            })
            ->where('valide', false)
            ->count();

        // -- Dernières écritures (gel_ecritures n'a pas de total_debit/credit natif,
        // les totaux se calculent depuis les lignes d'écriture)
        $dernieresEcritures = DB::table('gel_ecritures as ge')
            ->join('gel_journaux as gj', 'ge.journal_id', '=', 'gj.id')
            ->leftJoin('clients as c', 'ge.client_id', '=', 'c.id')
            ->whereIn('ge.client_id', function ($q) use ($entrepriseIds, $clientId) {
                $q->select('id')->from('clients')
                    ->whereIn('entreprise_id', $entrepriseIds)
                    ->where('statut', 'actif');
                if ($clientId) {
                    $q->where('id', $clientId);
                }
            })
            ->orderByDesc('ge.date_ecriture')
            ->limit(5)
            ->select(
                'ge.id',
                'ge.date_ecriture',
                'ge.libelle',
                'ge.valide',
                'gj.code as journal_code',
                'ge.numero_piece',
                'c.nom_entreprise as client_nom',
                // Totaux calculés depuis les lignes (sous-requêtes)
                DB::raw('(SELECT COALESCE(SUM(gl.montant),0) FROM gel_lignes_ecriture gl WHERE gl.ecriture_id = ge.id AND gl.sens = "debit") as total_debit'),
                DB::raw('(SELECT COALESCE(SUM(gl.montant),0) FROM gel_lignes_ecriture gl WHERE gl.ecriture_id = ge.id AND gl.sens = "credit") as total_credit')
            )
            ->get();

        return [
            'tresorerie'            => $tresorerie,
            'tva_estimee'           => $tvaEstimee,
            'factures_non_lettrees' => $ecrituresEnAttente, // Écritures en brouillon
            'docs_attente'          => 0, // GED — à implémenter quand GED active
            'dernieres_ecritures'   => $dernieresEcritures,
            'nb_clients'            => $allClients ?? 0,
        ];
    }
}
