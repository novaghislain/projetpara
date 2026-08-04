<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un contact d'une entreprise cliente.
 *
 * Stocke les informations de contact (nom, poste, téléphone, email)
 * pour les personnes à contacter dans une entreprise cliente.
 * Un contact peut être marqué comme principal.
 *
 * @property int $id
 * @property int $client_id Identifiant du client
 * @property string $name Nom du contact
 * @property string|null $position Poste / fonction
 * @property string|null $phone Téléphone
 * @property string|null $email Email
 * @property bool $is_primary Contact principal
 *
 * @property-read Client $client Client associé
 *
 * @table client_contacts
 */
class ClientContact extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'position',
        'phone',
        'email',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
