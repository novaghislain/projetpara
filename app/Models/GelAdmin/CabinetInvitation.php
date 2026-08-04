<?php

namespace App\Models\GelAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Models\Gel\Cabinet;

class CabinetInvitation extends Model
{
    protected $table = 'cabinet_invitations';

    protected $fillable = [
        'cabinet_id',
        'email',
        'role_id',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function role()
    {
        // Si on utilise spatie/laravel-permission:
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }
}
