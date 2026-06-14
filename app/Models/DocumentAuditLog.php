<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAuditLog extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'action',
        'metadata',
        'ip_address',
    ];

    protected $table = 'document_audit_log';

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
