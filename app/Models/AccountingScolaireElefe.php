<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingScolaireElefe extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'accounting_scolaire_eleves';

    protected $fillable = [
        'client_id',
        'nom',
        'prenom',
        'classe',
        'matricule',
        'statut'
    ];

    public function accountingScolaireFactures()
    {
        return $this->hasMany(AccountingScolaireFacture::class, 'eleve_id', 'id');
    }

}
