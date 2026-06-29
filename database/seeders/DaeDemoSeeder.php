<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\License;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\Dae\{
    DaeCourrier,
    DaeEmail,
    DaeAgendaEvent,
    DaeContrat,
    DaeDocument,
    DaePersonnelDossier,
    DaeConformite,
    DaeRapport,
    DaeTache,
    DaeModeleCourrier,
};
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DaeDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Clients de démonstration ──────────────────────────────────
        $client1 = Client::firstOrCreate(
            ['email' => 'contact@techinnov.bj'],
            [
                'company_name' => 'TechInno SARL',
                'legal_form' => 'SARL',
                'address' => '15 Boulevard du Commerce',
                'city' => 'Cotonou',
                'country' => 'Bénin',
                'phone' => '+229 01 23 45 67',
                'status' => 'actif',
                'contract_type' => 'monthly',
                'contract_start' => Carbon::now()->subMonths(8),
                'created_by' => 1,
            ]
        );

        $client2 = Client::firstOrCreate(
            ['email' => 'direction@africa-logistics.com'],
            [
                'company_name' => 'Africa Logistics SA',
                'legal_form' => 'SA',
                'address' => '42 Rue des Entreprises',
                'city' => 'Cotonou',
                'country' => 'Bénin',
                'phone' => '+229 97 89 01 23',
                'status' => 'actif',
                'contract_type' => 'annual',
                'contract_start' => Carbon::now()->subMonths(14),
                'created_by' => 1,
            ]
        );

        // Définir le contexte RLS PostgreSQL pour ce client
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SET app.client_id = '{$client1->id}'");
        }
        try {

        // ─── 2. Service DAE ──────────────────────────────────────────────
        $daeService = Service::firstOrCreate(
            ['slug' => 'dae'],
            [
                'name' => 'Direction Administrative Externalisée',
                'description' => 'Externalisation complète de la gestion administrative : courriers, agenda, contrats, RH, conformité.',
                'icon' => 'bi-file-text',
                'color' => '#FF7900',
                'category' => 'gestion',
                'is_active' => true,
            ]
        );

        // ─── 3. Licences DAE pour les clients ────────────────────────────
        License::firstOrCreate(
            ['client_id' => $client1->id, 'service_id' => $daeService->id],
            [
                'license_key' => 'DAE-DEMO-001',
                'duration_months' => 12,
                'start_date' => Carbon::now()->subMonths(6),
                'end_date' => Carbon::now()->addMonths(6),
                'price' => 150000,
                'status' => 'active',
            ]
        );

        // Basculer le contexte RLS pour client2
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SET app.client_id = '{$client2->id}'");
        }

        License::firstOrCreate(
            ['client_id' => $client2->id, 'service_id' => $daeService->id],
            [
                'license_key' => 'DAE-DEMO-002',
                'duration_months' => 12,
                'start_date' => Carbon::now()->subMonths(10),
                'end_date' => Carbon::now()->addMonths(2),
                'price' => 250000,
                'status' => 'active',
            ]
        );

        // ─── 4. Rôle secrétaire ──────────────────────────────────────────
        $secretaireRole = Role::firstOrCreate(
            ['slug' => 'secretaire'],
            [
                'name' => 'Secrétaire',
                'description' => 'Accès au module DAE (courriers, emails, agenda, contrats, documents, tâches).',
                'level' => 10,
                'is_system' => true,
            ]
        );

        // ─── 5. Utilisateur secrétaire de démonstration ───────────────────
        $secretaire = User::firstOrCreate(
            ['email' => 'secretaire@monprojet.com'],
            [
                'name' => 'Sophie Koffi',
                'password' => Hash::make('Secretaire2025!'),
                'role' => 'secretaire',
                'role_id' => $secretaireRole->id,
                'role_secretaire' => true,
                'clients_assignes' => [$client1->id, $client2->id],
                'phone' => '+229 61 23 45 67',
                'is_active' => true,
                'email_verified_at' => now(),
                'fonction' => 'Secrétaire Direction Administrative',
            ]
        );

        // ─── 6. Données de démonstration DAE ─────────────────────────────

        // 6a. Courriers
        $courriers = [
            [
                'client_id' => $client1->id,
                'reference' => 'COU-2025-001',
                'expediteur' => 'Direction Générale Impôts',
                'destinataire' => 'TechInno SARL',
                'type' => 'entrant',
                'mode' => 'postal',
                'objet' => 'Avis de taxation exercice 2024',
                'contenu' => 'Notification du montant d\'impôt dû pour l\'exercice 2024.',
                'urgence' => 'urgent',
                'statut' => 'recu',
                'date_reception' => Carbon::now()->subDays(5),
                'tags' => ['fiscal', 'impôts', '2024'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'reference' => 'COU-2025-002',
                'expediteur' => 'TechInno SARL',
                'destinataire' => 'Client ABC',
                'type' => 'sortant',
                'mode' => 'email',
                'objet' => 'Relance de paiement facture FACT-2025-042',
                'contenu' => 'Nous vous prions de bien vouloir procéder au règlement de la facture.',
                'urgence' => 'normal',
                'statut' => 'envoye',
                'date_envoi' => Carbon::now()->subDays(3),
                'tags' => ['facture', 'relance'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'reference' => 'COU-2025-003',
                'expediteur' => 'Direction des Douanes',
                'destinataire' => 'Africa Logistics SA',
                'type' => 'entrant',
                'mode' => 'postal',
                'objet' => 'Notification de contrôle douanier',
                'contenu' => 'Un contrôle de vos documents d\'importation est programmé.',
                'urgence' => 'tre_urgent',
                'statut' => 'traite',
                'date_reception' => Carbon::now()->subDays(10),
                'date_traitement' => Carbon::now()->subDays(8),
                'reponse' => 'Documents transmis au service des douanes.',
                'traite_par' => $secretaire->id,
                'tags' => ['douane', 'contrôle'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'reference' => 'COU-2025-004',
                'type' => 'interne',
                'mode' => 'remise_main',
                'objet' => 'Note de service : Nouveau process RH',
                'contenu' => 'À compter du 1er juillet, toutes les demandes de congés passent par le portail.',
                'urgence' => 'normal',
                'statut' => 'archive',
                'date_reception' => Carbon::now()->subMonths(2),
                'tags' => ['interne', 'rh'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'reference' => 'COU-2025-005',
                'expediteur' => 'Client XYZ',
                'type' => 'entrant',
                'mode' => 'postal',
                'objet' => 'Réclamation sur commande LIV-2025-089',
                'contenu' => 'Le client signale une anomalie sur la livraison reçue.',
                'urgence' => 'urgent',
                'statut' => 'recu',
                'tags' => ['réclamation', 'livraison'],
                'created_by' => $secretaire->id,
            ],
        ];

        foreach ($courriers as $data) {
            DaeCourrier::create($data);
        }

        // 6b. Emails
        $emails = [
            [
                'client_id' => $client1->id,
                'reference_message' => 'MSG-001',
                'from_address' => 'contact@techinnov.bj',
                'to_addresses' => ['client@exemple.com'],
                'objet' => 'Proposition commerciale accompagnement DAE',
                'corps_html' => '<p>Suite à notre échange, voici notre proposition pour l\'accompagnement DAE.</p>',
                'statut' => 'envoye',
                'date_envoi' => Carbon::now()->subDays(7),
                'dossier' => 'Propositions',
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'reference_message' => 'MSG-002',
                'from_address' => 'expert-comptable@cabinet.bj',
                'to_addresses' => ['contact@techinnov.bj'],
                'objet' => 'Relevé de comptes annuel 2024',
                'corps_html' => '<p>Veuillez trouver ci-joint le relevé de comptes annuel.</p>',
                'statut' => 'recu',
                'date_reception' => Carbon::now()->subDays(2),
                'dossier' => 'Comptabilité',
                'pieces_jointes' => ['releve_2024.pdf'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'reference_message' => 'MSG-003',
                'from_address' => 'direction@africa-logistics.com',
                'to_addresses' => ['fournisseur@transport.bj'],
                'cc_addresses' => ['compta@africa-logistics.com'],
                'objet' => 'Bon de commande BC-2025-156',
                'corps_html' => '<p>Veuillez préparer la commande selon les termes convenus.</p>',
                'statut' => 'brouillon',
                'dossier' => 'Commandes',
                'created_by' => $secretaire->id,
            ],
        ];

        foreach ($emails as $data) {
            DaeEmail::create($data);
        }

        // 6c. Événements agenda
        $events = [
            [
                'client_id' => $client1->id,
                'title' => 'Réunion bilan trimestriel',
                'type' => 'reunion',
                'start_at' => Carbon::now()->addDays(3)->setHour(9)->setMinute(0),
                'end_at' => Carbon::now()->addDays(3)->setHour(11)->setMinute(0),
                'location' => 'Salle de conférence - Étage 3',
                'couleur' => '#FF7900',
                'statut' => 'confirme',
                'participants' => ['Sophie Koffi', 'Directeur Général', 'Comptable'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'title' => 'Appel fournisseur IT',
                'type' => 'appel',
                'start_at' => Carbon::now()->addDay()->setHour(14)->setMinute(0),
                'end_at' => Carbon::now()->addDay()->setHour(14)->setMinute(30),
                'couleur' => '#10B981',
                'statut' => 'planifie',
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'title' => 'Échéance déclaration fiscale',
                'type' => 'echeance',
                'start_at' => Carbon::now()->addDays(15)->setHour(8)->setMinute(0),
                'all_day' => true,
                'couleur' => '#EF4444',
                'statut' => 'planifie',
                'rappel' => ['type' => 'email', 'delai' => 2],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'title' => 'Réunion stratégique conseil',
                'type' => 'rdv',
                'start_at' => Carbon::now()->addWeek()->setHour(10)->setMinute(0),
                'end_at' => Carbon::now()->addWeek()->setHour(12)->setMinute(0),
                'location' => 'Bureau direction',
                'couleur' => '#3B82F6',
                'statut' => 'confirme',
                'participants' => ['Conseil d\'administration'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'title' => 'Point hebdomadaire DAE',
                'type' => 'reunion',
                'start_at' => Carbon::now()->setHour(11)->setMinute(0),
                'end_at' => Carbon::now()->setHour(11)->setMinute(30),
                'couleur' => '#FF7900',
                'statut' => 'planifie',
                'recurrence' => 'hebdomadaire',
                'participants' => ['Sophie Koffi', 'Équipe DAE'],
                'created_by' => $secretaire->id,
            ],
        ];

        foreach ($events as $data) {
            DaeAgendaEvent::create($data);
        }

        // 6d. Contrats
        $contrats = [
            [
                'client_id' => $client1->id,
                'reference' => 'CT-2025-001',
                'titre' => 'Contrat de maintenance informatique',
                'type_contrat' => 'Prestation de service',
                'partie_adverse' => 'IT Services Bénin',
                'date_signature' => Carbon::now()->subMonths(3),
                'date_debut' => Carbon::now()->subMonths(3),
                'date_fin' => Carbon::now()->addMonths(9),
                'duree_mois' => 12,
                'montant' => 2400000,
                'devise' => 'XOF',
                'objet' => 'Maintenance préventive et curative du parc informatique.',
                'statut' => 'actif',
                'renouvelable' => true,
                'date_renouvellement' => Carbon::now()->addMonths(8),
                'tags' => ['informatique', 'maintenance'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'reference' => 'CT-2025-002',
                'titre' => 'Contrat de location entrepôt',
                'type_contrat' => 'Location',
                'partie_adverse' => 'Immobilier Logistics SA',
                'date_signature' => Carbon::now()->subMonths(2),
                'date_debut' => Carbon::now()->subMonths(2),
                'date_fin' => Carbon::now()->addMonths(10),
                'duree_mois' => 12,
                'montant' => 6000000,
                'devise' => 'XOF',
                'objet' => 'Location entrepôt zone industrielle PK10.',
                'statut' => 'actif',
                'renouvelable' => true,
                'date_renouvellement' => Carbon::now()->addMonths(9),
                'tags' => ['entrepôt', 'logistique'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'reference' => 'CT-2024-015',
                'titre' => 'Abonnement logiciel compta',
                'type_contrat' => 'Abonnement SaaS',
                'partie_adverse' => 'GEL Cabinet',
                'date_signature' => Carbon::now()->subMonths(14),
                'date_debut' => Carbon::now()->subMonths(14),
                'date_fin' => Carbon::now()->subMonths(2),
                'duree_mois' => 12,
                'montant' => 1200000,
                'devise' => 'XOF',
                'objet' => 'Abonnement à la solution de comptabilité GEL.',
                'statut' => 'expire',
                'tags' => ['comptabilité', 'saas'],
                'created_by' => $secretaire->id,
            ],
        ];

        foreach ($contrats as $data) {
            DaeContrat::create($data);
        }

        // 6e. Documents
        $docs = [
            [
                'client_id' => $client1->id,
                'reference' => 'DOC-2025-001',
                'titre' => 'Statuts mis à jour',
                'type_document' => 'Juridique',
                'categorie' => 'Statuts',
                'fichier' => 'statuts_techinno_2025.pdf',
                'taille_fichier' => 245000,
                'mime_type' => 'application/pdf',
                'version' => 3,
                'statut' => 'final',
                'valide' => true,
                'signe' => true,
                'mots_cles' => ['statuts', 'juridique', 'modification'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'reference' => 'DOC-2025-002',
                'titre' => 'Rapport d\'activité 2024',
                'type_document' => 'Rapport',
                'categorie' => 'Gestion',
                'fichier' => 'rapport_activite_2024.pdf',
                'taille_fichier' => 1800000,
                'mime_type' => 'application/pdf',
                'version' => 2,
                'statut' => 'final',
                'valide' => true,
                'mots_cles' => ['rapport', 'activité', '2024'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'reference' => 'DOC-2025-003',
                'titre' => 'Certificat de conformité douane',
                'type_document' => 'Certificat',
                'categorie' => 'Conformité',
                'fichier' => 'certificat_douane_2025.pdf',
                'taille_fichier' => 320000,
                'mime_type' => 'application/pdf',
                'version' => 1,
                'statut' => 'final',
                'date_expiration' => Carbon::now()->addMonths(6),
                'alerte_expiration' => true,
                'valide' => true,
                'mots_cles' => ['douane', 'certificat', 'conformité'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'reference' => 'DOC-2025-004',
                'titre' => 'Bilan financier 2024',
                'type_document' => 'Financier',
                'categorie' => 'Comptabilité',
                'fichier' => 'bilan_2024.xlsx',
                'taille_fichier' => 520000,
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'version' => 1,
                'statut' => 'brouillon',
                'valide' => false,
                'mots_cles' => ['bilan', 'financier', '2024'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'reference' => 'DOC-2025-005',
                'titre' => 'Contrat type de vente',
                'type_document' => 'Modèle',
                'categorie' => 'Contrats',
                'fichier' => 'contrat_type_vente.docx',
                'taille_fichier' => 45000,
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'version' => 1,
                'statut' => 'final',
                'valide' => true,
                'mots_cles' => ['modèle', 'contrat', 'vente'],
                'created_by' => $secretaire->id,
            ],
        ];

        foreach ($docs as $data) {
            DaeDocument::create($data);
        }

        // 6f. Personnel
        $personnel = [
            [
                'client_id' => $client1->id,
                'nom' => 'ADAM',
                'prenom' => 'Karim',
                'email' => 'karim.adam@techinnov.bj',
                'telephone' => '+229 61 11 22 33',
                'poste' => 'Développeur Full Stack',
                'departement' => 'Informatique',
                'date_embauche' => Carbon::parse('2022-03-15'),
                'statut' => 'actif',
                'type_contrat' => 'CDI',
                'salaire' => 450000,
                'numero_securite_sociale' => 'SS-2022-001',
                'competences' => ['PHP', 'Laravel', 'Vue.js', 'PostgreSQL'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'nom' => 'SOSSOU',
                'prenom' => 'Bénédicte',
                'email' => 'b.sossou@techinnov.bj',
                'poste' => 'Comptable',
                'departement' => 'Finance',
                'date_embauche' => Carbon::parse('2023-09-01'),
                'statut' => 'actif',
                'type_contrat' => 'CDD',
                'salaire' => 350000,
                'competences' => ['Comptabilité', 'SYSCOHADA', 'Excel'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'nom' => 'HOUNKPATIN',
                'prenom' => 'Michaël',
                'email' => 'm.hounkpatin@africa-logistics.com',
                'poste' => 'Responsable Logistique',
                'departement' => 'Opérations',
                'date_embauche' => Carbon::parse('2021-01-10'),
                'statut' => 'actif',
                'type_contrat' => 'CDI',
                'salaire' => 650000,
                'numero_securite_sociale' => 'SS-2021-003',
                'competences' => ['Logistique', 'Supply Chain', 'Management'],
                'created_by' => $secretaire->id,
            ],
        ];

        foreach ($personnel as $data) {
            DaePersonnelDossier::create($data);
        }

        // 6g. Conformité
        $conformites = [
            [
                'client_id' => $client1->id,
                'type_conformite' => 'Fiscal',
                'titre' => 'Déclaration IRPP 2024',
                'description' => 'Déclaration annuelle des revenus.',
                'exigence_reglementaire' => 'Code Général des Impôts Art. 150',
                'autorite_competente' => 'Direction Générale des Impôts',
                'date_soumission' => Carbon::now()->subMonths(2),
                'date_validation' => Carbon::now()->subMonth(),
                'statut' => 'valide',
                'verified_by' => $secretaire->id,
                'verified_at' => Carbon::now()->subMonth(),
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'type_conformite' => 'Douanier',
                'titre' => 'Licence d\'importation 2025',
                'description' => 'Renouvellement de la licence d\'importation de marchandises.',
                'exigence_reglementaire' => 'Code des Douanes Art. 45',
                'autorite_competente' => 'Direction des Douanes',
                'date_expiration' => Carbon::now()->addMonths(2),
                'statut' => 'en_cours',
                'created_by' => $secretaire->id,
            ],
        ];

        foreach ($conformites as $data) {
            DaeConformite::create($data);
        }

        // 6h. Rapports
        $rapports = [
            [
                'client_id' => $client1->id,
                'titre' => 'Rapport d\'activité DAE - Mai 2025',
                'type_rapport' => 'Mensuel',
                'description' => 'Synthèse des activités du secrétariat pour le mois de mai.',
                'periode_debut' => Carbon::parse('2025-05-01'),
                'periode_fin' => Carbon::parse('2025-05-31'),
                'contenu' => [
                    'courriers_traites' => 12,
                    'emails_envoyes' => 45,
                    'contrats_suivis' => 5,
                    'taches_realisees' => 28,
                ],
                'metriques' => [
                    'taux_traitement' => '92%',
                    'délai_moyen' => '2.3 jours',
                ],
                'statut' => 'finalise',
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'titre' => 'Bilan conformité réglementaire',
                'type_rapport' => 'Trimestriel',
                'description' => 'État des lieux de la conformité réglementaire.',
                'periode_debut' => Carbon::parse('2025-01-01'),
                'periode_fin' => Carbon::parse('2025-03-31'),
                'contenu' => [
                    'conformites_validees' => 3,
                    'en_cours' => 2,
                    'non_conformes' => 0,
                ],
                'statut' => 'genere',
                'created_by' => $secretaire->id,
            ],
        ];

        foreach ($rapports as $data) {
            DaeRapport::create($data);
        }

        // 6i. Tâches
        $taches = [
            [
                'client_id' => $client1->id,
                'titre' => 'Classer les courriers fiscaux',
                'description' => 'Archiver les courriers reçus de la DGI dans le dossier fiscal.',
                'priorite' => 'haute',
                'statut' => 'en_cours',
                'echeance' => Carbon::now()->addDays(2),
                'assigned_to' => $secretaire->id,
                'tags' => ['courrier', 'fiscal'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'titre' => 'Préparer la réunion bilan',
                'description' => 'Rassembler les documents pour la réunion bilan trimestriel.',
                'priorite' => 'haute',
                'statut' => 'a_faire',
                'echeance' => Carbon::now()->addDays(3),
                'assigned_to' => $secretaire->id,
                'tags' => ['réunion', 'préparation'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'titre' => 'Vérifier les contrats arrivant à expiration',
                'description' => 'Faire le point sur les contrats qui expirent dans les 30 jours.',
                'priorite' => 'moyenne',
                'statut' => 'a_faire',
                'echeance' => Carbon::now()->addWeek(),
                'tags' => ['contrats', 'échéance'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'titre' => 'Renouveler licence d\'importation',
                'description' => 'Préparer les documents pour le renouvellement de la licence.',
                'priorite' => 'critique',
                'statut' => 'en_cours',
                'echeance' => Carbon::now()->addDays(10),
                'assigned_to' => $secretaire->id,
                'tags' => ['douane', 'licence'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'titre' => 'Répondre à la réclamation client',
                'description' => 'Traiter la réclamation sur la livraison LIV-2025-089.',
                'priorite' => 'critique',
                'statut' => 'a_faire',
                'echeance' => Carbon::now()->addDays(3),
                'assigned_to' => $secretaire->id,
                'tags' => ['réclamation', 'client'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'titre' => 'Mettre à jour le registre du personnel',
                'description' => 'Ajouter les nouveaux entrants et sortants.',
                'priorite' => 'basse',
                'statut' => 'terminee',
                'echeance' => Carbon::now()->subDays(2),
                'completed_at' => Carbon::now()->subDays(3),
                'assigned_to' => $secretaire->id,
                'tags' => ['rh', 'registre'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client2->id,
                'titre' => 'Archiver les documents douane 2024',
                'description' => 'Numériser et classer tous les documents douaniers de l\'année 2024.',
                'priorite' => 'moyenne',
                'statut' => 'en_revision',
                'echeance' => Carbon::now()->addWeek(),
                'assigned_to' => $secretaire->id,
                'tags' => ['archive', 'douane'],
                'created_by' => $secretaire->id,
            ],
            [
                'client_id' => $client1->id,
                'titre' => 'Préparer déclaration fiscale mensuelle',
                'description' => 'Rassembler les pièces pour la déclaration du mois en cours.',
                'priorite' => 'haute',
                'statut' => 'a_faire',
                'echeance' => Carbon::now()->addDays(15),
                'tags' => ['fiscal', 'déclaration'],
                'created_by' => $secretaire->id,
            ],
            // Sous-tâche
            [
                'client_id' => $client1->id,
                'titre' => 'Collecter les factures fournisseurs',
                'description' => 'Sous-tâche : rassembler les factures pour la déclaration.',
                'priorite' => 'moyenne',
                'statut' => 'a_faire',
                'echeance' => Carbon::now()->addDays(10),
                'parent_id' => 8,
                'tags' => ['factures', 'fournisseurs'],
                'created_by' => $secretaire->id,
            ],
        ];

        foreach ($taches as $data) {
            DaeTache::create($data);
        }

        // 6j. Modèles de courriers
        $modeles = [
            [
                'nom' => 'Relance de paiement',
                'type' => 'recouvrement',
                'objet_defaut' => 'Relance de paiement n°{{numero}}',
                'corps' => "Objet : Relance de paiement\n\nMadame, Monsieur,\n\nNous vous rappelons que la facture n°{{facture}} d'un montant de {{montant}} arrive à échéance le {{date_echeance}}.\n\nNous vous prions de bien vouloir procéder au règlement dans les plus brefs délais.\n\nCordialement,\n{{signature}}",
                'variables' => ['numero', 'facture', 'montant', 'date_echeance', 'signature'],
                'categorie' => 'Recouvrement',
                'created_by' => $secretaire->id,
            ],
            [
                'nom' => 'Accusé de réception',
                'type' => 'administratif',
                'objet_defaut' => 'Accusé de réception de votre courrier du {{date}}',
                'corps' => "Objet : Accusé de réception\n\nMadame, Monsieur,\n\nNous accusons réception de votre courrier reçu en date du {{date}} et référencé {{reference}}.\n\nVotre demande sera traitée dans les meilleurs délais.\n\nCordialement,\n{{signature}}",
                'variables' => ['date', 'reference', 'signature'],
                'categorie' => 'Administratif',
                'created_by' => $secretaire->id,
            ],
            [
                'nom' => 'Certificat de travail',
                'type' => 'rh',
                'objet_defaut' => 'Certificat de travail - {{employe}}',
                'corps' => "CERTIFICAT DE TRAVAIL\n\nJe soussigné, {{employeur}}, certifie que {{employe}} a été employé dans notre entreprise du {{date_debut}} au {{date_fin}} en qualité de {{poste}}.\n\nFait pour valoir ce que de droit.\n\n{{signature}}",
                'variables' => ['employe', 'employeur', 'date_debut', 'date_fin', 'poste', 'signature'],
                'categorie' => 'Ressources Humaines',
                'created_by' => $secretaire->id,
            ],
        ];

        foreach ($modeles as $data) {
            DaeModeleCourrier::create($data);
        }
    } finally {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SET app.client_id = '0'");
        }
    }
}
}
