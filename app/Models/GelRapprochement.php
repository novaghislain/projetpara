<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelRapprochement extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_rapprochements';

    protected $fillable = [
        'client_id',
        'bank_account_id',
        'date_debut',
        'date_fin',
        'statut',
        'solde_releve',
        'solde_comptable',
        'ecart'
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id', 'id');
    }

}
