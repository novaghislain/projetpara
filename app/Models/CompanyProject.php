<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle représentant un projet d'entreprise cliente.
 *
 * Table associée : `company_projects` (via convention Laravel)
 *
 * Relations :
 * - Un projet peut avoir plusieurs tâches (CompanyProjectTask)
 * - Un projet est créé par un utilisateur (User)
 */
class CompanyProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
        'budget',
        'progress',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'budget'     => 'decimal:2',
        'progress'   => 'integer',
    ];

    /**
     * Tâches du projet.
     */
    public function tasks()
    {
        return $this->hasMany(CompanyProjectTask::class, 'project_id');
    }

    /**
     * Utilisateur qui a créé le projet.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope pour filtrer par client.
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
