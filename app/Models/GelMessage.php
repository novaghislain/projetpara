<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'portal_contact_id'
    ];

}
