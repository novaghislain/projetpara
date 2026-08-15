<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cache extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cache';

    protected $fillable = [
        'key',
        'value',
        'expiration'
    ];

}
