<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class GelMessage extends Model
{
    use HasFactory;

    protected $table = 'gel_messages';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'sender_id',
        'sender_type',
        'receiver_id',
        'channel',
        'message',
        'piece_jointe',
        'est_lu',
        'portal_contact_id',
    ];

    // Canaux de messagerie (S11)
    public const CHANNEL_ENTREPRISE = 'entreprise';
    public const CHANNEL_COMPTABLE = 'interne_comptable';
    public const CHANNEL_ADMIN = 'interne_admin';
    // S4.1 — Canal de coordination dédié, strictement réservé à l'échange
    // Secrétaire ↔ Comptable sur une même entreprise (distinct de la messagerie
    // visible par l'entreprise et des canaux internes génériques).
    public const CHANNEL_COORDINATION = 'coordination';

    protected $casts = [
        'est_lu' => 'boolean',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function portalContact()
    {
        return $this->belongsTo(\App\Models\PortalContact::class, 'portal_contact_id');
    }
}
