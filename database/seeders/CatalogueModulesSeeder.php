<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatalogueCategory;
use App\Models\CatalogueService;
use App\Models\CatalogueOrder;
use App\Models\CatalogueOrderMessage;
use Illuminate\Support\Facades\DB;

class CatalogueModulesSeeder extends Seeder
{
    public function run()
    {
        // Nettoyer d'abord
        DB::statement('PRAGMA foreign_keys = OFF;');
        CatalogueOrderMessage::truncate();
        CatalogueOrder::truncate();
        CatalogueService::truncate();
        CatalogueCategory::truncate();
        DB::statement('PRAGMA foreign_keys = ON;');

        // 1. Catégories
        $catGestion = CatalogueCategory::create(['nom' => 'Gestion & Productivité', 'icone' => 'bi-briefcase', 'description' => 'Outils de gestion quotidienne', 'couleur' => 'text-blue-600', 'ordre' => 1, 'actif' => true]);
        $catCompta = CatalogueCategory::create(['nom' => 'Comptabilité & Finance', 'icone' => 'bi-calculator', 'description' => 'Conformité OHADA et gestion financière', 'couleur' => 'text-green-600', 'ordre' => 2, 'actif' => true]);
        $catRH = CatalogueCategory::create(['nom' => 'Social, Paie & Juridique', 'icone' => 'bi-people', 'description' => 'Ressources humaines et obligations légales', 'couleur' => 'text-purple-600', 'ordre' => 3, 'actif' => true]);
        $catIA = CatalogueCategory::create(['nom' => 'Outils Avancés & IA', 'icone' => 'bi-robot', 'description' => 'GEL Intelligence et automatisation', 'couleur' => 'text-orange-600', 'ordre' => 4, 'actif' => true]);
        $catFinance = CatalogueCategory::create(['nom' => 'Paiement & Finance Locale', 'icone' => 'bi-wallet2', 'description' => 'Tontine, Mobile Money et POS', 'couleur' => 'text-teal-600', 'ordre' => 5, 'actif' => true]);
        $catDivers = CatalogueCategory::create(['nom' => 'Divers & IT', 'icone' => 'bi-hdd-network', 'description' => 'Support, blog et services complémentaires', 'couleur' => 'text-gray-600', 'ordre' => 6, 'actif' => true]);

        $modules = [
            // Gestion & Productivité
            ['category_id' => $catGestion->id, 'nom' => 'CRM Clients', 'description' => 'Gestion complète du portefeuille client, contacts et historique.', 'icone' => 'bi-person-lines-fill', 'ordre_affichage' => 1],
            ['category_id' => $catGestion->id, 'nom' => 'GED', 'description' => 'Gestion Électronique des Documents centralisée et sécurisée.', 'icone' => 'bi-folder2-open', 'ordre_affichage' => 2],
            ['category_id' => $catGestion->id, 'nom' => 'Pôles & Missions', 'description' => 'Assignation et suivi des missions par collaborateur.', 'icone' => 'bi-diagram-3', 'ordre_affichage' => 3],
            ['category_id' => $catGestion->id, 'nom' => 'Projets', 'description' => 'Planification et suivi des projets complexes.', 'icone' => 'bi-kanban', 'ordre_affichage' => 4],
            
            // Comptabilité & Finance
            ['category_id' => $catCompta->id, 'nom' => 'Comptabilité', 'description' => 'Saisie comptable, journaux et rapprochements bancaires.', 'icone' => 'bi-journal-text', 'ordre_affichage' => 1],
            ['category_id' => $catCompta->id, 'nom' => 'Comptabilité avancée & Conformité Bénin', 'description' => 'Génération des états financiers certifiés SYSCOHADA et déclarations DGI.', 'icone' => 'bi-shield-check', 'ordre_affichage' => 2],
            ['category_id' => $catCompta->id, 'nom' => 'ERP', 'description' => 'Gestion intégrée de vos ressources d\'entreprise.', 'icone' => 'bi-box-seam', 'ordre_affichage' => 3],
            ['category_id' => $catCompta->id, 'nom' => 'Facturation e-MECeF', 'description' => 'Émission de factures normalisées connectées à la DGI Bénin.', 'icone' => 'bi-receipt', 'ordre_affichage' => 4],
            ['category_id' => $catCompta->id, 'nom' => 'Caisse', 'description' => 'Suivi des flux de trésorerie et gestion des dépenses.', 'icone' => 'bi-cash-coin', 'ordre_affichage' => 5],
            ['category_id' => $catCompta->id, 'nom' => 'Transactions Récurrentes', 'description' => 'Automatisation des abonnements et factures périodiques.', 'icone' => 'bi-arrow-repeat', 'ordre_affichage' => 6],
            
            // Social, Paie & Juridique
            ['category_id' => $catRH->id, 'nom' => 'RH & Paie', 'description' => 'Édition des fiches de paie conformes aux barèmes IRPP/CNSS du Bénin.', 'icone' => 'bi-person-badge', 'ordre_affichage' => 1],
            ['category_id' => $catRH->id, 'nom' => 'Juridique', 'description' => 'Gestion des actes, AG, litiges et conformité réglementaire.', 'icone' => 'bi-bank2', 'ordre_affichage' => 2],
            ['category_id' => $catRH->id, 'nom' => 'Secrétariat DAE', 'description' => 'Direction Administrative et Executive, gestion des courriers.', 'icone' => 'bi-envelope-paper', 'ordre_affichage' => 3],
            ['category_id' => $catRH->id, 'nom' => 'Signature électronique', 'description' => 'Validation légale de documents à distance.', 'icone' => 'bi-pen', 'ordre_affichage' => 4],
            ['category_id' => $catRH->id, 'nom' => 'Workflows d\'approbation', 'description' => 'Circuits de validation multiniveaux paramétrables.', 'icone' => 'bi-check2-all', 'ordre_affichage' => 5],
            
            // Outils Avancés & IA
            ['category_id' => $catIA->id, 'nom' => 'IA & Automatisation', 'description' => 'Outils d\'OCR pour la saisie automatique de factures.', 'icone' => 'bi-magic', 'ordre_affichage' => 1],
            ['category_id' => $catIA->id, 'nom' => 'GEL Intelligence', 'description' => 'Assistant virtuel et Système Multi-Agents IA pour le cabinet.', 'icone' => 'bi-cpu', 'ordre_affichage' => 2],
            ['category_id' => $catIA->id, 'nom' => 'Fil d\'Activité IA', 'description' => 'Activity Feed intelligent résumant les actions du cabinet.', 'icone' => 'bi-activity', 'ordre_affichage' => 3],
            ['category_id' => $catIA->id, 'nom' => 'Omnisearch', 'description' => 'Moteur de recherche globale instantané.', 'icone' => 'bi-search', 'ordre_affichage' => 4],
            ['category_id' => $catIA->id, 'nom' => 'Workpapers', 'description' => 'Dossiers de travail numériques pour la révision comptable.', 'icone' => 'bi-file-earmark-spreadsheet', 'ordre_affichage' => 5],
            ['category_id' => $catIA->id, 'nom' => 'Magic Links', 'description' => 'Demande de documents clients sécurisée sans mot de passe.', 'icone' => 'bi-link-45deg', 'ordre_affichage' => 6],
            
            // Paiement & Finance Locale
            ['category_id' => $catFinance->id, 'nom' => 'Paiements Mobiles', 'description' => 'Intégration MTN Mobile Money et Moov Money pour le règlement.', 'icone' => 'bi-phone', 'ordre_affichage' => 1],
            ['category_id' => $catFinance->id, 'nom' => 'Tontine / Microfinance', 'description' => 'Gestion numérisée des cotisations et épargnes locales.', 'icone' => 'bi-piggy-bank', 'ordre_affichage' => 2],
            ['category_id' => $catFinance->id, 'nom' => 'Gestion Commerciale et POS', 'description' => 'Point de vente et suivi d\'inventaire connectés à la compta.', 'icone' => 'bi-shop', 'ordre_affichage' => 3],
            
            // Divers & IT
            ['category_id' => $catDivers->id, 'nom' => 'Services Informatiques (IT)', 'description' => 'Helpdesk, gestion de parc et maintenance réseau.', 'icone' => 'bi-laptop', 'ordre_affichage' => 1],
            ['category_id' => $catDivers->id, 'nom' => 'Blog / Actualités', 'description' => 'Veille fiscale et informations utiles.', 'icone' => 'bi-newspaper', 'ordre_affichage' => 2],
            ['category_id' => $catDivers->id, 'nom' => 'Catalogue e-commerce', 'description' => 'Boutique en ligne intégrée pour vos prestations.', 'icone' => 'bi-cart3', 'ordre_affichage' => 3],
        ];

        foreach ($modules as $m) {
            CatalogueService::create([
                'category_id' => $m['category_id'],
                'nom' => $m['nom'],
                'description' => $m['description'],
                'inclus_json' => ['Support technique', 'Mise à jour légale'],
                'tarif_type' => 'devis',
                'ordre_affichage' => $m['ordre_affichage'],
                'actif' => true,
            ]);
        }
    }
}
