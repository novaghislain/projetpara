<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhAttendance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rh_attendance';

    protected $fillable = [
        'client_id',
        'salarie_id',
        'date_presence',
        'heure_arrivee',
        'heure_depart',
        'statut'
    ];

    public function salarie()
    {
        return $this->belongsTo(GelSalary::class, 'salarie_id', 'id');
    }

}
