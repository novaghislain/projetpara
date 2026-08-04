<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant la relation entre un client et un pôle.
 *
 * Table pivot entre les clients et les pôles d'activité.
 * Un pôle est un regroupement de compétences ou de services
 * (ex: Expertise Comptable, Commissariat aux Comptes, Juridique).
 *
 * @property int $id
 * @property int $client_id Identifiant du client
 * @property int $pole_id Identifiant du pôle
 * @property bool $is_active Pôle actif pour ce client
 *
 * @property-read Client $client Client associé
 * @property-read Pole $pole Pôle associé
 *
 * @table client_pole
 */
class ClientPole extends Model
{
    protected $table = 'client_pole';

    protected $fillable = [
        'client_id',
        'pole_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }
}
