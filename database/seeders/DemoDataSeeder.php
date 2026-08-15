<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Gel\Client;
use App\Models\Gel\GelMessage;
use App\Models\Gel\Task;
use App\Models\Document;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Assurer l'existence du client principal
        $client = Client::firstOrCreate(
            ['email' => 'contact@techinnov.bj'],
            [
                'nom_entreprise' => 'TechInnov Bénin',
                'ifu' => '3201912345678',
                'rc' => 'RB/COTO/19 B 1234',
                'telephone' => '+229 01 23 45 67',
                'adresse' => 'Cotonou, Bénin',
                'statut' => 'actif',
                'cabinet_id' => 1
            ]
        );

        // 2. Création de faux documents
        for ($i = 1; $i <= 5; $i++) {
            Document::firstOrCreate(
                ['name' => "Facture_Achat_00$i.pdf"],
                [
                    'client_id' => $client->id,
                    'category' => 'facture_achat',
                    'workflow_step' => 'transmis_comptable',
                    'transmitted_at' => now()->subDays(rand(1, 10)),
                    'uploaded_by' => 1,
                    'file_path' => 'demo/fake.pdf'
                ]
            );
        }

        // 3. Création de fausses tâches de coordination
        Task::firstOrCreate(
            ['titre' => 'Vérification IFU TechInnov'],
            [
                'client_id' => $client->id,
                'cabinet_id' => 1,
                'assigned_to' => User::where('email', 'comptable@demo.com')->value('id') ?? 1,
                'created_by' => User::where('email', 'secretaire@demo.com')->value('id') ?? 1,
                'description' => 'Merci de vérifier si cet IFU est à jour sur le portail des impôts.',
                'priorite' => 'haute',
                'statut' => 'a_faire',
                'source' => 'coordination'
            ]
        );

        // 4. Fausses conversations
        GelMessage::firstOrCreate(
            ['message' => 'Bonjour, pouvez-vous valider le dernier lot de factures ?'],
            [
                'client_id' => $client->id,
                'cabinet_id' => 1,
                'sender_id' => User::where('email', 'secretaire@demo.com')->value('id') ?? 1,
                'sender_type' => 'secretary',
                'receiver_id' => User::where('email', 'comptable@demo.com')->value('id') ?? 1,
                'channel' => GelMessage::CHANNEL_COORDINATION,
                'est_lu' => false
            ]
        );

        $this->command->info("Démo de Coordination générée avec succès.");
    }
}
