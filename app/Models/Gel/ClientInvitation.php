<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ClientInvitation extends Model
{
    protected $table = 'gel_client_invitations';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'email',
        'nom',
        'token',
        'statut',
        'expire_at',
        'accepte_at',
        'message',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'accepte_at' => 'datetime',
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
