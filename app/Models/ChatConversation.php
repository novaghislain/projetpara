<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'cabinet_id', 'title', 'messages',
        'statut', 'contexte', 'source', 'metadata',
    ];

    protected $casts = [
        'messages' => 'array',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function iaMessages(): HasMany
    {
        return $this->hasMany(IaMessage::class, 'conversation_id');
    }
}
