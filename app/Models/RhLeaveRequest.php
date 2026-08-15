<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhLeaveRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rh_leave_requests';

    protected $fillable = [
        'client_id',
        'employee_id',
        'type',
        'date_debut',
        'date_fin',
        'duree_jours',
        'motif',
        'statut',
        'approbateur_id',
        'notes_approbateur',
        'date_approbation'
    ];

}
