<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'gel_subscriptions';

    protected $fillable = [
        'cabinet_id', 'formule', 'statut', 'date_debut', 'date_fin',
        'stripe_id', 'montant', 'devise', 'max_users', 'max_clients', 'features',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant' => 'decimal:2',
        'features' => 'array',
    ];

    // ─── Relations ───
    public function cabinet() { return $this->belongsTo(Cabinet::class); }

    // ─── Scopes ───
    public function scopeActif($q) { return $q->where('statut', 'actif'); }
    public function scopeByFormule($q, $f) { return $q->where('formule', $f); }

    // ─── Helpers ───
    public function estActif(): bool { return $this->statut === 'actif'; }
    public function estExpire(): bool { return $this->date_fin && $this->date_fin->isPast(); }
    public function joursRestants(): int { return $this->date_fin ? now()->diffInDays($this->date_fin, false) : 0; }
}
