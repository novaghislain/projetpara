<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CacheLock extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cache_locks';

    protected $fillable = [
        'key',
        'owner',
        'expiration'
    ];

}
