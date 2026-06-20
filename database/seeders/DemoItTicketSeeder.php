<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ItSlaPolicy;
use App\Models\ItTicket;
use App\Models\ItAsset;
use App\Models\ItMaintenanceContract;
use App\Models\ItKnowledgeBase;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoItTicketSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::where('status', 'actif')->get();
        $techs = User::where('role', 'super_admin')->orWhere('is_admin', true)->get();

        if ($clients->isEmpty() || $techs->isEmpty()) return;

        // SLA Policies
        ItSlaPolicy::create(['name' => 'Standard', 'priority' => 'medium',  'first_response_hours' => 8,  'resolution_hours' => 48, 'is_default' => true]);
        ItSlaPolicy::create(['name' => 'Urgent',   'priority' => 'high',    'first_response_hours' => 2,  'resolution_hours' => 8]);
        ItSlaPolicy::create(['name' => 'Critique', 'priority' => 'critical','first_response_hours' => 1,  'resolution_hours' => 4]);

        // Tickets
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];
        foreach (range(1, 10) as $i) {
            ItTicket::create([
                'client_id'     => $clients->random()->id,
                'ticket_number' => 'TK-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
                'title'         => fake()->sentence(6),
                'description'   => fake()->paragraph(),
                'type'          => fake()->randomElement(['incident', 'request', 'change']),
                'priority'      => fake()->randomElement(['low', 'medium', 'high', 'critical']),
                'status'        => $statuses[array_rand($statuses)],
                'assigned_to'   => $techs->random()->id,
                'requested_by'  => $techs->random()->id,
                'category'      => fake()->randomElement(['Hardware', 'Software', 'Réseau', 'Email', 'Autre']),
            ]);
        }

        // Assets IT
        foreach (['Serveur HP ProLiant', 'Station Dell OptiPlex', 'Routeur Cisco', 'Firewall Fortinet'] as $name) {
            ItAsset::create([
                'client_id'     => $clients->random()->id,
                'asset_tag'     => 'AST-' . strtoupper(fake()->bothify('??####')),
                'name'          => $name,
                'category'      => fake()->randomElement(['computer', 'server', 'printer', 'network', 'mobile', 'software', 'other']),
                'brand'         => fake()->randomElement(['Dell', 'HP', 'Lenovo', 'Cisco']),
                'model'         => fake()->bothify('??-####'),
                'serial_number' => strtoupper(fake()->bothify('SN-####-????')),
                'status'        => 'active',
                'purchase_date' => fake()->dateTimeBetween('-3 years', '-1 month'),
                'purchase_price'=> fake()->randomFloat(2, 200, 5000),
                'location'      => fake()->randomElement(['Siège', 'Agence Cotonou']),
            ]);
        }

        // Maintenance contract
        ItMaintenanceContract::create([
            'client_id'       => $clients->first()->id,
            'title'           => 'Contrat maintenance premium',
            'reference'       => 'MC-' . now()->format('Ymd') . '-001',
            'type'            => 'preventive',
            'start_date'      => now()->subMonths(2),
            'end_date'        => now()->addMonths(10),
            'monthly_amount'  => 150000,
            'status'          => 'active',
        ]);

        // Knowledge base
        foreach (['Configurer Outlook', 'Réinitialiser mot de passe VPN', 'Procédure sauvegarde NAS', 'Guide Teams'] as $title) {
            ItKnowledgeBase::create([
                'title'    => $title,
                'content'  => fake()->paragraphs(3, true),
                'category' => fake()->randomElement(['Guide', 'FAQ', 'Procédure']),
                'slug'     => \Illuminate\Support\Str::slug($title),
            ]);
        }

        $this->command->info('IT Demo data seeded.');
    }
}
