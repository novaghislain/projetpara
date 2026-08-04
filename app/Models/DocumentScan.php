<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant le résultat d'analyse OCR d'un document scanné.
 *
 * Table associée : `document_scans` (via convention Laravel)
 *
 * Stocke le texte extrait, le niveau de confiance et les entités
 * détectées automatiquement par l'OCR.
 *
 * Relations :
 * - Un scan appartient à un client (User, via client_id)
 * - Un scan appartient à un document (Document)
 */
class DocumentScan extends Model
{
    protected $fillable = [
        'client_id', 'document_id', 'original_filename', 'mime_type',
        'file_path', 'extracted_text', 'confidence',
        'entities', 'status', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'entities' => 'array',
            'confidence' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
