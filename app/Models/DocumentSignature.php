<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignature extends Model
{
    protected $fillable = [
        'document_id', 'signer_name', 'signer_email', 'signer_phone',
        'signature_data', 'document_hash', 'ip_address',
        'signed_at', 'token', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo { return $this->belongsTo(Document::class); }
}
