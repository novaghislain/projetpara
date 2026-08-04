<?php

namespace App\Models\Rh;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle RhTraining - Formation suivie par un employé.
 *
 * Table associée : 'rh_trainings'.
 * Enregistre les formations : titre, organisme, dates, durée, coût,
 * type, certificat et statut (planifiée/terminée/annulée).
 * Relations :
 * - employee() : appartient à un employé (RhEmployee).
 */
class RhTraining extends RhBaseModel
{
    use SoftDeletes;

    protected $table = 'rh_trainings';

    protected $fillable = [
        'employee_id', 'titre', 'organisme', 'date_debut', 'date_fin',
        'duree_heures', 'cout', 'type', 'certificat_url', 'statut', 'notes',
    ];

    protected $casts = [
        'date_debut'    => 'date',
        'date_fin'      => 'date',
        'cout'          => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(RhEmployee::class, 'employee_id');
    }
}
