<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\RelanceRule;
use Illuminate\Database\Seeder;

class DemoRelanceSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::where('status', 'actif')->get();
        if ($clients->isEmpty()) return;

        RelanceRule::create([
            'client_id'        => $clients->first()->id,
            'name'             => 'Relance J-7',
            'trigger_days'     => 7,
            'channel'          => 'sms',
            'template_subject' => 'Facture {{invoice_number}} à échéance',
            'template_body'    => "Bonjour {{client_name}},\nVotre facture {{invoice_number}} de {{amount}} € arrive à échéance le {{due_date}}.\nGEL Cabinet",
            'is_active'        => true,
        ]);

        RelanceRule::create([
            'client_id'        => $clients->first()->id,
            'name'             => 'Relance J-1 WhatsApp',
            'trigger_days'     => 1,
            'channel'          => 'whatsapp',
            'template_subject' => 'URGENT — Facture {{invoice_number}}',
            'template_body'    => "Bonjour {{client_name}},\nVotre facture {{invoice_number}} de {{amount}} € est due demain ({{due_date}}).\nGEL Cabinet",
            'is_active'        => true,
        ]);

        $this->command->info('Relance demo data seeded.');
    }
}
