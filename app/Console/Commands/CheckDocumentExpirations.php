<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CheckDocumentExpirations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:check-expirations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie les dates d\'expiration des documents et le statut des signatures pour alerter les utilisateurs.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Vérification des documents en cours...');
        
        // 1. Documents expirant dans moins de 30 jours
        $expiringSoon = Document::whereNotNull('expiration_date')
            ->where('expiration_date', '<=', Carbon::now()->addDays(30))
            ->where('expiration_date', '>', Carbon::now())
            ->where('is_archived', false)
            ->get();
            
        foreach ($expiringSoon as $doc) {
            // Ici, intégration avec le système de notification (ex: Email, in-app Notif)
            Log::info("Le document {$doc->name} expire bientôt ({$doc->expiration_date}).");
            // App\Notifications\DocumentExpiringNotification...
        }
        
        // 2. Documents dont la signature est en attente depuis plus de 7 jours
        $pendingSignatures = Document::where('requires_signature', true)
            ->where('signature_status', 'pending')
            ->where('updated_at', '<=', Carbon::now()->subDays(7))
            ->where('is_archived', false)
            ->get();
            
        foreach ($pendingSignatures as $doc) {
            Log::info("Le document {$doc->name} est en attente de signature depuis plus de 7 jours.");
            // Relance automatique de la signature
        }

        $this->info('Vérification terminée. ' . $expiringSoon->count() . ' documents expirent bientôt, ' . $pendingSignatures->count() . ' en attente de signature.');
    }
}
