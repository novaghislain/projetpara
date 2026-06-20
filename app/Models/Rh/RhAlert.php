<?php

namespace App\Models\Rh;

class RhAlert extends RhBaseModel
{
    protected $table = 'rh_alerts';

    protected $fillable = [
        'client_id', 'employee_id', 'type', 'titre', 'description',
        'date_echeance', 'date_declenchement', 'statut', 'days_before',
    ];

    protected $casts = [
        'date_echeance'      => 'date',
        'date_declenchement' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(RhEmployee::class, 'employee_id');
    }

    public function scopeActives($query)
    {
        return $query->where('statut', 'active');
    }
}
