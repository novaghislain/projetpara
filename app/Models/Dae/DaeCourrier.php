<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeCourrier extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_courriers';

    protected $fillable = [
        'client_id', 'reference', 'expediteur', 'destinataire',
        'type', 'mode', 'objet', 'contenu', 'urgence', 'statut',
        'date_courrier', 'date_reception', 'date_envoi', 'date_traitement', 'reponse',
        'traite_par', 'assigned_to', 'fichier_joint', 'tags', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'date_courrier' => 'datetime',
            'date_reception' => 'datetime',
            'date_envoi' => 'datetime',
            'date_traitement' => 'datetime',
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
