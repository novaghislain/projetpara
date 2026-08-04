<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une piste d'audit (traçabilité).
 *
 * Enregistre toutes les modifications importantes effectuées
 * sur les modèles du système. Utilise une relation polymorphe
 * (auditable) pour s'attacher à n'importe quel modèle.
 * Stocke les anciennes et nouvelles valeurs pour tracer
 * chaque changement.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client
 * @property int|null $user_id Identifiant de l'utilisateur
 * @property string $event Type d'événement (created, updated, deleted, etc.)
 * @property string $auditable_type Type du modèle audité
 * @property int $auditable_id Identifiant de l'enregistrement audité
 * @property array|null $old_values Anciennes valeurs
 * @property array|null $new_values Nouvelles valeurs
 * @property string|null $ip_address Adresse IP
 * @property string|null $user_agent User agent du navigateur
 * @property string|null $description Description de l'action
 *
 * @property-read User|null $user Utilisateur à l'origine de l'action
 * @property-read \Illuminate\Database\Eloquent\Model $auditable Modèle audité (polymorphe)
 *
 * @table audit_trails
 */
class AuditTrail extends Model
{
    protected $fillable = [
        'client_id', 'user_id', 'event', 'auditable_type',
        'auditable_id', 'old_values', 'new_values',
        'ip_address', 'user_agent', 'description',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'json',
            'new_values' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
