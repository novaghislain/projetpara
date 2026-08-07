<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateInformaticien extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-informaticien';
    protected $description = 'Create informaticien account';

    public function handle()
    {
        $u = \App\Models\User::where('account_type', 'informaticien')->first();
        if (!$u) {
            $u = \App\Models\User::create([
                'name' => 'Informatique',
                'prenom' => 'Support',
                'email' => 'informaticien@gelsabinet.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'account_type' => 'informaticien',
                'role' => 'informaticien',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
        $this->info("Email: {$u->email}");
        $this->info("Password: password123");
    }
}
