<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelAuditLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_audit_logs';

    protected $fillable = [
        'cabinet_id',
        'user_id',
        'client_id',
        'action',
        'modele',
        'modele_id',
        'old_values',
        'new_values',
        'ip_address'
    ];

}
