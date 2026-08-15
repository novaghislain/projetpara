<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CabinetInvitation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cabinet_invitations';

    protected $fillable = [
        'cabinet_id',
        'invited_by',
        'email',
        'role',
        'token',
        'statut',
        'expires_at'
    ];

}
