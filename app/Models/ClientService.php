<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant la souscription d'un service par un client.
 *
 * Table pivot entre les clients et les services proposés.
 * Enregistre le statut et les dates de début et fin de la
 * souscription, ainsi que les paramètres spécifiques.
 *
 * @property int $id
 * @property int $client_id Identifiant du client
 * @property int $service_id Identifiant du service
 * @property string $status Statut (actif, inactif, suspendu)
 * @property string|null $start_date Date de début
 * @property string|null $end_date Date de fin
 * @property mixed|null $settings Paramètres (JSON)
 *
 * @property-read Client $client Client associé
 * @property-read Service $service Service associé
 *
 * @table client_service
 */
class ClientService extends Model
{
    protected $table = 'client_service';

    protected $fillable = [
        'client_id', 'service_id', 'status', 'start_date', 'end_date', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'settings' => 'json',
        ];
    }

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
