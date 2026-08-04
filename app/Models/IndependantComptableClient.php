<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle IndependantComptableClient — Client propre d'un comptable indépendant (Modèle 3B).
 *
 * ISOLATION CRITIQUE : tous les accès à ce modèle DOIVENT filtrer sur `comptable_id`.
 * Ne jamais retourner des données sans ce filtre.
 *
 * Ce modèle représente une entreprise externe gérée par le comptable indépendant.
 * La relation est :
 *   - 1 comptable → N clients (IndependantComptableClient)
 *   - Chaque client est strictement isolé des clients des autres comptables
 */
class IndependantComptableClient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'independant_comptable_clients';

    protected $fillable = [
        'comptable_id',
        'nom_entreprise',
        'contact_nom',
        'email',
        'telephone',
        'adresse',
        'ifu',
        'rccm',
        'secteur',
        'notes',
        'type',
        'statut',
        'portal_contact_id',
        'invitation_token',
        'invitation_sent_at',
        'invitation_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'invitation_sent_at'     => 'datetime',
            'invitation_accepted_at' => 'datetime',
        ];
    }

    // ─── Relations ──────────────────────────────────────────────────────

    /** Le comptable propriétaire de ce client. */
    public function comptable()
    {
        return $this->belongsTo(User::class, 'comptable_id');
    }

    /** Le contact Portal (si le client a un compte sur la plateforme). */
    public function portalContact()
    {
        return $this->belongsTo(PortalContact::class, 'portal_contact_id');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────

    public function scopeActifs($q) { return $q->where('statut', 'actif'); }
    public function scopeManuels($q) { return $q->where('type', 'manuel'); }
    public function scopeInvites($q) { return $q->where('type', 'invite'); }
}
