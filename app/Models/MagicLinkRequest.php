<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicLinkRequest extends Model
{
    protected $fillable = [
        'uuid', 'client_id', 'title', 'description',
        'requested_documents', 'uploaded_files', 'expires_at', 'status'
    ];

    protected $casts = [
        'requested_documents' => 'array',
        'uploaded_files'      => 'array',
        'expires_at'          => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }


    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
