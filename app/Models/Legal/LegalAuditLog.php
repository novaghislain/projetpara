<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LegalAuditLog extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_audit_log';
    public $timestamps = true;

    protected $fillable = [
        'client_id', 'model_type', 'model_id',
        'action', 'user_id', 'details',
    ];

    protected $casts = [
        'details' => 'json',
    ];
}
