<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeFile implements ValidationRule
{
    protected array $allowedMimes = [
        'application/pdf',
        'image/jpeg', 'image/png', 'image/webp',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    protected int $maxSize = 5120; // 5 Mo

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('Fichier invalide');
            return;
        }

        // Vérification MIME réelle (pas seulement l'extension)
        if (!in_array($value->getMimeType(), $this->allowedMimes)) {
            $fail('Type de fichier non autorisé');
            return;
        }

        // Vérification taille
        if ($value->getSize() > $this->maxSize * 1024) {
            $fail("Le fichier ne doit pas dépasser {$this->maxSize} Ko");
            return;
        }

        // Vérification qu'il n'y a pas de contenu PHP caché
        $content = file_get_contents($value->getPathname());
        if (preg_match('/<\?php|<?=|exec\(|system\(|passthru\(|shell_exec\(|eval\(|include\(|require\(/i', $content)) {
            $fail('Le fichier contient du code suspect');
            return;
        }

        // Vérification de la signature (magic bytes) — anti-falsification
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $value->getPathname());
        finfo_close($finfo);

        if ($detectedMime !== $value->getMimeType()) {
            $fail('Fichier corrompu ou falsifié');
        }
    }
}
