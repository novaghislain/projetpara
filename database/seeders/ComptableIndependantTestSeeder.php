<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Gel\Cabinet;
use App\Models\IndependantComptableClient;
use Carbon\Carbon;

class ComptableIndependantTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $cabinet = Cabinet::firstOrCreate(
                ['email' => 'cabinet.solo.demo@test.com'],
                [
                    'nom'  => 'Cabinet Solo Demo Comptable',
                    'slug' => 'cabinet-solo-demo',
                    'actif' => true,
                ]
            );

            $user = User::updateOrCreate(
                ['email' => 'demo.comptable@test.com'],
                [
                    'name'                   => 'Demo Comptable',
                    'password'               => Hash::make('password'),
                    'role'                   => 'comptable',
                    'workspace_type'         => 'individuel_comptable',
                    'account_context'        => ['model3_comptable'],
                    'active_account_context' => 'model3_comptable',
                    'trial_ends_at'          => Carbon::now()->addDays(30),
                    'subscription_status'    => 'trial',
                    'is_active'              => true,
                    'onboarding_completed'   => true,
                    'cabinet_id'             => $cabinet->id,
                    'personal_company_name'  => 'Cabinet Solo Demo',
                ]
            );

            $this->command->info("User: {$user->email} (id={$user->id}) cabinet_id={$cabinet->id}");

            $clientA = IndependantComptableClient::updateOrCreate(
                ['comptable_id' => $user->id, 'nom_entreprise' => 'SARL Alpha Commerce'],
                [
                    'contact_nom' => 'Jean Alpha',
                    'email'       => 'alpha@example.com',
                    'secteur'     => 'Commerce general',
                    'ifu'         => 'IFU-TEST-001',
                    'type'        => 'manuel',
                ]
            );

            $clientB = IndependantComptableClient::updateOrCreate(
                ['comptable_id' => $user->id, 'nom_entreprise' => 'SARO Beta Services'],
                [
                    'contact_nom' => 'Marie Beta',
                    'email'       => 'beta@example.com',
                    'secteur'     => 'Services informatiques',
                    'ifu'         => 'IFU-TEST-002',
                    'type'        => 'manuel',
                ]
            );

            $this->command->info("Client A: {$clientA->nom_entreprise} (id={$clientA->id})");
            $this->command->info("Client B: {$clientB->nom_entreprise} (id={$clientB->id})");
            $this->command->info("Login: demo.comptable@test.com / password");
        });
    }
}
