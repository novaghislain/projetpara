<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasUuids;

    protected $table = 'roles';
    public $timestamps = false;

    protected $fillable = [
        'code',
        'libelle',
    ];
}
