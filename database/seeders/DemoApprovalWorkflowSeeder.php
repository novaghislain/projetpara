<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ApprovalWorkflow;
use Illuminate\Database\Seeder;

class DemoApprovalWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::where('status', 'actif')->get();
        if ($clients->isEmpty()) return;

        ApprovalWorkflow::create([
            'client_id'         => $clients->first()->id,
            'name'              => 'Validation facture > 500 000',
            'trigger_model'     => 'App\Models\ErpInvoice',
            'trigger_condition' => ['field' => 'total_ttc', 'operator' => '>', 'value' => 500000],
            'steps' => [
                ['step' => 1, 'name' => 'Validation comptable', 'type' => 'any', 'approvers' => []],
                ['step' => 2, 'name' => 'Approbation direction', 'type' => 'any', 'approvers' => []],
            ],
            'is_active' => true,
        ]);

        ApprovalWorkflow::create([
            'client_id'         => $clients->first()->id,
            'name'              => 'Validation devis client',
            'trigger_model'     => 'App\Models\Devis',
            'trigger_condition' => ['field' => 'total_ttc', 'operator' => '>', 'value' => 10000],
            'steps' => [
                ['step' => 1, 'name' => 'Validation commerciale', 'type' => 'any', 'approvers' => []],
            ],
            'is_active' => true,
        ]);

        $this->command->info('Approval workflow demo data seeded.');
    }
}
