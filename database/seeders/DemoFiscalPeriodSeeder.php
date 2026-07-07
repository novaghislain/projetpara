<?php

namespace Database\Seeders;

use App\Models\FiscalPeriod;
use App\Models\FiscalYear;
use Illuminate\Database\Seeder;

class DemoFiscalPeriodSeeder extends Seeder
{
    /**
     * Crée 12 périodes mensuelles pour les exercices existants.
     */
    public function run(): void
    {
        $years = FiscalYear::where('status', 'open')->get();

        if ($years->isEmpty()) {
            $this->command?->warn('Aucun exercice ouvert trouvé.');
            return;
        }

        $months = [
            ['M01', 'Janvier'], ['M02', 'Février'], ['M03', 'Mars'],
            ['M04', 'Avril'],   ['M05', 'Mai'],      ['M06', 'Juin'],
            ['M07', 'Juillet'], ['M08', 'Août'],     ['M09', 'Septembre'],
            ['M10', 'Octobre'], ['M11', 'Novembre'], ['M12', 'Décembre'],
        ];

        foreach ($years as $year) {
            $existing = FiscalPeriod::where('fiscal_year_id', $year->id)->count();
            if ($existing > 0) {
                $this->command?->info("Périodes existantes pour {$year->year}, ignoré.");
                continue;
            }

            foreach ($months as $i => [$code, $label]) {
                $monthNum = $i + 1;
                $startDate = sprintf('%d-%02d-01', $year->year, $monthNum);
                $endDate = date('Y-m-t', strtotime($startDate));

                FiscalPeriod::create([
                    'fiscal_year_id' => $year->id,
                    'code' => $code,
                    'label' => "{$label} {$year->year}",
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'open',
                ]);
            }

            $this->command?->info("✓ 12 périodes créées pour {$year->year}");
        }
    }
}
