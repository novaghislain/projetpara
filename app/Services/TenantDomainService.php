<?php
// =============================================================================
// FICHIER : TenantDomainService.php
// RÔLE    : Service métier — Gestion des domaines d'activité & modules compta
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Ce service centralise la logique liée aux domaines d'activité des clients.
// Il est utilisé par :
//   - Le middleware CheckComptaDomainModule
//   - Le contrôleur d'inscription (CompanyRegistrationController)
//   - La sidebar dynamique du CompanyLayout.vue
//
// Fonctions principales :
//   - activerModulesDomaine()   → active les modules comptables d'un client
//   - getDefaultConfig()       → config comptable par défaut selon le domaine
//   - hasComptaModule()        → vérifie l'accès à un module
//   - getActiveModules()       → liste des modules actifs
//   - getModulesSidebar()      → modules groupés pour la sidebar (core/métier/transversal)
// =============================================================================

namespace App\Services;

use App\Models\BusinessDomain;
use App\Models\Client;
use App\Models\ClientAccountingModule;
use Illuminate\Support\Facades\Auth;

class TenantDomainService
{
    /**
     * Active les modules comptables selon le domaine du client.
     * Appelé lors de la validation du client (inscription ou activation super_admin).
     *
     * @param  Client  $client  Le client dont on active les modules
     */
    public function activerModulesDomaine(Client $client): void
    {
        $domain = BusinessDomain::find($client->domain_id);
        if (!$domain) {
            return;
        }

        $modules = $domain->modules_comptables;

        foreach ($modules as $module) {
            ClientAccountingModule::firstOrCreate(
                [
                    'client_id' => $client->id,
                    'module' => $module,
                ],
                [
                    'is_active' => true,
                    'activated_at' => now(),
                    'activated_by' => Auth::id(),
                    'config' => self::getDefaultConfig($module, $domain->code),
                ]
            );
        }

        $client->update([
            'domain_confirmed' => true,
            'domain_confirmed_at' => now(),
        ]);
    }

    /**
     * Retourne la config par défaut d'un module selon le domaine.
     * Permet de pré-paramétrer certains comptes comptables spécifiques au domaine.
     *
     * @param  string  $module      Le module (ex: facturation, stock)
     * @param  string  $domainCode  Le code du domaine (ex: commerce, hotel)
     * @return array   Configuration par défaut (peut être vide)
     */
    public static function getDefaultConfig(string $module, string $domainCode): array
    {
        $configs = [
            'facturation' => [
                'commerce'  => ['compte_ventes' => '701', 'compte_clients' => '411'],
                'hotel'     => ['compte_ventes' => '706', 'compte_clients' => '411'],
                'scolaire'  => ['compte_ventes' => '706', 'compte_clients' => '411'],
                'location'  => ['compte_ventes' => '703', 'compte_clients' => '411'],
                'pressing'  => ['compte_ventes' => '706', 'compte_clients' => '411'],
                'transport' => ['compte_ventes' => '706', 'compte_clients' => '411'],
                'morgue'    => ['compte_ventes' => '706', 'compte_clients' => '411'],
            ],
            'stock' => [
                'commerce'  => ['methode_valorisation' => 'CUMP', 'compte_stock' => '301'],
                'industrie' => ['methode_valorisation' => 'FIFO', 'compte_stock' => '321'],
                'sante'     => ['methode_valorisation' => 'FIFO', 'compte_stock' => '321'],
            ],
            'taxe_nuitee' => [
                'hotel' => ['taux' => 1000, 'compte' => '447'],
            ],
        ];

        return $configs[$module][$domainCode] ?? [];
    }

    /**
     * Vérifie si un client a accès à un sous-module comptable.
     *
     * @param  int     $clientId  ID du client
     * @param  string  $module    Nom du module (ex: stock, gestion_chambres)
     * @return bool    True si le module est actif pour ce client
     */
    public static function hasComptaModule(int $clientId, string $module): bool
    {
        return ClientAccountingModule::where('client_id', $clientId)
            ->where('module', $module)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Retourne tous les modules comptables actifs d'un client.
     *
     * @param  int    $clientId  ID du client
     * @return array  Liste des noms de modules actifs
     */
    public static function getActiveModules(int $clientId): array
    {
        return ClientAccountingModule::where('client_id', $clientId)
            ->where('is_active', true)
            ->pluck('module')
            ->toArray();
    }

    /**
     * Retourne la liste des modules groupés pour construire la sidebar.
     * Les modules sont répartis en 3 catégories :
     *   - core        : modules toujours présents (plan compta, journaux, balance…)
     *   - metier      : modules spécifiques au domaine (stock, chambres, classes…)
     *   - transversal : modules communs à tous (mobile money, analytique, budget…)
     *
     * @param  int    $clientId  ID du client
     * @return array  Tableau avec les clés 'core', 'metier', 'transversal'
     */
    public static function getModulesSidebar(int $clientId): array
    {
        $actifs = self::getActiveModules($clientId);

        return [
            'core' => array_values(array_intersect($actifs, [
                'plan_comptable', 'journaux', 'balance',
                'etats_financiers', 'fiscalite', 'paie', 'tresorerie',
            ])),
            'metier' => array_values(array_intersect($actifs, [
                'facturation', 'facturation_nuitee', 'facturation_heure',
                'facturation_scolarite', 'facturation_honoraires',
                'facturation_pressing', 'facturation_fret', 'facturation_morgue',
                'stock', 'arrivage', 'commandes', 'emballage',
                'grille_tarifaire', 'commissions',
                'gestion_chambres', 'reservations', 'articles_chambre', 'taxe_nuitee',
                'gestion_classes', 'gestion_eleves', 'annee_scolaire',
                'gestion_biens', 'gestion_locataires', 'quittances_loyer',
                'avances_cautions', 'loyers_impayes', 'releves_proprietaires',
                'gestion_tontines', 'cotisations', 'attributions',
                'commandes_pressing', 'articles_pressing',
                'gestion_vehicules', 'tournees',
                'transit_dossiers', 'transit_charges',
                'gestion_depots', 'gestion_retraits', 'parametres_morgue',
                'multi_societes', 'missions', 'ged_documents',
            ])),
            'transversal' => array_values(array_intersect($actifs, [
                'mobile_money', 'analytique', 'budget',
                'immobilisations', 'relances', 'rapports',
            ])),
        ];
    }
}
