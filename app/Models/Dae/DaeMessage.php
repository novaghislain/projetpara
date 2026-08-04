<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un message interne dans le module DAE.
 *
 * Table associée : `dae_messages`
 *
 * Permet la messagerie interne avec suivi des statuts (lu, traité),
 * gestion des urgences et type de message (appel, notification, etc.).
 */
class DaeMessage extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_messages';

    protected $fillable = [
        'client_id', 'type', 'expediteur_name', 'expediteur_entreprise',
        'expediteur_contact', 'destinataire_id', 'objet', 'contenu',
        'urgence', 'statut', 'lu_at', 'traite_at', 'appel_rappele', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'lu_at' => 'datetime',
            'traite_at' => 'datetime',
            'appel_rappele' => 'boolean',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'messages';
    }

    // ─── Relations ────────────────────────────────────────

    public function destinataire()
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeNonLus($query)
    {
        return $query->whereIn('statut', ['recu']);
    }

    public function scopeUrgents($query)
    {
        return $query->where('urgence', 'urgent')->where('statut', '!=', 'archive');
    }

    public function scopeRecents($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
