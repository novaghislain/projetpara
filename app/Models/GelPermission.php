<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GelPermission extends Model
{
    use HasFactory;
    protected $table = 'gel_permissions';

    protected $fillable = [
        'name',
        'guard_name'
    ];

    public function gelRoleHasPermissions()
    {
        return $this->hasMany(GelRoleHasPermission::class, 'permission_id', 'id');
    }

}
