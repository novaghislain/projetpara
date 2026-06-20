<?php

namespace App\Models\Rh;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhLeaveRequest extends RhBaseModel
{
    use SoftDeletes;

    protected $table = 'rh_leave_requests';

    protected $fillable = [
        'employee_id', 'type', 'date_debut', 'date_fin', 'duree_jours',
        'motif', 'statut', 'approbateur_id', 'notes_approbateur', 'date_approbation',
    ];

    protected $casts = [
        'date_debut'       => 'date',
        'date_fin'         => 'date',
        'date_approbation' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(RhEmployee::class, 'employee_id');
    }

    public function approbateur()
    {
        return $this->belongsTo(User::class, 'approbateur_id');
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', 'approved')
            ->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now());
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'pending');
    }
}
