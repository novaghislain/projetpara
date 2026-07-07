<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use App\Models\EntryLine;
use App\Models\FiscalPeriod;
use App\Models\FiscalYear;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoJournalSeeder extends Seeder
{
    public function run(int $clientId, int $userId): void
    {
        // ---------------------------------------------------------------
        // 1. Créer les journaux comptables
        // ---------------------------------------------------------------
        $journalData = [
            ['code' => 'OD', 'label' => 'Opérations Diverses',        'type' => 'operations_diverses', 'prefix' => 'OD'],
            ['code' => 'VE', 'label' => 'Ventes',                     'type' => 'ventes',              'prefix' => 'VE'],
            ['code' => 'AC', 'label' => 'Achats',                     'type' => 'achats',              'prefix' => 'AC'],
            ['code' => 'BQ', 'label' => 'Banque',                     'type' => 'banque',              'prefix' => 'BQ'],
            ['code' => 'CA', 'label' => 'Caisse',                     'type' => 'caisse',              'prefix' => 'CA'],
            ['code' => 'AN', 'label' => 'À Nouveaux',                 'type' => 'a_nouveaux',          'prefix' => 'AN'],
            ['code' => 'SA', 'label' => 'Salaires',                   'type' => 'salaires',            'prefix' => 'SA'],
        ];

        $journals = [];
        foreach ($journalData as $j) {
            $journals[$j['code']] = Journal::firstOrCreate(
                ['client_id' => $clientId, 'code' => $j['code']],
                [
                    'label'      => $j['label'],
                    'type'       => $j['type'],
                    'prefix'     => $j['prefix'],
                    'is_active'  => true,
                    'next_number'=> 1,
                ]
            );
        }

        // ---------------------------------------------------------------
        // 2. Créer l'exercice fiscal 2024 + périodes mensuelles
        // ---------------------------------------------------------------
        $fiscalYear = FiscalYear::firstOrCreate(
            ['client_id' => $clientId, 'year' => 2024],
            [
                'date_start' => '2024-01-01',
                'date_end'   => '2024-12-31',
                'status'     => 'open',
            ]
        );

        // Créer les 12 périodes mensuelles si elles n'existent pas
        $periods = [];
        for ($m = 1; $m <= 12; $m++) {
            $code = sprintf('2024-%02d', $m);
            $start = sprintf('2024-%02d-01', $m);
            $end = $m === 12 ? '2024-12-31' : date('Y-m-t', strtotime($start));

            $period = FiscalPeriod::firstOrCreate(
                ['fiscal_year_id' => $fiscalYear->id, 'code' => $code],
                [
                    'label'      => \Carbon\Carbon::create(2024, $m, 1)->translatedFormat('F Y'),
                    'start_date' => $start,
                    'end_date'   => $end,
                    'status'     => 'open',
                ]
            );
            $periods[$code] = $period;
        }

        // ---------------------------------------------------------------
        // 3. Helper pour récupérer un compte comptable
        // ---------------------------------------------------------------
        $getAccount = function (string $code) use ($clientId): int {
            return AccountingAccount::where('client_id', $clientId)
                ->where('code', $code)
                ->firstOrFail()->id;
        };

        $getPeriod = function (string $date) use ($periods): int {
            $month = (int) substr($date, 5, 2);
            $code = sprintf('2024-%02d', $month);
            return $periods[$code]->id;
        };

        // ---------------------------------------------------------------
        // 4. Créer les écritures de démonstration
        // ---------------------------------------------------------------
        $entries = [
            // ÉCRITURE 1 : Apport en capital
            [
                'journal'     => $journals['OD'],
                'date'        => '2024-01-02',
                'ref'         => 'EC-20240102-001',
                'description' => "Apport en capital social",
                'lines'       => [
                    ['code' => '511100', 'debit' => 15000000, 'credit' => 0,     'desc' => 'Dépôt banque Société Générale'],
                    ['code' => '101200', 'debit' => 0,         'credit' => 15000000, 'desc' => 'Apport capital social'],
                ],
            ],
            // ÉCRITURE 2 : Achat de matériel informatique
            [
                'journal'     => $journals['AC'],
                'date'        => '2024-01-15',
                'ref'         => 'EC-20240115-002',
                'description' => 'Achat matériel informatique DELL',
                'lines'       => [
                    ['code' => '218100', 'debit' => 2500000, 'credit' => 0,     'desc' => 'Ordinateurs portables (5 x DELL)'],
                    ['code' => '431200', 'debit' =>  450000, 'credit' => 0,     'desc' => 'TVA récupérable 18%'],
                    ['code' => '401100', 'debit' => 0,         'credit' => 2950000, 'desc' => 'Fournisseur DELL SARL'],
                ],
            ],
            // ÉCRITURE 3 : Paiement fournisseur
            [
                'journal'     => $journals['BQ'],
                'date'        => '2024-01-20',
                'ref'         => 'EC-20240120-003',
                'description' => 'Paiement facture DELL',
                'lines'       => [
                    ['code' => '401100', 'debit' => 2950000, 'credit' => 0,     'desc' => 'Paiement facture DELL'],
                    ['code' => '511100', 'debit' => 0,         'credit' => 2950000, 'desc' => 'Virement bancaire'],
                ],
            ],
            // ÉCRITURE 4 : Vente prestation de conseil
            [
                'journal'     => $journals['VE'],
                'date'        => '2024-02-01',
                'ref'         => 'EC-20240201-004',
                'description' => 'Prestation conseil client ABC SARL',
                'lines'       => [
                    ['code' => '411100', 'debit' => 5900000, 'credit' => 0,     'desc' => 'Client ABC SARL'],
                    ['code' => '711000', 'debit' => 0,         'credit' => 5000000, 'desc' => 'Prestation conseil stratégique'],
                    ['code' => '431100', 'debit' => 0,         'credit' =>  900000, 'desc' => 'TVA collectée 18%'],
                ],
            ],
            // ÉCRITURE 5 : Encaissement client
            [
                'journal'     => $journals['BQ'],
                'date'        => '2024-02-15',
                'ref'         => 'EC-20240215-005',
                'description' => 'Encaissement client ABC SARL',
                'lines'       => [
                    ['code' => '511100', 'debit' => 5900000, 'credit' => 0,     'desc' => 'Chèque client ABC SARL'],
                    ['code' => '411100', 'debit' => 0,         'credit' => 5900000, 'desc' => 'Règlement facture FJ-2024-001'],
                ],
            ],
            // ÉCRITURE 6 : Paiement loyer local
            [
                'journal'     => $journals['OD'],
                'date'        => '2024-02-28',
                'ref'         => 'EC-20240228-006',
                'description' => 'Paiement loyer local février 2024',
                'lines'       => [
                    ['code' => '612000', 'debit' => 500000, 'credit' => 0,     'desc' => 'Loyer local commercial'],
                    ['code' => '511100', 'debit' => 0,         'credit' => 500000, 'desc' => 'Virement bancaire'],
                ],
            ],
            // ÉCRITURE 7 : Achat fournitures de bureau (caisse)
            [
                'journal'     => $journals['CA'],
                'date'        => '2024-03-05',
                'ref'         => 'EC-20240305-007',
                'description' => 'Achat fournitures bureau espèces',
                'lines'       => [
                    ['code' => '603000', 'debit' => 150000, 'credit' => 0,     'desc' => 'Fournitures de bureau'],
                    ['code' => '521100', 'debit' => 0,         'credit' => 150000, 'desc' => 'Sortie caisse'],
                ],
            ],
            // ÉCRITURE 8 : Virement entre banques
            [
                'journal'     => $journals['BQ'],
                'date'        => '2024-03-10',
                'ref'         => 'EC-20240310-008',
                'description' => 'Virement SG vers Ecobank',
                'lines'       => [
                    ['code' => '511100', 'debit' => 0,         'credit' => 2000000, 'desc' => 'Virement sortant SG'],
                    ['code' => '511200', 'debit' => 2000000, 'credit' => 0,     'desc' => 'Virement entrant Ecobank'],
                ],
            ],
            // ÉCRITURE 9 : Salaires mars (brut + charges)
            [
                'journal'     => $journals['SA'],
                'date'        => '2024-03-28',
                'ref'         => 'EC-20240328-009',
                'description' => 'Salaires mars 2024',
                'lines'       => [
                    ['code' => '641000', 'debit' => 3500000, 'credit' => 0,     'desc' => 'Salaires bruts'],
                    ['code' => '642000', 'debit' =>  700000, 'credit' => 0,     'desc' => 'Charges sociales patronales'],
                    ['code' => '421000', 'debit' => 0,         'credit' => 3500000, 'desc' => 'Net à payer personnel'],
                    ['code' => '423000', 'debit' => 0,         'credit' =>  700000, 'desc' => 'CNSS/ITS à payer'],
                ],
            ],
            // ÉCRITURE 10 : Paiement salaires via banque
            [
                'journal'     => $journals['BQ'],
                'date'        => '2024-03-29',
                'ref'         => 'EC-20240329-010',
                'description' => 'Virement salaires mars 2024',
                'lines'       => [
                    ['code' => '421000', 'debit' => 3500000, 'credit' => 0,     'desc' => 'Virement salaires personnel'],
                    ['code' => '423000', 'debit' =>  700000, 'credit' => 0,     'desc' => 'Règlement charges sociales'],
                    ['code' => '511100', 'debit' => 0,         'credit' => 4200000, 'desc' => 'Débit compte SG'],
                ],
            ],
            // ÉCRITURE 11 : Dotations aux amortissements (inventaire)
            [
                'journal'     => $journals['OD'],
                'date'        => '2024-12-31',
                'ref'         => 'EC-20241231-011',
                'description' => 'Dotations amortissements exercice 2024',
                'lines'       => [
                    ['code' => '681000', 'debit' => 500000, 'credit' => 0,     'desc' => 'Dotation amortissement matériel informatique'],
                    ['code' => '284000', 'debit' => 0,         'credit' => 500000, 'desc' => 'Amortissement linéaire du matériel'],
                ],
            ],
        ];

        $createdCount = 0;
        foreach ($entries as $data) {
            $monthCode = sprintf('2024-%02d', (int) substr($data['date'], 5, 2));
            $period = $periods[$monthCode];

            // Calculer totaux
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($data['lines'] as $line) {
                $totalDebit += $line['debit'];
                $totalCredit += $line['credit'];
            }

            // Créer l'écriture
            $entry = JournalEntry::create([
                'client_id'        => $clientId,
                'journal_id'       => $data['journal']->id,
                'fiscal_period_id' => $period->id,
                'entry_date'       => $data['date'],
                'reference'        => $data['ref'],
                'description'      => $data['description'],
                'total_debit'      => $totalDebit,
                'total_credit'     => $totalCredit,
                'is_balanced'      => true,
                'status'           => 'posted',
                'created_by'       => $userId,
            ]);

            // Créer les lignes
            foreach ($data['lines'] as $i => $line) {
                $account = AccountingAccount::where('client_id', $clientId)
                    ->where('code', $line['code'])
                    ->first();

                EntryLine::create([
                    'client_id'     => $clientId,
                    'entry_id'      => $entry->id,
                    'line_number'   => $i + 1,
                    'account_id'    => $account?->id,
                    'account_code'  => $line['code'],
                    'account_label' => $account?->name ?? $line['code'],
                    'description'   => $line['desc'],
                    'debit'         => $line['debit'],
                    'credit'        => $line['credit'],
                ]);
            }

            // Incrémenter le compteur du journal
            $data['journal']->increment('next_number');
            $createdCount++;
        }

        $this->command?->info("✓ {$createdCount} écritures de démonstration créées pour le client #{$clientId}.");
    }
}
