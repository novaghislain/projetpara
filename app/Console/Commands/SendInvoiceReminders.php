<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Gel\GelMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des relances automatiques pour les factures impayées/en retard.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la vérification des relances de factures...');

        // Trouver les factures envoyées et non payées dont la date d'échéance est passée
        $overdueInvoices = Invoice::where('type', 'customer_invoice')
            ->whereIn('status', ['sent', 'overdue'])
            ->where('due_date', '<', Carbon::now())
            ->with(['partner', 'client'])
            ->get();

        $count = 0;

        foreach ($overdueInvoices as $invoice) {
            // Mettre à jour le statut en 'overdue' si ce n'est pas déjà fait
            if ($invoice->status !== 'overdue') {
                $invoice->update(['status' => 'overdue']);
            }

            // Ici, nous pourrions envoyer un email réel, mais pour le MVP,
            // nous allons créer un événement d'historique et potentiellement un GelMessage interne.
            
            // Pour marquer la relance
            // $invoice->increment('reminders_sent');
            // $invoice->update(['last_reminder_at' => Carbon::now()]);

            Log::info("Relance générée pour la facture {$invoice->invoice_number} (Client: {$invoice->partner_name})");

            $count++;
        }

        $this->info("Terminé. $count relances générées.");
    }
}
