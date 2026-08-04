<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle représentant un contrat d'une entreprise cliente.
 *
 * Gère les contrats entre le cabinet et ses clients entreprises.
 * Chaque contrat a un type (prestation, maintenance, etc.),
 * une valeur, une période de validité et peut être signé
 * électroniquement avec un fichier attaché.
 *
 * @property int $id
 * @property int $client_id Identifiant du client (entreprise)
 * @property string $title Titre du contrat
 * @property string|null $reference Référence du contrat
 * @property string $type Type de contrat
 * @property string|null $party_name Nom de la partie contractante
 * @property string|null $party_contact Contact de la partie
 * @property string|null $description Description
 * @property string|null $start_date Date de début
 * @property string|null $end_date Date de fin
 * @property float|null $value Valeur du contrat
 * @property string $status Statut (brouillon, actif, expire, resilie)
 * @property string|null $file_path Chemin du fichier scanné
 * @property int|null $signed_by Identifiant du signataire
 * @property string|null $signed_at Date de signature
 * @property int|null $created_by Identifiant du créateur
 *
 * @property-read User|null $createdBy Créateur
 *
 * @table company_contracts
 */
class CompanyContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'title',
        'reference',
        'type',
        'party_name',
        'party_contact',
        'description',
        'start_date',
        'end_date',
        'value',
        'status',
        'file_path',
        'signed_by',
        'signed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'signed_at'  => 'datetime',
            'value'      => 'decimal:2',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
