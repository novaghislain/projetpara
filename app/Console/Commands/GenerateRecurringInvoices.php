<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoicing\RecurringInvoice;
use App\Models\Invoice; // Supposons que c'est le modèle de facture
use Carbon\Carbon;

class GenerateRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère les factures pour les abonnements arrivés à échéance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        
        $dueRecurrences = RecurringInvoice::where('status', 'active')
                            ->where('next_invoice_date', '<=', $today)
                            ->get();

        $count = 0;

        foreach ($dueRecurrences as $recurrence) {
            // Créer la facture
            $invoice = Invoice::create([
                'client_id' => $recurrence->client_id,
                // 'partner_id' => $recurrence->partner_id, // Si pertinent
                'type' => 'sale',
                'status' => 'draft',
                'date' => $today,
                'due_date' => Carbon::parse($today)->addDays(15)->toDateString(),
                'subtotal' => $recurrence->amount,
                'tax_amount' => $recurrence->amount * 0.18, // Exemple TVA 18%
                'total_amount' => $recurrence->amount * 1.18,
            ]);

            // Mettre à jour la date de prochaine facturation
            $nextDate = Carbon::parse($recurrence->next_invoice_date);
            
            switch ($recurrence->frequency) {
                case 'daily':
                    $nextDate->addDay();
                    break;
                case 'weekly':
                    $nextDate->addWeek();
                    break;
                case 'monthly':
                    $nextDate->addMonth();
                    break;
                case 'yearly':
                    $nextDate->addYear();
                    break;
            }

            $recurrence->update(['next_invoice_date' => $nextDate->toDateString()]);
            
            $count++;
        }

        $this->info("$count factures récurrentes générées avec succès.");
    }
}
