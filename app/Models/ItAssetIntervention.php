<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle ItAssetIntervention (Intervention sur équipement IT).
 *
 * Enregistre les interventions de maintenance effectuées sur un équipement
 * informatique : date, technicien, durée, type d'intervention et coût.
 *
 * @property int $id
 * @property int $asset_id ID de l'équipement concerné
 * @property int|null $ticket_id ID du ticket associé
 * @property int|null $technician_id ID du technicien
 * @property string $type Type d'intervention (maintenance, réparation, installation, etc.)
 * @property \Carbon\Carbon $date Date de l'intervention
 * @property int $duration_minutes Durée en minutes
 * @property string|null $description Description détaillée
 * @property float|null $cost Coût de l'intervention
 *
 * @property-read \App\Models\ItAsset $asset Équipement concerné
 * @property-read \App\Models\ItTicket|null $ticket Ticket associé
 * @property-read \App\Models\User|null $technician Technicien intervenant
 */
class ItAssetIntervention extends Model
{
    protected $fillable = [
        'asset_id', 'ticket_id', 'technician_id', 'type',
        'date', 'duration_minutes', 'description', 'cost',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'cost' => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    public function asset(): BelongsTo { return $this->belongsTo(ItAsset::class); }
    public function ticket(): BelongsTo { return $this->belongsTo(ItTicket::class); }
    public function technician(): BelongsTo { return $this->belongsTo(User::class, 'technician_id'); }
}
