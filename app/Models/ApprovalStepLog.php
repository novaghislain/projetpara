<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant l'historique des actions d'approbation.
 *
 * Enregistre chaque action effectuée par un approbateur sur une
 * demande d'approbation (approbation, rejet, commentaire).
 * Permet de tracer l'historique complet du processus de validation.
 *
 * @property int $id
 * @property int $request_id Identifiant de la demande d'approbation
 * @property int $step_number Numéro de l'étape
 * @property int $approver_id Identifiant de l'approbateur
 * @property string $action Action effectuée (approuve, rejete, commente)
 * @property string|null $comment Commentaire de l'approbateur
 * @property string|null $acted_at Date et heure de l'action
 *
 * @property-read ApprovalRequest $request Demande d'approbation associée
 * @property-read User $approver Approbateur
 *
 * @table approval_steps_log
 */
class ApprovalStepLog extends Model
{
    protected $table = 'approval_steps_log';
    public $timestamps = false;

    protected $fillable = [
        'request_id', 'step_number', 'approver_id',
        'action', 'comment', 'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'request_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
