<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle LegalAuditLog (Journal d'audit légal).
 *
 * Enregistre toutes les actions effectuées sur les entités du module juridique
 * (création, modification, validation d'actes, de contrats, etc.)
 * avec les détails des changements au format JSON.
 * Table associée : `legal_audit_log`.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $model_type Type du modèle audité
 * @property int $model_id ID du modèle audité
 * @property string $action Action effectuée (created, updated, deleted, validated, etc.)
 * @property int $user_id ID de l'utilisateur à l'origine
 * @property array|null $details Détails des changements (JSON)
 */
class LegalAuditLog extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_audit_log';
    public $timestamps = true;

    protected $fillable = [
        'client_id', 'model_type', 'model_id',
        'action', 'user_id', 'details',
    ];

    protected $casts = [
        'details' => 'json',
    ];
}
