<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle ClientInvitation (Invitation de client).
 *
 * Gère l'invitation d'un client à rejoindre la plateforme via un token.
 * L'invitation a une date d'expiration et un statut (en_attente, acceptee, expiree).
 * Table associée : `gel_client_invitations`.
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet émetteur
 * @property int|null $client_id ID du client (si déjà créé)
 * @property string $email Email du destinataire
 * @property string $nom Nom du contact invité
 * @property string $token Token d'invitation unique
 * @property string $statut Statut (en_attente, acceptee, expiree, refusee)
 * @property \Carbon\Carbon $expire_at Date d'expiration
 * @property \Carbon\Carbon|null $accepte_at Date d'acceptation
 * @property string|null $message Message personnalisé
 *
 * @property-read \App\Models\Gel\Cabinet $cabinet Cabinet émetteur
 * @property-read \App\Models\Gel\Client|null $client Client associé
 */
class ClientInvitation extends Model
{
    protected $table = 'gel_client_invitations';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'invited_by_user_id',
        'role_invite',
        'email',
        'nom',
        'token',
        'statut',
        'expire_at',
        'acceptee_at',
        'message',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'acceptee_at' => 'datetime',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeValide($query)
    {
        return $query->where('statut', 'en_attente')
            ->where('expire_at', '>', now());
    }

    public function estExpiree(): bool
    {
        return $this->expire_at && $this->expire_at->isPast();
    }

    public function estAcceptee(): bool
    {
        return $this->statut === 'acceptee';
    }
}
