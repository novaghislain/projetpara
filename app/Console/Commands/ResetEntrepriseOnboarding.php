<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Réinitialise l'onboarding des entreprises existantes.
 *
 * Passé pour toutes les entreprises déjà inscrites avant la refonte
 * du parcours /gel-business, pour qu'elles revoient le nouveau parcours
 * (choix des espaces + complétion de profil) à leur prochain accès.
 *
 * Seules les entreprises (rôle company_admin ou account_type entreprise)
 * ayant un client sont concernées : les cabinets et les secrétaires ne
 * sont pas touchés, et les données comptables (client_id / entreprise_id)
 * sont conservées — le parcours étant idempotent, aucun doublon ne sera créé.
 */
class ResetEntrepriseOnboarding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onboarding:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialise l\'onboarding des entreprises existantes pour leur faire revoir le nouveau parcours.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = User::query()
            ->whereNotNull('client_id')
            ->where(fn ($q) => $q->where('role', 'company_admin')->orWhere('account_type', 'entreprise'));

        $count = $query->count();

        if ($count === 0) {
            $this->info('Aucune entreprise existante à réinitialiser.');
            return self::SUCCESS;
        }

        if (!$this->confirm("{$count} entreprise(s) vont revoir le nouveau parcours d'onboarding. Continuer ?", true)) {
            $this->info('Annulé.');
            return self::SUCCESS;
        }

        $updated = 0;
        $query->each(function (User $user) use (&$updated) {
            $user->account_type = 'entreprise';
            $user->onboarding_completed = false;
            $user->onboarding_token = Str::random(40);
            $user->wants_accounting = null;
            $user->wants_secretary = null;
            $user->save();
            $updated++;
        });

        $this->info("Onboarding réinitialisé pour {$updated} entreprise(s).");
        $this->warn('Elles revoient le choix des espaces et le profil au prochain accès.');

        return self::SUCCESS;
    }
}
