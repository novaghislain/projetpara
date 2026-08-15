<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GelRoleHasPermission extends Model
{
    use HasFactory;
    protected $table = 'gel_role_has_permissions';

    protected $fillable = [
        'permission_id',
        'role_id'
    ];

    public function permission()
    {
        return $this->belongsTo(GelPermission::class, 'permission_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(GelRole::class, 'role_id', 'id');
    }

}
