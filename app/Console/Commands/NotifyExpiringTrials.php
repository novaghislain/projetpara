<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NotifyExpiringTrials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'secretary:notify-expiring-trials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifie les secrétaires autonomes dont l\'essai arrive à expiration (J-7, J-3, J-1, Jour J)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Vérification des essais expirants...');

        $intervals = [
            7 => 'Votre essai gratuit se termine dans 7 jours.',
            3 => 'Votre essai gratuit se termine dans 3 jours. Pensez à vous abonner.',
            1 => 'Dernier jour de votre essai gratuit !',
            0 => 'Votre essai est terminé. Abonnez-vous pour retrouver l\'accès.'
        ];

        foreach ($intervals as $days => $message) {
            // Identifier les utilisateurs concernés
            $users = \App\Models\User::where('workspace_type', 'individuel')
                ->where('subscription_status', 'trial')
                ->whereNotNull('trial_ends_at')
                ->whereDate('trial_ends_at', now()->addDays($days)->toDateString())
                ->get();

            foreach ($users as $user) {
                // Créer une notification interne (ex. RealTimeNotification ou autre)
                // Par simplification, on log ou on crée une notification standard :
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type'    => 'info',
                    'title'   => 'Abonnement',
                    'message' => $message,
                    'link'    => route('gel-secretary.subscription.expired'),
                ]);

                if ($days === 0) {
                    $user->update(['subscription_status' => 'expired']);
                }

                $this->line("Notifié {$user->email} pour J-{$days}");
            }
        }

        $this->info('Terminé.');
    }
}
