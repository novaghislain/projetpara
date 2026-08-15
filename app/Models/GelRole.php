<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GelRole extends Model
{
    use HasFactory;
    protected $table = 'gel_roles';

    protected $fillable = [
        'cabinet_id',
        'name',
        'guard_name',
        'module',
        'label_fr',
        'description',
        'level',
        'portail'
    ];

    public function gelRoleHasPermissions()
    {
        return $this->hasMany(GelRoleHasPermission::class, 'role_id', 'id');
    }

}
