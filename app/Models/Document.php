<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'folder_id',
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
        'uploaded_by',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'version' => 'integer',
            'is_archived' => 'boolean',
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
