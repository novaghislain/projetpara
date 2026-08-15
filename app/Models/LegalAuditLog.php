<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalAuditLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_audit_log';

    protected $fillable = [
        'user_id',
        'client_id',
        'action',
        'description'
    ];

}
