<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle ItAsset (Équipement IT).
 *
 * Gère l'inventaire des équipements informatiques (ordinateurs, serveurs,
 * imprimantes, etc.) avec leurs caractéristiques techniques, garanties,
 * et dates de maintenance prévues.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $asset_tag Code-barre / Tag d'identification
 * @property string $name Nom de l'équipement
 * @property string $category Catégorie (ordinateur, serveur, imprimante, etc.)
 * @property string|null $brand Marque
 * @property string|null $model Modèle
 * @property string|null $serial_number Numéro de série
 * @property string $status Statut (en_service, en_maintenance, hors_service, stock)
 * @property int|null $assigned_to_user ID de l'utilisateur assigné
 * @property string|null $location Localisation
 * @property \Carbon\Carbon|null $purchase_date Date d'achat
 * @property float|null $purchase_price Prix d'achat
 * @property \Carbon\Carbon|null $warranty_expires_at Date d'expiration de garantie
 * @property \Carbon\Carbon|null $next_maintenance_at Prochaine maintenance prévue
 * @property string|null $os_version Version du système d'exploitation
 * @property string|null $ip_address Adresse IP
 * @property string|null $mac_address Adresse MAC
 * @property string|null $notes Notes
 * @property string|null $photo Photo de l'équipement
 *
 * @property-read \App\Models\Client $client Client propriétaire
 * @property-read \App\Models\User|null $assignedTo Utilisateur assigné
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ItAssetLicense[] $licenses Licences associées
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ItAssetIntervention[] $interventions Interventions associées
 */
class ItAsset extends Model
{
    protected $fillable = [
        'client_id', 'asset_tag', 'name', 'category', 'brand',
        'model', 'serial_number', 'status', 'assigned_to_user',
        'location', 'purchase_date', 'purchase_price',
        'warranty_expires_at', 'next_maintenance_at',
        'os_version', 'ip_address', 'mac_address', 'notes', 'photo',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expires_at' => 'date',
            'next_maintenance_at' => 'date',
            'purchase_price' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to_user'); }
    public function licenses(): HasMany { return $this->hasMany(ItAssetLicense::class, 'asset_id'); }
    public function interventions(): HasMany { return $this->hasMany(ItAssetIntervention::class, 'asset_id'); }
}
