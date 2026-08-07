<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicLinkRequest extends Model
{
    protected $fillable = [
        'client_id', 'created_by', 'token', 'title', 'description',
        'requested_documents', 'channel', 'status',
        'viewed_at', 'responded_at', 'expires_at',
        'response_files', 'response_message',
    ];

    protected $casts = [
        'requested_documents' => 'array',
        'response_files'      => 'array',
        'viewed_at'           => 'datetime',
        'responded_at'        => 'datetime',
        'expires_at'          => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
