<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle représentant une tâche liée à un projet.
 *
 * Table associée : `company_project_tasks` (via convention Laravel)
 *
 * Relations :
 * - Une tâche appartient à un projet (CompanyProject)
 */
class CompanyProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'assigned_to',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * Projet parent.
     */
    public function project()
    {
        return $this->belongsTo(CompanyProject::class, 'project_id');
    }
}
