<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatalogueCategory;
use App\Models\CatalogueService;
use App\Models\CatalogueOrder;
use App\Models\CatalogueOrderMessage;
use App\Models\User;

class CatalogueDemoSeeder extends Seeder
{
    public function run()
    {
        // 1. Création des catégories
        $catCreation = CatalogueCategory::create([
            'nom' => 'Création d\'entreprise',
            'icone' => '🏢',
            'description' => 'Toutes les démarches pour immatriculer votre société.',
            'couleur' => 'text-blue-600',
            'ordre' => 1,
            'actif' => true,
        ]);

        $catGestion = CatalogueCategory::create([
            'nom' => 'Gestion & Conformité',
            'icone' => '📊',
            'description' => 'Assistance comptable, fiscale et juridique courante.',
            'couleur' => 'text-green-600',
            'ordre' => 2,
            'actif' => true,
        ]);

        $catModification = CatalogueCategory::create([
            'nom' => 'Modification statuts',
            'icone' => '📝',
            'description' => 'Changement de gérant, d\'adresse, etc.',
            'couleur' => 'text-purple-600',
            'ordre' => 3,
            'actif' => true,
        ]);

        // 2. Création des services
        $s1 = CatalogueService::create([
            'category_id' => $catCreation->id,
            'nom' => 'Création de SARL',
            'description' => 'Immatriculation complète de votre SARL incluant la rédaction des statuts.',
            'inclus_json' => ['Rédaction des statuts', 'Annonce légale', 'Dépôt au greffe', 'Obtention du KBIS'],
            'documents_requis_json' => ['Pièce d\'identité du gérant', 'Justificatif de domicile (siège social)'],
            'champs_formulaire_json' => [
                ['name' => 'nom_societe', 'label' => 'Nom de la future société', 'type' => 'text', 'required' => true],
                ['name' => 'activite_principale', 'label' => 'Activité principale', 'type' => 'textarea', 'required' => true],
            ],
            'tarif_type' => 'fixe',
            'tarif_fcfa' => 150000,
            'delai_jours' => '72 heures',
            'ordre_affichage' => 1,
            'actif' => true,
        ]);

        $s2 = CatalogueService::create([
            'category_id' => $catGestion->id,
            'nom' => 'Déclaration Fiscale Annuelle',
            'description' => 'Préparation et dépôt de vos liasses fiscales et bilans de fin d\'année.',
            'inclus_json' => ['Bilan comptable', 'Déclaration de résultats', 'Transmission aux impôts'],
            'documents_requis_json' => ['Grand livre comptable', 'Balance générale'],
            'tarif_type' => 'devis',
            'delai_jours' => '15 jours',
            'ordre_affichage' => 1,
            'actif' => true,
        ]);

        $s3 = CatalogueService::create([
            'category_id' => $catModification->id,
            'nom' => 'Transfert de Siège Social',
            'description' => 'Déménagement de l\'entreprise dans un nouveau local.',
            'inclus_json' => ['PV d\'Assemblée Générale', 'Modification des statuts', 'Annonce légale', 'Dépôt greffe'],
            'champs_formulaire_json' => [
                ['name' => 'ancienne_adresse', 'label' => 'Ancienne adresse', 'type' => 'text', 'required' => true],
                ['name' => 'nouvelle_adresse', 'label' => 'Nouvelle adresse', 'type' => 'text', 'required' => true],
            ],
            'tarif_type' => 'fixe',
            'tarif_fcfa' => 80000,
            'delai_jours' => '5 jours ouvrés',
            'ordre_affichage' => 1,
            'actif' => true,
        ]);

        // 3. Commandes de test (si des users existent)
        $client = User::where('email', 'admin@entreprise-demo.com')->first();
        $admin = User::where('email', 'superadmin@gelsabinet.com')->first();

        if ($client && $admin) {
            $order1 = CatalogueOrder::create([
                'reference' => 'GS-2026-DEMO1',
                'client_id' => $client->id,
                'service_id' => $s1->id,
                'categorie_id' => $s1->category_id,
                'responsable_id' => $admin->id,
                'statut' => 'En cours',
                'date_commande' => now()->subDays(2),
                'delai_estime' => '72 heures',
                'montant_estime_fcfa' => 150000,
                'form_data' => [
                    'nom_societe' => 'Demo SARL',
                    'activite_principale' => 'Vente de logiciels en ligne',
                ],
                'notes_internes' => 'Dossier prioritaire, client important.',
            ]);

            CatalogueOrderMessage::create([
                'commande_id' => $order1->id,
                'expediteur_id' => $admin->id,
                'type' => 'equipe',
                'contenu' => 'Bonjour, nous avons bien reçu votre demande de création. Pouvez-vous nous fournir la pièce d\'identité du gérant dans l\'onglet Documents ?',
            ]);

            CatalogueOrderMessage::create([
                'commande_id' => $order1->id,
                'expediteur_id' => $client->id,
                'type' => 'client',
                'contenu' => 'Bonjour, je vous envoie ça tout de suite.',
            ]);
            
            $order2 = CatalogueOrder::create([
                'reference' => 'GS-2026-DEMO2',
                'client_id' => $client->id,
                'service_id' => $s3->id,
                'categorie_id' => $s3->category_id,
                'statut' => 'Nouvelle Demande',
                'date_commande' => now()->subHours(5),
                'delai_estime' => '5 jours',
                'montant_estime_fcfa' => 80000,
                'form_data' => [
                    'ancienne_adresse' => '10 rue ancienne',
                    'nouvelle_adresse' => '20 rue nouvelle',
                ],
            ]);
        }
    }
}
