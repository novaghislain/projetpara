<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'gel_audit_logs';

    protected $fillable = [
        'cabinet_id',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditable()
    {
        return $this->morphTo();
    }
}
