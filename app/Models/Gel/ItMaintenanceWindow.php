<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItMaintenanceWindow extends Model
{
    use HasFactory;

    protected $fillable = [
        'starts_at',
        'ends_at',
        'message',
        'created_by'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
}
