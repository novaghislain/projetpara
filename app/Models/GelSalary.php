<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelSalary extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_salaries';

    protected $fillable = [
        'client_id',
        'nom',
        'prenom',
        'matricule',
        'poste',
        'salaire_base',
        'date_embauche',
        'statut'
    ];

    public function rhAttendance()
    {
        return $this->hasMany(RhAttendance::class, 'salarie_id', 'id');
    }

    public function rhContracts()
    {
        return $this->hasMany(RhContract::class, 'salarie_id', 'id');
    }

    public function rhExpenses()
    {
        return $this->hasMany(RhExpense::class, 'salarie_id', 'id');
    }

}
