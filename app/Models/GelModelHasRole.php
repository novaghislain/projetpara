<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GelModelHasRole extends Model
{
    use HasFactory;
    protected $table = 'gel_model_has_roles';

    protected $fillable = [
        'role_id',
        'model_type',
        'model_id',
        'cabinet_id'
    ];

    public function role()
    {
        return $this->belongsTo(GelRole::class, 'role_id', 'id');
    }

}
