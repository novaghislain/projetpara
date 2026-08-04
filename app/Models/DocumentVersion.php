<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une version d'un document.
 *
 * Table associée : `document_versions`
 *
 * Permet le versionnement des documents avec suivi des modifications
 * et la possibilité de restaurer une version antérieure.
 *
 * Relations :
 * - Une version appartient à un document (Document)
 * - Une version est créée par un utilisateur (User)
 */
class DocumentVersion extends Model
{
    protected $fillable = [
        'document_id',
        'version_number',
        'file_path',
        'file_size',
        'file_hash',
        'mime_type',
        'created_by',
        'change_notes',
    ];

    protected $table = 'document_versions';

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
