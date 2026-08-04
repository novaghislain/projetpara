<?php

namespace App\Models\Rh;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle RhPayroll - Fiche de paie / bulletin de salaire.
 *
 * Table associée : 'rh_payrolls'.
 * Gère les bulletins de salaire : salaire de base, primes, indemnités,
 * cotisations, retenues, avance, net à payer, et statut de paiement.
 * Relations :
 * - employee() : appartient à un employé (RhEmployee).
 * - valideur() : appartient à l'utilisateur (User) qui a validé la paie.
 * - creator() : appartient à l'utilisateur (User) qui a créé la fiche.
 */
class RhPayroll extends RhBaseModel
{
    use SoftDeletes;

    protected $table = 'rh_payrolls';

    protected $fillable = [
        'employee_id', 'periode', 'salaire_base', 'primes', 'indemnites',
        'cotisations', 'avance', 'net_a_payer', 'retenues',
        'date_paiement', 'statut', 'valide_par', 'created_by',
    ];

    protected $casts = [
        'salaire_base' => 'decimal:2',
        'avance'       => 'decimal:2',
        'net_a_payer'  => 'decimal:2',
        'primes'       => 'json',
        'indemnites'   => 'json',
        'cotisations'  => 'json',
        'retenues'     => 'json',
        'date_paiement' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(RhEmployee::class, 'employee_id');
    }

    public function valideur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
