<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelClientInvitation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_client_invitations';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'email',
        'token',
        'statut',
        'expires_at'
    ];

}
