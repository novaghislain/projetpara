<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeAuditLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_audit_logs';

    protected $fillable = [
        'user_id',
        'client_id',
        'action',
        'modele',
        'modele_id',
        'description'
    ];

}
