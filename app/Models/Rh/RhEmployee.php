<?php

namespace App\Models\Rh;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhEmployee extends RhBaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'matricule', 'civilite', 'nom', 'prenom', 'email', 'phone',
        'adresse', 'date_naissance', 'lieu_naissance', 'nationalite',
        'situation_matrimoniale', 'nombre_enfants',
        'poste', 'departement', 'date_embauche', 'date_depart',
        'type_contrat', 'salaire_base',
        'cnss_number', 'ifu_number', 'banque', 'iban',
        'urgence_nom', 'urgence_phone', 'photo', 'status', 'created_by',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_embauche'  => 'date',
        'date_depart'    => 'date',
        'salaire_base'   => 'decimal:2',
        'nombre_enfants' => 'integer',
    ];

    public function contracts()
    {
        return $this->hasMany(RhContract::class, 'employee_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(RhLeaveRequest::class, 'employee_id');
    }

    public function expenses()
    {
        return $this->hasMany(RhExpense::class, 'employee_id');
    }

    public function payrolls()
    {
        return $this->hasMany(RhPayroll::class, 'employee_id');
    }

    public function attendance()
    {
        return $this->hasMany(RhAttendance::class, 'employee_id');
    }

    public function trainings()
    {
        return $this->hasMany(RhTraining::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFullNameAttribute()
    {
        return trim($this->prenom . ' ' . $this->nom);
    }
}
