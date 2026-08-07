<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Task (Tâche).
 *
 * Gère les tâches à réaliser dans le cadre du suivi de mission.
 * Une tâche est assignée à un utilisateur avec une priorité, une date d'échéance,
 * et un statut d'avancement (a_faire, en_cours, termine).
 * Table associée : `gel_tasks`.
 * Supporte la suppression douce (SoftDeletes).
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property int $client_id ID du client
 * @property int $assigned_to ID de l'utilisateur assigné
 * @property int $created_by ID du créateur
 * @property string $titre Titre de la tâche
 * @property string|null $description Description détaillée
 * @property string $priorite Priorité (basse, moyenne, haute, critique)
 * @property string $statut Statut (a_faire, en_cours, termine)
 * @property \Carbon\Carbon|null $date_echeance Date d'échéance
 * @property \Carbon\Carbon|null $termine_at Date de réalisation
 *
 * @property-read \App\Models\Gel\Cabinet $cabinet Cabinet associé
 * @property-read \App\Models\Gel\Client $client Client associé
 * @property-read \App\Models\User $assigne Utilisateur assigné
 * @property-read \App\Models\User $createur Utilisateur créateur
 */
class Task extends Model
{
    use SoftDeletes;

    protected $table = 'gel_tasks';

    protected $fillable = [
        'cabinet_id', 'client_id', 'assigned_to', 'created_by',
        'titre', 'description', 'priorite', 'statut', 'date_echeance', 'termine_at',
        // S2.2 / S3.1 / S3.3 — coordination inter-personnel
        'source', 'coordination_type', 'related_document_id',
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'termine_at' => 'datetime',
    ];

    // ─── Relations ───
    public function cabinet() { return $this->belongsTo(Cabinet::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function assigne() { return $this->belongsTo(\App\Models\User::class, 'assigned_to'); }
    public function createur() { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
    
    public function comments() { return $this->hasMany(TaskComment::class); }
    public function attachments() { return $this->hasMany(TaskAttachment::class); }

    // ─── Scopes ───
    public function scopeAFaire($q) { return $q->where('statut', 'a_faire'); }
    public function scopeEnCours($q) { return $q->where('statut', 'en_cours'); }
    public function scopeTermine($q) { return $q->where('statut', 'termine'); }
    public function scopeHautePriorite($q) { return $q->whereIn('priorite', ['haute', 'critique']); }
    public function scopeEcheance($q) { return $q->whereNotNull('date_echeance')->where('date_echeance', '<=', now()); }
}
