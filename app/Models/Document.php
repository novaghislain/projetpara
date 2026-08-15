<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un document (fichier) stocké dans l'application.
 *
 * Table associée : `documents` (via convention Laravel)
 *
 * Relations :
 * - Un document appartient à un client (Client)
 * - Un document peut être dans un dossier (ClientFolder)
 * - Un document est téléversé par un utilisateur (User)
 * - Un document peut avoir plusieurs versions (DocumentVersion)
 * - Un document peut avoir plusieurs logs d'audit (DocumentAuditLog)
 */
class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'folder_id',
        'category',
        'annee_liee',
        'mois_lie',
        'document_date',
        'name',
        'original_name',
        'file_path',
        'file_hash',
        'file_type',
        'file_size',
        'mime_type',
        'description',
        'tags',
        'version',
        'is_favorite',
        'privacy_level',
        'share_token',
        'uploaded_by',
        'is_archived',
        // S10 — Workflow de circulation
        'workflow_step',
        'workflow_notes',
        'priority',
        'processed_at',
        'processed_by',
        'transmitted_at',
        'transmitted_by',
        'validated_at',
        'validated_by',
        'is_secured',
        'secure_password',
    ];

    protected $hidden = [
        'secure_password',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'version' => 'integer',
            'is_archived' => 'boolean',
            'is_favorite' => 'boolean',
            'tags' => 'array',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function folder()
    {
        return $this->belongsTo(ClientFolder::class, 'folder_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(DocumentAuditLog::class);
    }

    // Formater la taille du fichier
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) return '—';
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $size = $this->file_size;
        $unit = 0;
        while ($size >= 1024 && $unit < 3) {
            $size /= 1024;
            $unit++;
        }
        return round($size, 1) . ' ' . $units[$unit];
    }

    // Scope pour les documents non archivés
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    // Scope par dossier
    public function scopeInFolder($query, $folderId)
    {
        return $query->where('folder_id', $folderId);
    }
}
