<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un courrier (entrant/sortant) dans le module DAE.
 *
 * Table associée : `dae_courriers`
 *
 * Gère le suivi du courrier : expéditeur, destinataire, type, mode,
 * dates de réception/envoi/traitement, et la traçabilité complète.
 */
class DaeCourrier extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_courriers';

    protected $fillable = [
        'client_id', 'reference', 'expediteur', 'destinataire',
        'type', 'mode', 'objet', 'contenu', 'urgence', 'statut',
        'date_courrier', 'date_reception', 'date_envoi', 'date_traitement', 'reponse',
        'traite_par', 'assigned_to', 'fichier_joint', 'tags', 'created_by', 'updated_by',
        // Workflow
        'workflow_step', 'workflow_notes',
        'visa_par', 'visa_at', 'signe_par', 'signe_at', 'envoye_at', 'archive_at',
    ];

    // Workflow steps in order
    const WORKFLOW_STEPS = [
        'creation'   => ['label' => 'Création',   'icon' => 'fa-edit',          'color' => '#64748b'],
        'visa'       => ['label' => 'Visa',        'icon' => 'fa-check-circle',  'color' => '#3B82F6'],
        'validation' => ['label' => 'Validation',  'icon' => 'fa-user-check',    'color' => '#8B5CF6'],
        'signature'  => ['label' => 'Signature',   'icon' => 'fa-pen-nib',       'color' => '#F59E0B'],
        'envoi'      => ['label' => 'Envoi',        'icon' => 'fa-paper-plane',   'color' => '#0EA5E9'],
        'archive'    => ['label' => 'Archivage',   'icon' => 'fa-archive',       'color' => '#10B981'],
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'date_courrier' => 'datetime',
            'date_reception' => 'datetime',
            'date_envoi' => 'datetime',
            'date_traitement' => 'datetime',
            'visa_at' => 'datetime',
            'signe_at' => 'datetime',
            'envoye_at' => 'datetime',
            'archive_at' => 'datetime',
        ];
    }


    protected function getDaeModuleName(): string
    {
        return 'courriers';
    }

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeUrgents($query)
    {
        return $query->whereIn('urgence', ['urgent', 'tre_urgent'])
            ->whereNotIn('statut', ['traite', 'archive']);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeNonTraites($query)
    {
        return $query->whereNotIn('statut', ['traite', 'archive']);
    }

    public function scopeAssignesA($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}
