<?php

namespace Database\Seeders;

use App\Models\BusinessDomain;
use Illuminate\Database\Seeder;

class BusinessDomainSeeder extends Seeder
{
    public function run(): void
    {
        $domains = [
            [
                'code' => 'commerce',
                'label' => 'Commerce et Distribution',
                'description' => 'Vente de marchandises, distribution, import/export, négoce',
                'icon' => 'bi-shop',
                'sort_order' => 1,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie', 'facturation',
                    'stock', 'arrivage', 'commandes', 'emballage',
                    'grille_tarifaire', 'commissions', 'mobile_money', 'rapports',
                ],
                'modules_optionnels' => ['analytique', 'budget', 'relances'],
            ],
            [
                'code' => 'hotel',
                'label' => 'Hôtel et Hébergement',
                'description' => 'Hôtels, auberges, résidences hôtelières, gîtes',
                'icon' => 'bi-building',
                'sort_order' => 2,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'facturation_nuitee', 'facturation_heure', 'taxe_nuitee',
                    'gestion_chambres', 'reservations', 'articles_chambre',
                    'mobile_money', 'rapports',
                ],
                'modules_optionnels' => ['analytique', 'relances'],
            ],
            [
                'code' => 'scolaire',
                'label' => 'Établissement Scolaire',
                'description' => 'Écoles, collèges, lycées, centres de formation',
                'icon' => 'bi-mortarboard',
                'sort_order' => 3,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'facturation_scolarite', 'gestion_classes', 'gestion_eleves',
                    'annee_scolaire', 'mobile_money', 'rapports',
                ],
                'modules_optionnels' => ['budget', 'analytique'],
            ],
            [
                'code' => 'location',
                'label' => 'Location Immobilière',
                'description' => 'Agences immobilières, gestion locative, propriétaires bailleurs',
                'icon' => 'bi-house-door',
                'sort_order' => 4,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'gestion_biens', 'gestion_locataires', 'quittances_loyer',
                    'avances_cautions', 'loyers_impayes', 'releves_proprietaires',
                    'mobile_money', 'rapports',
                ],
                'modules_optionnels' => ['relances', 'analytique'],
            ],
            [
                'code' => 'tontine',
                'label' => 'Tontine et Finance Participative',
                'description' => 'Associations tontinières, groupes d\'épargne, mutuelles',
                'icon' => 'bi-people',
                'sort_order' => 5,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'gestion_tontines', 'cotisations', 'attributions',
                    'mobile_money', 'rapports',
                ],
                'modules_optionnels' => [],
            ],
            [
                'code' => 'pressing',
                'label' => 'Pressing et Blanchisserie',
                'description' => 'Pressings, blanchisseries, nettoyage à sec',
                'icon' => 'bi-droplet',
                'sort_order' => 6,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'facturation_pressing', 'commandes_pressing', 'articles_pressing',
                    'mobile_money', 'rapports',
                ],
                'modules_optionnels' => ['relances'],
            ],
            [
                'code' => 'transport',
                'label' => 'Transport et Transit',
                'description' => 'Transport de marchandises, transit douane, logistique',
                'icon' => 'bi-truck',
                'sort_order' => 7,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'facturation_fret', 'gestion_vehicules', 'tournees',
                    'transit_dossiers', 'transit_charges', 'transit_points',
                    'mobile_money', 'rapports',
                ],
                'modules_optionnels' => ['analytique', 'budget'],
            ],
            [
                'code' => 'morgue',
                'label' => 'Morgue et Pompes Funèbres',
                'description' => 'Services funéraires, morgues, pompes funèbres',
                'icon' => 'bi-flower1',
                'sort_order' => 8,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'facturation_morgue', 'gestion_depots', 'gestion_retraits',
                    'parametres_morgue', 'mobile_money', 'rapports',
                ],
                'modules_optionnels' => [],
            ],
            [
                'code' => 'cabinet_comptable',
                'label' => 'Cabinet d\'Expertise Comptable',
                'description' => 'Cabinets comptables, experts-comptables, commissaires aux comptes',
                'icon' => 'bi-briefcase',
                'sort_order' => 9,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'facturation_honoraires', 'multi_societes', 'missions',
                    'ged_documents', 'rapports',
                ],
                'modules_optionnels' => ['analytique', 'budget', 'relances'],
            ],
            [
                'code' => 'restauration',
                'label' => 'Restauration et Bar',
                'description' => 'Restaurants, bars, traiteurs, cafétérias',
                'icon' => 'bi-cup-hot',
                'sort_order' => 10,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'facturation', 'stock', 'gestion_menus',
                    'mobile_money', 'rapports',
                ],
                'modules_optionnels' => ['relances'],
            ],
            [
                'code' => 'industrie',
                'label' => 'Industrie et Production',
                'description' => 'Industrie manufacturière, production, transformation',
                'icon' => 'bi-gear',
                'sort_order' => 11,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'facturation', 'stock', 'arrivage', 'immobilisations',
                    'analytique', 'budget', 'rapports',
                ],
                'modules_optionnels' => ['emballage', 'commissions'],
            ],
            [
                'code' => 'sante',
                'label' => 'Santé et Clinique',
                'description' => 'Cliniques, hôpitaux, centres de santé, laboratoires',
                'icon' => 'bi-hospital',
                'sort_order' => 12,
                'modules_comptables' => [
                    'plan_comptable', 'journaux', 'balance', 'etats_financiers',
                    'fiscalite', 'paie', 'tresorerie',
                    'facturation', 'stock', 'mobile_money', 'rapports',
                ],
                'modules_optionnels' => ['analytique', 'relances'],
            ],
        ];

        foreach ($domains as $data) {
            BusinessDomain::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }

        $this->command->info('✓ 12 business domains seeded.');
    }
}
