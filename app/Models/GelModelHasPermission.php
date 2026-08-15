<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GelModelHasPermission extends Model
{
    use HasFactory;
    protected $table = 'gel_model_has_permissions';

    protected $fillable = [
        'permission_id',
        'model_type',
        'model_id',
        'cabinet_id'
    ];

    public function permission()
    {
        return $this->belongsTo(GelPermission::class, 'permission_id', 'id');
    }

}
