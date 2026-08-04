<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle ItMaintenanceContract (Contrat de maintenance IT).
 *
 * Gère les contrats de maintenance des équipements informatiques.
 * Définit la période de couverture, le montant mensuel, les heures
 * incluses, les délais d'intervention (SLA), et les actifs couverts.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $reference Référence du contrat
 * @property string $title Titre du contrat
 * @property string $type Type de contrat
 * @property string $status Statut (actif, expire, resilie)
 * @property \Carbon\Carbon $start_date Date de début
 * @property \Carbon\Carbon|null $end_date Date de fin
 * @property float $monthly_amount Montant mensuel
 * @property int $included_hours Heures incluses par mois
 * @property int $response_time_hours Délai d'intervention (heures)
 * @property string|null $coverage_hours Période de couverture
 * @property array|null $covered_assets Actifs couverts (JSON)
 * @property bool $auto_renew Renouvellement automatique
 * @property string|null $notes Notes
 *
 * @property-read \App\Models\Client $client Client associé
 */
class ItMaintenanceContract extends Model
{
    protected $fillable = [
        'client_id', 'reference', 'title', 'type', 'status',
        'start_date', 'end_date', 'monthly_amount', 'included_hours',
        'response_time_hours', 'coverage_hours', 'covered_assets',
        'auto_renew', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_amount' => 'decimal:2',
            'included_hours' => 'integer',
            'response_time_hours' => 'integer',
            'covered_assets' => 'array',
            'auto_renew' => 'boolean',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
