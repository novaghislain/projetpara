<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle ItSlaPolicy (Politique SLA).
 *
 * Définit les niveaux de service (SLA) pour le support IT :
 * délai de première réponse, délai de résolution, et priorité associée.
 * Une politique peut être définie comme politique par défaut.
 *
 * @property int $id
 * @property string $name Nom de la politique SLA
 * @property string $priority Priorité (basse, moyenne, haute, critique)
 * @property int $first_response_hours Délai de première réponse (heures)
 * @property int $resolution_hours Délai de résolution (heures)
 * @property bool $is_default Politique par défaut
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ItTicket[] $tickets Tickets associés
 */
class ItSlaPolicy extends Model
{
    protected $fillable = [
        'name', 'priority', 'first_response_hours',
        'resolution_hours', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'first_response_hours' => 'integer',
            'resolution_hours' => 'integer',
        ];
    }

    public function tickets(): HasMany { return $this->hasMany(ItTicket::class, 'sla_id'); }
}
