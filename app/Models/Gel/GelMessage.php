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
        'message',
        'piece_jointe',
        'est_lu',
        'portal_contact_id',
    ];

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
