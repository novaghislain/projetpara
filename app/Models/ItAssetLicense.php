<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle ItAssetLicense (Licence logicielle).
 *
 * Gère les licences logicielles associées aux équipements informatiques.
 * Suit les informations de licence (clé, nombre de postes, fournisseur,
 * date d'expiration) et le coût d'acquisition.
 *
 * @property int $id
 * @property int $asset_id ID de l'équipement associé
 * @property int $client_id ID du client propriétaire
 * @property string $software_name Nom du logiciel
 * @property string|null $license_key Clé de licence
 * @property int $seats Nombre de postes autorisés
 * @property \Carbon\Carbon|null $expires_at Date d'expiration
 * @property string|null $vendor Fournisseur / Éditeur
 * @property float|null $purchase_price Prix d'achat
 * @property string|null $notes Notes
 *
 * @property-read \App\Models\ItAsset $asset Équipement associé
 * @property-read \App\Models\Client $client Client propriétaire
 */
class ItAssetLicense extends Model
{
    protected $fillable = [
        'asset_id', 'client_id', 'software_name', 'license_key',
        'seats', 'expires_at', 'vendor', 'purchase_price', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'purchase_price' => 'decimal:2',
            'seats' => 'integer',
        ];
    }

    public function asset(): BelongsTo { return $this->belongsTo(ItAsset::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
