<?php

namespace App\Models\Rh;

use Illuminate\Database\Eloquent\SoftDeletes;

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
