<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant le journal d'audit des documents.
 *
 * Table associée : `document_audit_log`
 *
 * Enregistre chaque action (consultation, téléchargement, modification)
 * effectuée sur un document.
 */
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
