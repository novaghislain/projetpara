<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un workflow d'approbation.
 *
 * Définit les règles et étapes d'approbation pour différents types
 * de modèles (ex: devis, factures, contrats). Chaque workflow est
 * lié à un client (entreprise) et contient les étapes de validation
 * (steps) et les conditions de déclenchement (trigger_condition).
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $name Nom du workflow
 * @property string $trigger_model Modèle déclencheur (ex: Quote, Invoice)
 * @property array|null $trigger_condition Conditions de déclenchement
 * @property array $steps Étapes d'approbation (JSON)
 * @property bool $is_active Indique si le workflow est actif
 *
 * @property-read Client|null $client Client associé
 * @property-read \Illuminate\Database\Eloquent\Collection|ApprovalRequest[] $requests Demandes d'approbation
 *
 * @table approval_workflows
 */
class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'client_id', 'name', 'trigger_model',
        'trigger_condition', 'steps', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'trigger_condition' => 'array',
            'steps' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function requests(): HasMany { return $this->hasMany(ApprovalRequest::class, 'workflow_id'); }
}
