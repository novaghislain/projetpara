<?php

namespace App\Models\Rh;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle RhExpense - Note de frais d'un employé.
 *
 * Table associée : 'rh_expenses'.
 * Gère les notes de frais : catégorie, montant, justificatif,
 * statut (en attente/approuvé/refusé) et approbation.
 * Relations :
 * - employee() : appartient à un employé (RhEmployee).
 * - approbateur() : appartient à l'utilisateur (User) qui a approuvé la note.
 */
class RhExpense extends RhBaseModel
{
    use SoftDeletes;

    protected $table = 'rh_expenses';

    protected $fillable = [
        'employee_id', 'categorie', 'montant', 'description',
        'justificatif_url', 'statut', 'approbateur_id',
        'date_approbation', 'date_paiement',
    ];

    protected $casts = [
        'montant'          => 'decimal:2',
        'date_approbation' => 'datetime',
        'date_paiement'    => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(RhEmployee::class, 'employee_id');
    }

    public function approbateur()
    {
        return $this->belongsTo(User::class, 'approbateur_id');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'pending');
    }
}
