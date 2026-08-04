<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une signature électronique sur un document.
 *
 * Table associée : `document_signatures` (via convention Laravel)
 *
 * Stocke les données de signature, le hash du document et les
 * informations du signataire.
 *
 * Relations :
 * - Une signature appartient à un document (Document)
 */
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
