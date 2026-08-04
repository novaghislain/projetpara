<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant une demande d'approbation en cours.
 *
 * Chaque demande est liée à un workflow d'approbation et à un modèle
 * spécifique (via model_type/model_id) qui nécessite une validation.
 * Le champ current_step indique l'étape en cours, et le statut suit
 * le cycle de vie : en_attente -> approuve / rejete.
 *
 * @property int $id
 * @property int $workflow_id Identifiant du workflow d'approbation
 * @property string $model_type Type du modèle concerné
 * @property int $model_id Identifiant du modèle concerné
 * @property int $current_step Étape actuelle du processus
 * @property string $status Statut (en_attente, approuve, rejete)
 * @property int $requested_by Identifiant du demandeur
 * @property string|null $completed_at Date de complétion
 *
 * @property-read ApprovalWorkflow $workflow Workflow d'approbation associé
 * @property-read User $requester Utilisateur demandeur
 * @property-read \Illuminate\Database\Eloquent\Collection|ApprovalStepLog[] $stepsLog Historique des étapes
 *
 * @table approval_requests
 */
class ApprovalRequest extends Model
{
    protected $fillable = [
        'workflow_id', 'model_type', 'model_id', 'current_step',
        'status', 'requested_by', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo { return $this->belongsTo(ApprovalWorkflow::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function stepsLog(): HasMany { return $this->hasMany(ApprovalStepLog::class, 'request_id'); }
}
