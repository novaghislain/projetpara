<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // Si la facture vient d'être confirmée/envoyée, on notifie le client par WhatsApp
        if ($invoice->wasChanged('status') && $invoice->status === 'sent') {
            
            $clientPhone = $invoice->client->telephone ?? null;

            if ($clientPhone) {
                try {
                    $waService = app(WhatsAppService::class);
                    $message = "Bonjour {$invoice->client->nom_entreprise},\n\nVotre facture N° {$invoice->invoice_number} d'un montant de " . number_format($invoice->total, 0, ',', ' ') . " FCFA a été émise.\n\nMerci de votre confiance.\n- L'équipe GEL Cabinet";
                    
                    $waService->sendTextMessage($clientPhone, $message);

                    // Si on a généré un PDF accessible publiquement, on l'envoie aussi
                    // $pdfUrl = route('invoice.download', $invoice->id);
                    // $waService->sendDocument($clientPhone, $pdfUrl, 'Facture PDF');
                    
                } catch (\Exception $e) {
                    Log::error("InvoiceObserver WhatsApp Error: " . $e->getMessage());
                }
            }
        }
    }
}
