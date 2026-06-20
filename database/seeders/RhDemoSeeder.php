<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\License;
use App\Models\User;
use App\Models\Rh\RhEmployee;
use App\Models\Rh\RhContract;
use App\Models\Rh\RhLeaveRequest;
use App\Models\Rh\RhExpense;
use App\Models\Rh\RhPayroll;
use App\Models\Rh\RhAttendance;
use App\Models\Rh\RhTraining;
use App\Models\Rh\RhAlert;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RhDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Trouver les clients de demo
        $client1 = Client::where('email', 'contact@techinnov.bj')->first();
        $client2 = Client::where('email', 'direction@africa-logistics.com')->first();

        if (!$client1) {
            $client1 = Client::first();
        }
        if (!$client1) return;

        $client1Id = $client1->id;
        $client2Id = $client2?->id;
        if (!$client2Id) $client2Id = $client1Id;

        // --- Employés Client 1 (TechInnov) ---
        $emp1 = RhEmployee::updateOrCreate(
            ['matricule' => 'RH-001', 'client_id' => $client1Id],
            [
                'civilite' => 'M.', 'nom' => 'Kouassi', 'prenom' => 'Jean',
                'email' => 'j.kouassi@techinnov.bj', 'phone' => '+229 01 23 45 67',
                'poste' => 'Comptable Senior', 'departement' => 'Finance',
                'date_embauche' => '2022-03-15', 'type_contrat' => 'CDI',
                'salaire_base' => 350000, 'cnss_number' => 'CNSS-RH-001',
                'situation_matrimoniale' => 'marie', 'nombre_enfants' => 2,
                'status' => 'actif', 'created_by' => 1,
            ]
        );

        $emp2 = RhEmployee::updateOrCreate(
            ['matricule' => 'RH-002', 'client_id' => $client1Id],
            [
                'civilite' => 'Mme', 'nom' => 'Hounsou', 'prenom' => 'Marie',
                'email' => 'm.hounsou@techinnov.bj', 'phone' => '+229 01 23 45 68',
                'poste' => 'Juriste d\'Affaires', 'departement' => 'Juridique',
                'date_embauche' => '2023-01-10', 'type_contrat' => 'CDI',
                'salaire_base' => 400000, 'cnss_number' => 'CNSS-RH-002',
                'ifu_number' => 'IFU-RH-002',
                'situation_matrimoniale' => 'celibataire', 'nombre_enfants' => 0,
                'status' => 'actif', 'created_by' => 1,
            ]
        );

        $emp3 = RhEmployee::updateOrCreate(
            ['matricule' => 'RH-003', 'client_id' => $client1Id],
            [
                'civilite' => 'M.', 'nom' => 'Agossou', 'prenom' => 'David',
                'email' => 'd.agossou@techinnov.bj', 'phone' => '+229 01 23 45 69',
                'poste' => 'Assistant Comptable', 'departement' => 'Finance',
                'date_embauche' => '2024-06-01', 'type_contrat' => 'CDD',
                'salaire_base' => 200000, 'date_depart' => '2025-12-31',
                'status' => 'actif', 'created_by' => 1,
            ]
        );

        $emp4 = RhEmployee::updateOrCreate(
            ['matricule' => 'RH-004', 'client_id' => $client1Id],
            [
                'civilite' => 'Mme', 'nom' => 'Dossou', 'prenom' => 'Sarah',
                'email' => 's.dossou@techinnov.bj', 'phone' => '+229 01 23 45 70',
                'poste' => 'Responsable RH', 'departement' => 'Ressources Humaines',
                'date_embauche' => '2023-09-20', 'type_contrat' => 'CDI',
                'salaire_base' => 300000, 'cnss_number' => 'CNSS-RH-004',
                'situation_matrimoniale' => 'marie', 'nombre_enfants' => 1,
                'status' => 'actif', 'created_by' => 1,
            ]
        );

        $emp5 = RhEmployee::updateOrCreate(
            ['matricule' => 'RH-005', 'client_id' => $client1Id],
            [
                'civilite' => 'M.', 'nom' => 'Soglo', 'prenom' => 'Paul',
                'email' => 'p.soglo@techinnov.bj', 'phone' => '+229 01 23 45 71',
                'poste' => 'Fiscaliste', 'departement' => 'Fiscal',
                'date_embauche' => '2022-11-01', 'type_contrat' => 'CDI',
                'salaire_base' => 450000, 'ifu_number' => 'IFU-RH-005',
                'status' => 'actif', 'created_by' => 1,
            ]
        );

        // --- Employés Client 2 (Africa Logistics) ---
        $emp6 = RhEmployee::updateOrCreate(
            ['matricule' => 'RH-006', 'client_id' => $client2Id],
            [
                'civilite' => 'M.', 'nom' => 'Toure', 'prenom' => 'Moussa',
                'email' => 'm.toure@africa-logistics.com', 'phone' => '+229 97 65 43 21',
                'poste' => 'Directeur Logistique', 'departement' => 'Logistique',
                'date_embauche' => '2021-06-15', 'type_contrat' => 'CDI',
                'salaire_base' => 550000, 'cnss_number' => 'CNSS-RH-006',
                'status' => 'actif', 'created_by' => 1,
            ]
        );

        $emp7 = RhEmployee::updateOrCreate(
            ['matricule' => 'RH-007', 'client_id' => $client2Id],
            [
                'civilite' => 'Mme', 'nom' => 'Diallo', 'prenom' => 'Aminata',
                'email' => 'a.diallo@africa-logistics.com', 'phone' => '+229 97 65 43 22',
                'poste' => 'Responsable Administratif', 'departement' => 'Administration',
                'date_embauche' => '2023-03-01', 'type_contrat' => 'CDI',
                'salaire_base' => 320000,
                'status' => 'actif', 'created_by' => 1,
            ]
        );

        $emp8 = RhEmployee::updateOrCreate(
            ['matricule' => 'RH-008', 'client_id' => $client2Id],
            [
                'civilite' => 'M.', 'nom' => 'Garcia', 'prenom' => 'Carlos',
                'email' => 'c.garcia@africa-logistics.com', 'phone' => '+229 97 65 43 23',
                'poste' => 'Chauffeur Senior', 'departement' => 'Transport',
                'date_embauche' => '2024-09-01', 'type_contrat' => 'INTERIM',
                'salaire_base' => 150000,
                'status' => 'actif', 'created_by' => 1,
            ]
        );

        // --- Contrats ---
        RhContract::updateOrCreate(
            ['reference' => 'CTR-RH-001', 'employee_id' => $emp1->id],
            [
                'type' => 'CDI', 'date_debut' => '2022-03-15',
                'poste' => 'Comptable Senior', 'departement' => 'Finance',
                'salaire' => 350000, 'periode_essai_jours' => 90,
                'renouvelable' => false, 'statut' => 'actif',
                'created_by' => 1,
            ]
        );

        RhContract::updateOrCreate(
            ['reference' => 'CTR-RH-002', 'employee_id' => $emp2->id],
            [
                'type' => 'CDI', 'date_debut' => '2023-01-10',
                'poste' => 'Juriste d\'Affaires', 'departement' => 'Juridique',
                'salaire' => 400000, 'periode_essai_jours' => 90,
                'renouvelable' => false, 'statut' => 'actif',
                'created_by' => 1,
            ]
        );

        RhContract::updateOrCreate(
            ['reference' => 'CTR-RH-003', 'employee_id' => $emp3->id],
            [
                'type' => 'CDD', 'date_debut' => '2024-06-01', 'date_fin' => '2025-12-31',
                'duree_mois' => 19, 'poste' => 'Assistant Comptable', 'departement' => 'Finance',
                'salaire' => 200000, 'periode_essai_jours' => 30,
                'renouvelable' => true, 'statut' => 'actif',
                'created_by' => 1,
            ]
        );

        RhContract::updateOrCreate(
            ['reference' => 'CTR-RH-004', 'employee_id' => $emp6->id],
            [
                'type' => 'CDI', 'date_debut' => '2021-06-15',
                'poste' => 'Directeur Logistique', 'departement' => 'Logistique',
                'salaire' => 550000, 'periode_essai_jours' => 90,
                'renouvelable' => false, 'statut' => 'actif',
                'created_by' => 1,
            ]
        );

        RhContract::updateOrCreate(
            ['reference' => 'CTR-RH-005', 'employee_id' => $emp7->id],
            [
                'type' => 'CDI', 'date_debut' => '2023-03-01',
                'poste' => 'Responsable Administratif', 'departement' => 'Administration',
                'salaire' => 320000, 'periode_essai_jours' => 90,
                'renouvelable' => false, 'statut' => 'actif',
                'created_by' => 1,
            ]
        );

        // --- Congés ---
        RhLeaveRequest::updateOrCreate(
            ['employee_id' => $emp1->id, 'date_debut' => '2025-07-01'],
            [
                'type' => 'conge', 'date_fin' => '2025-07-15', 'duree_jours' => 15,
                'motif' => 'Congés annuels', 'statut' => 'approved',
                'approbateur_id' => 1, 'date_approbation' => now()->subDays(10),
            ]
        );

        RhLeaveRequest::updateOrCreate(
            ['employee_id' => $emp2->id, 'date_debut' => '2025-06-01'],
            [
                'type' => 'conge', 'date_fin' => '2025-06-10', 'duree_jours' => 10,
                'motif' => 'Vacances', 'statut' => 'approved',
                'approbateur_id' => 1, 'date_approbation' => now()->subDays(20),
            ]
        );

        RhLeaveRequest::updateOrCreate(
            ['employee_id' => $emp4->id, 'date_debut' => now()->format('Y-m-d')],
            [
                'type' => 'maladie', 'date_fin' => now()->addDays(3)->format('Y-m-d'),
                'duree_jours' => 3, 'motif' => 'Repos médical', 'statut' => 'pending',
            ]
        );

        RhLeaveRequest::updateOrCreate(
            ['employee_id' => $emp6->id, 'date_debut' => '2025-08-01'],
            [
                'type' => 'conge', 'date_fin' => '2025-08-20', 'duree_jours' => 20,
                'motif' => 'Congés annuels', 'statut' => 'pending',
            ]
        );

        RhLeaveRequest::updateOrCreate(
            ['employee_id' => $emp3->id, 'date_debut' => now()->subDays(10)->format('Y-m-d')],
            [
                'type' => 'formation', 'date_fin' => now()->subDays(8)->format('Y-m-d'),
                'duree_jours' => 3, 'motif' => 'Formation Excel avancé', 'statut' => 'approved',
                'approbateur_id' => 1, 'date_approbation' => now()->subDays(15),
            ]
        );

        RhLeaveRequest::updateOrCreate(
            ['employee_id' => $emp8->id, 'date_debut' => now()->addDays(5)->format('Y-m-d')],
            [
                'type' => 'conge', 'date_fin' => now()->addDays(7)->format('Y-m-d'),
                'duree_jours' => 3, 'motif' => 'Permission personnelle', 'statut' => 'approved',
                'approbateur_id' => 1, 'date_approbation' => now()->subDays(1),
            ]
        );

        // --- Notes de frais ---
        RhExpense::updateOrCreate(
            ['employee_id' => $emp1->id, 'categorie' => 'Transport'],
            [
                'montant' => 45000, 'description' => 'Déplacement client Parakou',
                'statut' => 'pending',
            ]
        );

        RhExpense::updateOrCreate(
            ['employee_id' => $emp2->id, 'categorie' => 'Repas'],
            [
                'montant' => 12500, 'description' => 'Repas d\'affaires avec cabinet juridique',
                'statut' => 'approved', 'approbateur_id' => 1,
                'date_approbation' => now()->subDays(2),
            ]
        );

        RhExpense::updateOrCreate(
            ['employee_id' => $emp6->id, 'categorie' => 'Carburant'],
            [
                'montant' => 85000, 'description' => 'Plein carburant véhicule logistique',
                'statut' => 'paid', 'approbateur_id' => 1,
                'date_approbation' => now()->subDays(5), 'date_paiement' => now()->subDays(3),
            ]
        );

        RhExpense::updateOrCreate(
            ['employee_id' => $emp7->id, 'categorie' => 'Fournitures'],
            [
                'montant' => 23500, 'description' => 'Fournitures de bureau',
                'statut' => 'pending',
            ]
        );

        // --- Fiches de paie ---
        $currentPeriod = now()->format('Y-m');
        $lastPeriod = now()->subMonth()->format('Y-m');

        foreach ([$emp1, $emp2, $emp4, $emp5, $emp6, $emp7] as $index => $emp) {
            $base = (float) $emp->salaire_base;
            $primes = json_encode(['transport' => 25000, 'performance' => $base * 0.1]);
            $cotisations = json_encode(['cnss' => $base * 0.07, 'assurance' => 5000]);
            $indemnites = json_encode(['logement' => 30000, 'cantine' => 15000]);
            $totalPrimes = 25000 + ($base * 0.1);
            $totalCotisations = ($base * 0.07) + 5000;
            $totalIndemnites = 30000 + 15000;
            $net = $base + $totalPrimes + $totalIndemnites - $totalCotisations;

            RhPayroll::updateOrCreate(
                ['employee_id' => $emp->id, 'periode' => $currentPeriod],
                [
                    'salaire_base' => $base,
                    'primes' => $primes,
                    'indemnites' => $indemnites,
                    'cotisations' => $cotisations,
                    'avance' => 0,
                    'net_a_payer' => $net,
                    'statut' => $index < 3 ? 'valide' : 'calcule',
                    'created_by' => 1,
                ]
            );

            // Paie mois dernier
            RhPayroll::updateOrCreate(
                ['employee_id' => $emp->id, 'periode' => $lastPeriod],
                [
                    'salaire_base' => $base,
                    'primes' => $primes,
                    'indemnites' => $indemnites,
                    'cotisations' => $cotisations,
                    'avance' => 0,
                    'net_a_payer' => $net,
                    'statut' => 'paye',
                    'date_paiement' => now()->subDays(5),
                    'created_by' => 1,
                ]
            );
        }

        // --- Présences ---
        $today = now();
        for ($i = 0; $i < 5; $i++) {
            $date = $today->copy()->subDays($i);
            if ($date->isWeekend()) continue;

            foreach ([$emp1, $emp2, $emp4, $emp5] as $emp) {
                RhAttendance::updateOrCreate(
                    ['employee_id' => $emp->id, 'date' => $date->format('Y-m-d')],
                    [
                        'heure_arrivee' => '08:00', 'heure_depart' => '17:00',
                        'heures_travaillees' => 8.0,
                        'type_presence' => 'present',
                    ]
                );
            }
        }

        // --- Formations ---
        RhTraining::updateOrCreate(
            ['employee_id' => $emp3->id, 'titre' => 'Excel Avancé'],
            [
                'organisme' => 'FormaPro Bénin', 'date_debut' => '2025-05-12',
                'date_fin' => '2025-05-14', 'duree_heures' => 24,
                'cout' => 75000, 'type' => 'interne',
                'statut' => 'termine',
            ]
        );

        RhTraining::updateOrCreate(
            ['employee_id' => $emp1->id, 'titre' => 'IFRS 2025'],
            [
                'organisme' => 'Ordre des Experts Comptables', 'date_debut' => '2025-08-01',
                'date_fin' => '2025-08-05', 'duree_heures' => 40,
                'cout' => 150000, 'type' => 'externe',
                'statut' => 'planifie',
            ]
        );

        RhTraining::updateOrCreate(
            ['employee_id' => $emp4->id, 'titre' => 'Management RH 2.0'],
            [
                'organisme' => 'LinkedIn Learning', 'date_debut' => '2025-07-01',
                'date_fin' => '2025-07-30', 'duree_heures' => 20,
                'cout' => 45000, 'type' => 'en_ligne',
                'statut' => 'en_cours',
            ]
        );

        // --- Alertes ---
        RhAlert::updateOrCreate(
            ['client_id' => $client1Id, 'type' => 'contrat_fin', 'titre' => 'Fin contrat CDD - David Agossou'],
            [
                'employee_id' => $emp3->id,
                'description' => 'Le contrat CDD de David Agossou expire le 31/12/2025',
                'date_echeance' => '2025-12-31', 'days_before' => 30,
                'statut' => 'active',
            ]
        );

        RhAlert::updateOrCreate(
            ['client_id' => $client2Id, 'type' => 'cnss', 'titre' => 'Déclaration CNSS Trimestrielle'],
            [
                'description' => 'Déclaration CNSS du 3e trimestre à effectuer',
                'date_echeance' => now()->endOfQuarter()->format('Y-m-d'),
                'days_before' => 15, 'statut' => 'active',
            ]
        );

        $month = now()->month;
        $year = now()->year;
        RhAlert::updateOrCreate(
            ['client_id' => $client1Id, 'type' => 'anniversaire', 'titre' => 'Anniversaire - Marie Hounsou'],
            [
                'employee_id' => $emp2->id,
                'description' => "Anniversaire de Marie Hounsou ({$month}/{$year})",
                'date_echeance' => now()->addDays(5)->format('Y-m-d'),
                'days_before' => 3, 'statut' => 'active',
            ]
        );

        $this->command->info("RH Demo data seeded: 8 employees, 5 contracts, 6 leaves, 4 expenses, payrolls, attendance, trainings, alerts.");
    }
}
