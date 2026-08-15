<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhExpense extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rh_expenses';

    protected $fillable = [
        'client_id',
        'salarie_id',
        'categorie',
        'montant',
        'date_depense',
        'statut',
        'justificatif_path'
    ];

    public function salarie()
    {
        return $this->belongsTo(GelSalary::class, 'salarie_id', 'id');
    }

}
