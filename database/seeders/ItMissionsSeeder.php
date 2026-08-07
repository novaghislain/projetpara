<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gel\ItMission;
use App\Models\Gel\ItIntervention;
use App\Models\Gel\ItEquipmentOrder;
use App\Models\User;
use App\Models\Client;
use Carbon\Carbon;

class ItMissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $informaticiens = User::role('informaticien')->get();
        $clients = Client::all();

        if ($informaticiens->isEmpty() || $clients->isEmpty()) {
            return;
        }

        $info = $informaticiens->first();
        $client = $clients->first();

        // 1. Mission de Sécurité
        $mission1 = ItMission::create([
            'client_id' => $client->id,
            'type' => 'securite',
            'subject' => 'Audit de sécurité et mise en place EDR',
            'description' => 'Le client souhaite un audit complet de son parc et l\'installation d\'un EDR sur tous les postes.',
            'volume' => '50 postes, 2 serveurs',
            'status' => 'en_cours',
        ]);
        $mission1->informaticiens()->attach($info->id);

        ItIntervention::create([
            'it_mission_id' => $mission1->id,
            'informaticien_id' => $info->id,
            'description' => 'Audit initial du réseau local.',
            'scheduled_at' => Carbon::now()->subDays(2),
            'completed_at' => Carbon::now()->subDays(2),
            'status' => 'realisee',
        ]);
        ItIntervention::create([
            'it_mission_id' => $mission1->id,
            'informaticien_id' => $info->id,
            'description' => 'Déploiement de l\'EDR sur le premier lot de postes.',
            'scheduled_at' => Carbon::now()->addDays(1),
            'status' => 'programmee',
        ]);

        // 2. Mission de Maintenance
        $mission2 = ItMission::create([
            'client_id' => $client->id,
            'type' => 'maintenance',
            'subject' => 'Contrat de maintenance préventive',
            'description' => 'Passage mensuel pour vérification du matériel et mises à jour.',
            'volume' => '1 site, 15 postes',
            'status' => 'en_cours',
        ]);
        $mission2->informaticiens()->attach($info->id);

        // 3. Commande de matériel
        ItEquipmentOrder::create([
            'client_id' => $client->id,
            'informaticien_id' => $info->id,
            'order_number' => 'CMD-IT-X8J9K2-' . date('my'),
            'items' => [
                '2x Ordinateurs portables Dell XPS 15',
                '1x Switch Cisco 24 ports',
                '3x Écrans 27 pouces IIyama'
            ],
            'status' => 'valide', // devis_envoye, valide, approvisionnement...
        ]);
        
        ItEquipmentOrder::create([
            'client_id' => $client->id,
            'informaticien_id' => $info->id,
            'order_number' => 'CMD-IT-L9P2M1-' . date('my'),
            'items' => [
                '10x Licences Office 365 Business Premium',
            ],
            'status' => 'recue',
        ]);
        
        // 4. Mission en attente (pour le dispatcher)
        ItMission::create([
            'client_id' => $client->id,
            'type' => 'developpement',
            'subject' => 'Création d\'un portail intranet',
            'description' => 'Le client souhaite un outil interne pour gérer ses congés et notes de frais.',
            'volume' => 'Application web sur mesure',
            'status' => 'en_attente',
        ]);
    }
}
