<?php

namespace Database\Seeders;

use App\Models\Legal\LegalAssembly;
use App\Models\Legal\LegalCompanyInfo;
use App\Models\Legal\LegalCompliance;
use App\Models\Legal\LegalContract;
use App\Models\Legal\LegalContractSignature;
use App\Models\Legal\LegalLitigation;
use App\Models\Legal\LegalActsLibrary;
use App\Models\Legal\LegalRegistre;
use App\Models\Legal\LegalDossier;
use App\Models\Legal\LegalVeille;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LegalDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Trouver ou créer un client démo
        $client = User::where('email', 'jean@techinnov.bj')->first();
        if (!$client) {
            $client = User::where('client_id', '>', '0')->first();
        }
        $clientId = $client ? ($client->client_id ?: $client->id) : 1;

        // ââ€â‚¬ââ€â‚¬ââ€â‚¬ 1. Société ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬
        LegalCompanyInfo::updateOrCreate(
            ['client_id' => $clientId],
            [
                'raison_sociale' => 'TechInnov SARL',
                'forme_juridique' => 'SARL',
                'capital_social' => 10_000_000,
                'date_creation' => '2022-03-15',
                'numero_rccm' => 'RB/COT/22-A-12345',
                'ifu' => '3201234567890',
                'siege_social' => 'Cotonou, Lot 123, Avenue de la République',
                'objet_social' => 'Conseil en technologies, développement logiciel et transformation digitale',
                'duree_vie' => 99,
                'exercice_social' => '01/01 - 31/12',
                'gerant_nom' => 'Koffi',
                'gerant_prenom' => 'Jean',
                'gerant_nationalite' => 'Béninoise',
                'conseil_administration' => [
                    ['nom' => 'Koffi Jean', 'fonction' => 'Gérant', 'date_nomination' => '2022-03-15'],
                ],
                'associes' => [
                    ['nom' => 'Koffi Jean', 'parts' => 600, 'pourcentage' => 60],
                    ['nom' => 'ADEKAMBI A.', 'parts' => 400, 'pourcentage' => 40],
                ],
                'statuts_path' => null,
                'statuts_date' => '2022-03-15',
                'statuts_version' => 1,
            ]
        );

        // ââ€â‚¬ââ€â‚¬ââ€â‚¬ 2. Assemblée Générale ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬
        LegalAssembly::updateOrCreate(
            ['client_id' => $clientId, 'type' => 'AGO', 'annee' => 2025],
            [
                'date_convocation' => '2025-05-01',
                'date_tenue' => '2025-06-15',
                'lieu' => 'Siège social, Cotonou',
                'quorum_requis' => 75,
                'quorum_atteint' => 85,
                'ordre_du_jour' => [
                    'Approbation des comptes 2024',
                    'Affectation du résultat',
                    'Renouvellement du gérant',
                    'Questions diverses',
                ],
                'resolutions' => [
                    ['numero' => 'R1', 'intitule' => 'Approbation des comptes annuels', 'votes_pour' => 600, 'votes_contre' => 0, 'adoptee' => true],
                    ['numero' => 'R2', 'intitule' => 'Affectation du résultat en réserves', 'votes_pour' => 600, 'votes_contre' => 0, 'adoptee' => true],
                ],
                'participants' => [
                    ['nom' => 'Koffi Jean', 'pourcentage' => 60, 'presence' => true],
                    ['nom' => 'ADEKAMBI A.', 'pourcentage' => 25, 'presence' => true],
                ],
                'statut' => 'tenue',
                'pv_approuve' => true,
                'convocation_envoyee' => true,
                'created_by' => 1,
            ]
        );

        // ââ€â‚¬ââ€â‚¬ââ€â‚¬ 3. Contrats (5) ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬
        $contrats = [
            ['titre' => 'Maintenance applicative ERP', 'type' => 'prestation_service', 'partie_adverse' => 'SOFIBANK SA', 'montant' => 15_000_000, 'date_debut' => '2025-01-01', 'date_fin' => '2025-12-31', 'statut' => 'actif', 'objet' => 'Contrat de maintenance et support de l\'ERP bancaire'],
            ['titre' => 'Bail local professionnel', 'type' => 'bail_commercial', 'partie_adverse' => 'IMMO BÃƒâ€°NIN', 'montant' => 3_600_000, 'date_debut' => '2024-06-01', 'date_fin' => '2027-05-31', 'statut' => 'actif', 'objet' => 'Location des locaux du siège social'],
            ['titre' => 'Contrat de prestation Cloud', 'type' => 'prestation_service', 'partie_adverse' => 'ORANGE BÃƒâ€°NIN', 'montant' => 2_400_000, 'date_debut' => '2025-03-01', 'date_fin' => '2025-09-01', 'statut' => 'actif', 'objet' => 'Hébergement cloud et connectivité'],
            ['titre' => 'NDA Partenariat Stratégique', 'type' => 'confidentialite_nda', 'partie_adverse' => 'STARTUP LAB', 'montant' => null, 'date_debut' => '2025-02-01', 'date_fin' => null, 'statut' => 'signé', 'objet' => 'Accord de confidentialité préalable au partenariat R&D'],
            ['titre' => 'Contrat de travail - Chef Projet', 'type' => 'travail', 'partie_adverse' => 'Koffi Jean', 'montant' => 6_000_000, 'date_debut' => '2024-01-01', 'date_fin' => null, 'statut' => 'actif', 'objet' => 'Contrat de travail à durée indéterminée'],
        ];

        foreach ($contrats as $i => $data) {
            $data['client_id'] = $clientId;
            $data['reference'] = 'CTR-' . date('Y') . '-' . str_pad(($i + 1) * 111, 4, '0', STR_PAD_LEFT);
            $data['parties'] = [
                ['nom' => 'TechInnov SARL', 'role' => 'Prestataire', 'email' => 'contact@techinnov.bj'],
                ['nom' => $data['partie_adverse'], 'role' => 'Client', 'email' => 'contact@' . strtolower(str_replace(' ', '', $data['partie_adverse'])) . '.bj'],
            ];
            $data['devise'] = 'XOF';
            $data['renouvellement_auto'] = false;
            $data['droit_applicable'] = 'Droit béninois (OHADA)';
            $data['created_by'] = 1;
            unset($data['partie_adverse']);

            $contrat = LegalContract::updateOrCreate(
                ['client_id' => $clientId, 'reference' => $data['reference']],
                $data
            );

            // Ajouter une signature si signé ou actif
            if (in_array($contrat->statut, ['signé', 'actif'])) {
                LegalContractSignature::updateOrCreate(
                    ['contract_id' => $contrat->id, 'signataire_nom' => 'Koffi Jean'],
                    [
                        'signataire_email' => 'jean@techinnov.bj',
                        'signataire_role' => 'Gérant',
                        'statut' => 'signé',
                        'date_signature' => now()->subDays(30),
                    ]
                );
            }
        }

        // ââ€â‚¬ââ€â‚¬ââ€â‚¬ 4. Contentieux (2) ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬
        LegalLitigation::updateOrCreate(
            ['client_id' => $clientId, 'reference' => 'LIT-2025-001'],
            [
                'titre' => 'Paiement impayé - Projet Site Web',
                'type' => 'commercial',
                'nature' => 'demandeur',
                'partie_adverse' => 'Agence WebPlus',
                'statut' => 'instruction',
                'tribunal' => 'Tribunal de Commerce de Cotonou',
                'numero_dossier' => 'TC/2025/452',
                'date_saisine' => '2025-03-20',
                'prochaine_audience' => '2025-09-15',
                'montant_litige' => 4_500_000,
                'avocat_cabinet' => 'Me. BOKO Gaston',
                'partie_adverse_avocat' => 'Me. DOSSOU',
                'created_by' => 1,
            ]
        );

        LegalLitigation::updateOrCreate(
            ['client_id' => $clientId, 'reference' => 'LIT-2025-002'],
            [
                'titre' => 'Conflit sur clause de non-concurrence',
                'type' => 'social',
                'nature' => 'défendeur',
                'partie_adverse' => 'M. Sossa Innocent',
                'statut' => 'en_cours',
                'tribunal' => 'Tribunal du Travail',
                'numero_dossier' => 'TT/2025/89',
                'date_saisine' => '2025-06-01',
                'prochaine_audience' => '2025-10-10',
                'montant_litige' => 2_000_000,
                'created_by' => 1,
            ]
        );

        // ââ€â‚¬ââ€â‚¬ââ€â‚¬ 5. Conformité (7) ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬
        $obligations = [
            ['intitule' => 'Déclaration IFU annuelle', 'type' => 'déclaration_obligatoire', 'organisme' => 'DGI', 'periodicite' => 'annuel', 'date_echeance' => '2026-03-31', 'statut' => 'conforme'],
            ['intitule' => 'Déclaration TVA mensuelle', 'type' => 'déclaration_obligatoire', 'organisme' => 'DGI', 'periodicite' => 'mensuel', 'date_echeance' => '2026-07-15', 'statut' => 'en_cours'],
            ['intitule' => 'Cotisations INSS', 'type' => 'obligation_sociale', 'organisme' => 'INSS', 'periodicite' => 'trimestriel', 'date_echeance' => '2026-07-31', 'statut' => 'conforme'],
            ['intitule' => 'Dépôt comptes annuels (RCCM)', 'type' => 'déclaration_obligatoire', 'organisme' => 'RCCM/CCEI', 'periodicite' => 'annuel', 'date_echeance' => '2026-06-30', 'statut' => 'expiré'],
            ['intitule' => 'Déclaration annuelle de TVA', 'type' => 'déclaration_obligatoire', 'organisme' => 'DGI', 'periodicite' => 'annuel', 'date_echeance' => '2026-04-30', 'statut' => 'conforme'],
            ['intitule' => 'Déclaration IRPP employés', 'type' => 'obligation_sociale', 'organisme' => 'DGI', 'periodicite' => 'mensuel', 'date_echeance' => '2026-07-15', 'statut' => 'en_cours'],
            ['intitule' => 'Archives des registres sociaux', 'type' => 'déclaration_obligatoire', 'organisme' => 'Ministère du Travail', 'periodicite' => 'annuel', 'date_echeance' => '2026-12-31', 'statut' => 'conforme'],
        ];

        foreach ($obligations as $data) {
            $data['client_id'] = $clientId;
            $data['alerte_avant'] = 30;
            $data['created_by'] = 1;
            LegalCompliance::updateOrCreate(
                ['client_id' => $clientId, 'intitule' => $data['intitule']],
                $data
            );
        }

        // ââ€â‚¬ââ€â‚¬ââ€â‚¬ 6. Bibliothèque d'actes (5 modèles) ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬
        $modeles = [
            ['titre' => 'Statuts SARL', 'categorie' => 'Constitution', 'contenu' => "STATUTS DE {{ nom_societe }}\n\nArticle 1 ââ‚¬â€ Forme\nIl est formé une Société à Responsabilité Limitée régie par l'Acte Uniforme OHADA et les présents statuts.\n\nArticle 2 ââ‚¬â€ Objet\n{{ objet_social }}\n\nArticle 3 ââ‚¬â€ Siège\n{{ siege_social }}\n\nArticle 4 ââ‚¬â€ Capital\nLe capital social est fixé à {{ capital }} FCFA, divisé en {{ parts }} parts sociales de {{ valeur_nominale }} FCFA.\n\nFait à {{ lieu }}, le {{ date }}\nLes Associés", 'variables' => json_encode(['nom_societe', 'objet_social', 'siege_social', 'capital', 'parts', 'valeur_nominale', 'lieu', 'date']), 'is_validated' => true],
            ['titre' => 'PV d\'Assemblée Générale Ordinaire', 'categorie' => 'Assemblées', 'contenu' => "PROCÃƒË†S-VERBAL D'ASSEMBLÃƒâ€°E GÃƒâ€°NÃƒâ€°RALE ORDINAIRE\n\nL'an {{ annee }}, le {{ date }}, au siège social sis {{ siege_social }},\n\nOrdre du jour :\n{{ ordre_du_jour }}\n\nRésolutions :\n{{ resolutions }}\n\nLe Gérant", 'variables' => json_encode(['annee', 'date', 'siege_social', 'ordre_du_jour', 'resolutions']), 'is_validated' => true],
            ['titre' => 'Contrat de Prestation de Services', 'categorie' => 'Contrats', 'contenu' => "CONTRAT DE PRESTATION DE SERVICES\n\nEntre {{ prestataire }} et {{ client }}\n\nObjet : {{ objet_mission }}\nMontant : {{ montant }} FCFA\nDurée : du {{ date_debut }} au {{ date_fin }}\n\nFait à {{ lieu }}, le {{ date }}", 'variables' => json_encode(['prestataire', 'client', 'objet_mission', 'montant', 'date_debut', 'date_fin', 'lieu', 'date']), 'is_validated' => true],
            ['titre' => 'Convention de Compte Courant d\'Associé', 'categorie' => 'Finance', 'contenu' => "CONVENTION DE COMPTE COURANT D'ASSOCIÃƒâ€°\n\nEntre la société {{ nom_societe }} et l'associé {{ nom_associe }}\n\nMontant : {{ montant }} FCFA\nTaux : {{ taux_interet }}%\nDurée : {{ duree }}\n\nFait à {{ lieu }}, le {{ date }}", 'variables' => json_encode(['nom_societe', 'nom_associe', 'montant', 'taux_interet', 'duree', 'lieu', 'date']), 'is_validated' => false],
            ['titre' => 'Lettre de Mission Audit', 'categorie' => 'Audit', 'contenu' => "LETTRE DE MISSION\n\nCabinet : {{ cabinet }}\nClient : {{ client }}\nObjet : Audit des comptes de l'exercice {{ exercice }}\nHonoraires : {{ honoraires }} FCFA\n\nLe Cabinet", 'variables' => json_encode(['cabinet', 'client', 'exercice', 'honoraires']), 'is_validated' => true],
        ];

        foreach ($modeles as $data) {
            $data['client_id'] = null; // Modèles globaux
            $data['created_by'] = 1;
            $data['version'] = 1;
            LegalActsLibrary::updateOrCreate(
                ['titre' => $data['titre']],
                $data
            );
        }

        // ââ€â‚¬ââ€â‚¬ââ€â‚¬ 7. Registres (3) ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬
        $registreTypes = ['registre_assemblee', 'registre_decisions', 'registre_commerce'];
        foreach ($registreTypes as $type) {
            LegalRegistre::updateOrCreate(
                ['client_id' => $clientId, 'type' => $type, 'annee' => 2025],
                [
                    'entrees' => [
                        ['date' => '2025-06-15', 'numero' => 1, 'objet' => 'Registre ' . $type . ' 2025', 'details' => 'Ouverture du registre'],
                    ],
                    'is_closed' => false,
                ]
            );
        }

        // ââ€â‚¬ââ€â‚¬ââ€â‚¬ 8. Dossiers (2) ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬
        LegalDossier::updateOrCreate(
            ['client_id' => $clientId, 'reference' => 'DOS-2025-001'],
            [
                'titre' => 'Restructuration juridique TechInnov',
                'type' => 'conseil',
                'statut' => 'en_cours',
                'priorite' => 'urgente',
                'description' => 'Accompagnement à la transformation en SAS avec augmentation de capital',
                'documents' => [],
                'assigned_to' => 1,
                'created_by' => 1,
            ]
        );

        LegalDossier::updateOrCreate(
            ['client_id' => $clientId, 'reference' => 'DOS-2025-002'],
            [
                'titre' => 'Contentieux WebPlus',
                'type' => 'contentieux',
                'statut' => 'ouvert',
                'priorite' => 'urgente',
                'description' => 'Recouvrement de créance impayée et rupture de contrat',
                'documents' => [],
                'assigned_to' => 1,
                'created_by' => 1,
            ]
        );

        // ââ€â‚¬ââ€â‚¬ââ€â‚¬ 9. Veille juridique (3) ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬ââ€â‚¬
        LegalVeille::updateOrCreate(
            ['client_id' => $clientId, 'titre' => 'Réforme OHADA 2025 - Nouveau Règlement sur les procédures'],
            [
                'description' => 'L\'OHADA a adopté un nouveau règlement simplifiant les procédures de recouvrement des créances.',
                'source' => 'Journal Officiel OHADA',
                'categorie' => 'règlementation',
                'date_publication' => '2025-11-01',
                'url' => null,
                'impact' => 'Moyen',
                'created_by' => 1,
            ]
        );

        LegalVeille::updateOrCreate(
            ['client_id' => $clientId, 'titre' => 'Code Général des Impôts 2026 - Nouvelles mesures fiscales'],
            [
                'description' => 'Réduction du taux de l\'IS de 30% à 28% pour les PME innovantes.',
                'source' => 'DGI Bénin',
                'categorie' => 'fiscal',
                'date_publication' => '2025-12-15',
                'url' => null,
                'impact' => 'Ãƒâ€°levé',
                'created_by' => 1,
            ]
        );

        LegalVeille::updateOrCreate(
            ['client_id' => $clientId, 'titre' => 'Loi sur la protection des données personnelles au Bénin'],
            [
                'description' => 'Nouvelle loi transposant le règlement CEDEAO sur la protection des données, entrée en vigueur le 1er janvier 2026.',
                'source' => 'Assemblée Nationale Bénin',
                'categorie' => 'règlementation',
                'date_publication' => '2025-11-01',
                'url' => null,
                'impact' => 'Ãƒâ€°levé',
                'created_by' => 1,
            ]
        );

        $this->command->info('âÅ“â€¦ Démo juridique créée : société, AG, 5 contrats, 2 contentieux, 7 obligations, 5 modèles d\'actes, veille, registres, dossiers.');
    }
}
