<?php

namespace App\Models\Rh;

use Illuminate\Database\Eloquent\SoftDeletes;

class RhAttendance extends RhBaseModel
{
    use SoftDeletes;

    protected $table = 'rh_attendance';

    protected $fillable = [
        'employee_id', 'date', 'heure_arrivee', 'heure_depart',
        'pauses', 'heures_travaillees', 'type_presence', 'justificatif', 'notes',
    ];

    protected $casts = [
        'date'              => 'date',
        'heure_arrivee'     => 'string',
        'heure_depart'      => 'string',
        'pauses'            => 'json',
        'heures_travaillees' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(RhEmployee::class, 'employee_id');
    }
}
