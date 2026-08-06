<?php

namespace App\Console\Commands;

use App\Models\Dae\DaeAgendaEvent;
use App\Models\Gel\Client;
use App\Models\Gel\FiscalParameter;
use Illuminate\Console\Command;

/**
 * Précharge le calendrier fiscal et social (Section 14) dans l'Agenda.
 *
 * Crée réellement les événements d'échéance (TVA, ITS / IR, CNSS, Patente)
 * pour une année donnée, en s'appuyant sur le référentiel paramétrable
 * `gel_fiscal_parameters`. Idempotent : n'insère pas de doublons.
 *
 * Usage :
 *   php artisan fiscal:calendar 2026
 *   php artisan fiscal:calendar 2026 --cabinet=1
 */
class FiscalCalendarCommand extends Command
{
    protected $signature = 'fiscal:calendar {year? : Année (défaut : exercice paramétré ou année courante)} {--cabinet= : ID du cabinet (défaut : tous)}';

    protected $description = 'Précharge le calendrier fiscal & social des entreprises dans l\'Agenda pour une année.';

    public function handle(): int
    {
        $params = FiscalParameter::tableau();
        $year = (int) ($this->argument('year') ?? $params[FiscalParameter::EXERCICE_FISCAL] ?? now()->year);
        $cabinetId = $this->option('cabinet');

        $clientsQuery = Client::query();
        if ($cabinetId) {
            $clientsQuery->where('cabinet_id', (int) $cabinetId);
        }
        $clients = $clientsQuery->whereNotNull('cabinet_id')->get();

        if ($clients->isEmpty()) {
            $this->warn('Aucune entreprise cliente trouvée pour le calendrier.');
            return self::SUCCESS;
        }

        $tvaDay = (int) ($params[FiscalParameter::TVA_DUE_DAY] ?? 15);
        $itsDay = (int) ($params[FiscalParameter::ITS_DUE_DAY] ?? 15);
        $cnssDay = (int) ($params[FiscalParameter::CNSS_DUE_DAY] ?? 15);
        $patenteDay = (int) ($params[FiscalParameter::PATENTE_DUE_DAY] ?? 31);
        $patenteMonth = 12;

        $created = 0;
        $skipped = 0;

        foreach ($clients as $client) {
            // Échéances mensuelles : TVA, ITS, CNSS (le jour J du mois suivant M+1)
            for ($m = 1; $m <= 12; $m++) {
                $labelDate = sprintf('%s-%02d', $year, $m);

                $bilans = [
                    ['echeance_fiscale', "TVA — $labelDate", $tvaDay, '#7C3AED'],
                    ['echeance_fiscale', "ITS — $labelDate", $itsDay, '#7C3AED'],
                    ['echeance_cnss', "Déclaration CNSS — $labelDate", $cnssDay, '#EC4899'],
                ];

                foreach ($bilans as [$type, $title, $day, $couleur]) {
                    $start = \Carbon\Carbon::create($year, $m, 1)->setTime(23, 59)->format('Y-m-d') . ' 00:00:00';
                    // Échéance = mois suivant, jour J
                    $due = \Carbon\Carbon::create($year, $m)->startOfMonth()
                        ->addMonth()->setDay(min($day, now()->addMonth()->daysInMonth))->setTime(23, 59);

                    $exists = DaeAgendaEvent::where('client_id', $client->id)
                        ->where('type', $type)
                        ->whereDate('start_at', $due->toDateString())
                        ->where('title', $title)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    DaeAgendaEvent::create([
                        'client_id' => $client->id,
                        'title' => $title,
                        'description' => 'Échéance générée depuis le référentiel fiscal & social paramétrable.',
                        'type' => $type,
                        'start_at' => $due,
                        'end_at' => $due->copy()->addMinutes(30),
                        'couleur' => $couleur,
                        'statut' => 'planifie',
                        'created_by' => null,
                    ]);
                    $created++;
                }
            }

            // Patente — échéance annuelle (décembre)
            $patenteDue = \Carbon\Carbon::create($year, $patenteMonth, min($patenteDay, 28))->setTime(23, 59);
            $patenteTitle = "Patente $year — paiement";
            if (!DaeAgendaEvent::where('client_id', $client->id)
                ->where('type', 'echeance_fiscale')
                ->whereDate('start_at', $patenteDue->toDateString())
                ->where('title', $patenteTitle)
                ->exists()) {
                DaeAgendaEvent::create([
                    'client_id' => $client->id,
                    'title' => $patenteTitle,
                    'description' => 'Droit annuel de patente exigible selon le référentiel.',
                    'type' => 'echeance_fiscale',
                    'start_at' => $patenteDue,
                    'end_at' => $patenteDue->copy()->addMinutes(30),
                    'couleur' => '#7C3AED',
                    'statut' => 'planifie',
                    'created_by' => null,
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->info("[{$year}] {$created} événement(s) fiscal(aux) générés pour {$clients->count()} entreprise(s). {$skipped} déjà présents (idempotent).");

        return self::SUCCESS;
    }
}