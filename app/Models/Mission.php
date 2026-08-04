<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Mission (Mission).
 *
 * Gère les missions confiées aux équipes, avec suivi de l'avancement,
 * priorité, dates, et collaborateurs assignés.
 * Supporte la suppression douce (SoftDeletes).
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property int $pole_id ID du pôle/dept
 * @property string $title Titre de la mission
 * @property string|null $description Description détaillée
 * @property string $type Type (conseil, audit, expertise, etc.)
 * @property string $status Statut (planifiée, en_cours, terminée, annulée)
 * @property string $priority Priorité (basse, moyenne, haute, critique)
 * @property \Carbon\Carbon|null $start_date Date de début
 * @property \Carbon\Carbon|null $due_date Date d'échéance
 * @property int $progress Pourcentage d'avancement
 * @property int $assigned_to ID du responsable
 * @property int $created_by ID du créateur
 *
 * @property-read \App\Models\Client $client Client associé
 * @property-read \App\Models\Pole $pole Pôle associé
 * @property-read \App\Models\User $assignedTo Responsable assigné
 * @property-read \App\Models\User $createdBy Créateur
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $collaborators Collaborateurs de la mission
 */
class Mission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'pole_id',
        'title',
        'description',
        'type',
        'status',
        'priority',
        'start_date',
        'due_date',
        'progress',
        'assigned_to',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'progress' => 'integer',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'mission_user')
            ->withPivot('role')
            ->withTimestamps();
    }
}
