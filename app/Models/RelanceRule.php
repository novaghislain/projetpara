<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle RelanceRule - Règle de relance client.
 *
 * Table associée : 'relance_rules' (convention Laravel).
 * Définit les règles de relance automatique pour les impayés :
 * délai de déclenchement, canal de communication, modèle de message.
 * Relations :
 * - client() : appartient à un client (Client).
 */
class RelanceRule extends Model
{
    protected $fillable = [
        'client_id', 'name', 'trigger_days', 'channel',
        'template_subject', 'template_body', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
