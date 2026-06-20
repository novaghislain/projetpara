<?php

namespace App\Models\Rh;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhContract extends RhBaseModel
{
    use SoftDeletes;

    protected $table = 'rh_contracts';

    protected $fillable = [
        'employee_id', 'reference', 'type', 'date_debut', 'date_fin',
        'duree_mois', 'poste', 'departement', 'salaire',
        'periode_essai_jours', 'renouvelable', 'date_fin_periode_essai',
        'statut', 'fichier_url', 'notes', 'created_by',
    ];

    protected $casts = [
        'date_debut'           => 'date',
        'date_fin'             => 'date',
        'date_fin_periode_essai' => 'date',
        'salaire'              => 'decimal:2',
        'renouvelable'         => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(RhEmployee::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeExpires($query)
    {
        return $query->where('statut', 'expire');
    }
}
