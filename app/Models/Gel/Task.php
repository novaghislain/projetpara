<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $table = 'gel_tasks';

    protected $fillable = [
        'cabinet_id', 'client_id', 'assigned_to', 'created_by',
        'titre', 'description', 'priorite', 'statut', 'date_echeance', 'termine_at',
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

    // ─── Scopes ───
    public function scopeAFaire($q) { return $q->where('statut', 'a_faire'); }
    public function scopeEnCours($q) { return $q->where('statut', 'en_cours'); }
    public function scopeTermine($q) { return $q->where('statut', 'termine'); }
    public function scopeHautePriorite($q) { return $q->whereIn('priorite', ['haute', 'critique']); }
    public function scopeEcheance($q) { return $q->whereNotNull('date_echeance')->where('date_echeance', '<=', now()); }
}
