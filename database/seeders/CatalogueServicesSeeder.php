<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatalogueCategory;
use App\Models\CatalogueService;
use App\Models\CatalogueOrder;
use App\Models\CatalogueOrderMessage;
use Illuminate\Support\Facades\DB;

class CatalogueServicesSeeder extends Seeder
{
    public function run()
    {
        // Nettoyer d'abord (compatible SQLite)
        DB::statement('PRAGMA foreign_keys = OFF;');
        CatalogueOrderMessage::truncate();
        CatalogueOrder::truncate();
        CatalogueService::truncate();
        CatalogueCategory::truncate();
        DB::statement('PRAGMA foreign_keys = ON;');

        // 1. Création des vraies catégories de services (prestations vendues)
        $categoriesData = [
            ['nom' => 'Administration', 'icone' => 'bi-gear-wide-connected', 'description' => 'Gestion administrative externalisée', 'couleur' => 'text-blue-600'],
            ['nom' => 'Consultation', 'icone' => 'bi-chat-dots', 'description' => 'Conseils stratégiques et audits', 'couleur' => 'text-indigo-600'],
            ['nom' => 'Fiscal', 'icone' => 'bi-receipt', 'description' => 'Déclarations et optimisation fiscale', 'couleur' => 'text-green-600'],
            ['nom' => 'IT', 'icone' => 'bi-laptop', 'description' => 'Prestations et support informatique', 'couleur' => 'text-gray-600'],
            ['nom' => 'Social & Paie', 'icone' => 'bi-people', 'description' => 'Gestion de la paie et des contrats', 'couleur' => 'text-pink-600'],
            ['nom' => 'Juridique', 'icone' => 'bi-bank2', 'description' => 'Assistance légale et statuts', 'couleur' => 'text-purple-600'],
            ['nom' => 'Logiciel Comptabilité', 'icone' => 'bi-calculator', 'description' => 'Abonnements et paramétrages logiciels', 'couleur' => 'text-orange-600'],
        ];

        $categories = [];
        $ordre = 1;
        foreach ($categoriesData as $cat) {
            $categories[$cat['nom']] = CatalogueCategory::create([
                'nom' => $cat['nom'],
                'icone' => $cat['icone'],
                'description' => $cat['description'],
                'couleur' => $cat['couleur'],
                'ordre' => $ordre++,
                'actif' => true,
            ]);
        }

        // 2. Création de quelques prestations de test pour ces catégories
        $servicesData = [
            ['category' => 'Administration', 'nom' => 'Domiciliation d\'entreprise', 'desc' => 'Adresse légale et gestion de votre courrier.'],
            ['category' => 'Consultation', 'nom' => 'Audit Financier', 'desc' => 'Analyse complète de la santé financière de votre entreprise.'],
            ['category' => 'Fiscal', 'nom' => 'Déclaration Fiscale Annuelle', 'desc' => 'Prise en charge de vos liasses fiscales (SYSCOHADA).'],
            ['category' => 'IT', 'nom' => 'Maintenance Parc Informatique', 'desc' => 'Support technique et sécurité réseau pour votre cabinet.'],
            ['category' => 'Social & Paie', 'nom' => 'Gestion de Paie Externalisée', 'desc' => 'Édition des bulletins de salaire et déclarations CNSS.'],
            ['category' => 'Juridique', 'nom' => 'Création de Société', 'desc' => 'Rédaction des statuts, dépôt au greffe et immatriculation.'],
            ['category' => 'Logiciel Comptabilité', 'nom' => 'Déploiement ERP GEL', 'desc' => 'Installation, configuration et formation sur l\'ERP GEL Cabinet.'],
        ];

        foreach ($servicesData as $i => $s) {
            CatalogueService::create([
                'category_id' => $categories[$s['category']]->id,
                'nom' => $s['nom'],
                'description' => $s['desc'],
                'inclus_json' => ['Assistance dédiée', 'Rapport mensuel'],
                'tarif_type' => 'devis',
                'ordre_affichage' => $i + 1,
                'actif' => true,
            ]);
        }
    }
}
