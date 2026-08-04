<?php

namespace App\Models\Rh;

/**
 * Modèle RhAlert - Alerte RH (rappel de contrat, visite médicale, etc.).
 *
 * Table associée : 'rh_alerts'.
 * Permet de créer des alertes liées aux employés (fin de contrat,
 * date d'échéance, etc.) avec un déclenchement avant la date limite.
 * Relations :
 * - employee() : appartient à un employé (RhEmployee).
 */
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
