<?php

namespace App\Services;

use App\Models\DocumentScan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Smalot\PdfParser\Parser as PdfParser;
use Spatie\PdfToText\Pdf as SpatiePdfToText;

/**
 * Service d'OCR et d'extraction de texte de documents scannés.
 *
 * Supporte :
 * - Tesseract OCR (images, PDF scannés)
 * - Extraction texte de PDF natifs (via smalot/pdfparser + spatie/pdf-to-text)
 * - Google Cloud Vision API (optionnel, haute précision)
 */
class OcrService
{
    private string $engine;
    private string $tesseractPath;
    private ?string $googleCredentials;

    public function __construct()
    {
        $this->engine = config('services.ocr.engine', 'tesseract');
        $this->tesseractPath = config('services.ocr.tesseract_path', 'tesseract');
        $this->googleCredentials = config('services.ocr.google_credentials');
    }

    /**
     * Analyser un fichier et extraire le texte.
     */
    public function analyze(string $filePath, string $mimeType, array $meta = []): array
    {
        $fullPath = Storage::path($filePath);

        if (!file_exists($fullPath)) {
            return ['success' => false, 'error' => 'Fichier introuvable.'];
        }

        // PDF natif → extraction directe
        if ($mimeType === 'application/pdf') {
            $text = $this->extractPdfText($fullPath);
            if (strlen(trim($text)) > 50) {
                // Le PDF contient déjà du texte exploitable
                return [
                    'success' => true,
                    'text' => $text,
                    'method' => 'pdf_parse',
                    'confidence' => 95.0,
                ];
            }
            // Sinon, c'est un PDF scanné → OCR
        }

        // OCR via l'engine configuré
        return match ($this->engine) {
            'google_vision' => $this->ocrViaGoogleVision($fullPath, $mimeType),
            default        => $this->ocrViaTesseract($fullPath),
        };
    }

    /**
     * Analyser et sauvegarder le résultat en base.
     */
    public function analyzeAndSave(string $filePath, string $mimeType, int $clientId, ?int $documentId = null, array $meta = []): DocumentScan
    {
        $scan = DocumentScan::create([
            'client_id'         => $clientId,
            'document_id'       => $documentId,
            'original_filename' => $meta['filename'] ?? basename($filePath),
            'mime_type'         => $mimeType,
            'file_path'         => $filePath,
            'status'            => 'processing',
        ]);

        try {
            $result = $this->analyze($filePath, $mimeType, $meta);

            if ($result['success']) {
                $entities = $this->extractEntities($result['text']);

                $scan->update([
                    'extracted_text' => $result['text'],
                    'confidence'     => $result['confidence'] ?? 0,
                    'entities'       => $entities,
                    'status'         => 'completed',
                    'processed_at'   => now(),
                ]);
            } else {
                $scan->update([
                    'status' => 'failed',
                    'processed_at' => now(),
                ]);
                Log::error('OCR failed', ['scan_id' => $scan->id, 'error' => $result['error']]);
            }
        } catch (\Exception $e) {
            $scan->update(['status' => 'failed', 'processed_at' => now()]);
            Log::error('OCR exception', ['scan_id' => $scan->id, 'error' => $e->getMessage()]);
        }

        return $scan->fresh();
    }

    /**
     * OCR via Tesseract.
     */
    private function ocrViaTesseract(string $filePath): array
    {
        try {
            if (!class_exists(TesseractOCR::class)) {
                // Fallback: pas de Tesseract installé → retour simulé pour dev
                return [
                    'success' => false,
                    'error' => 'Tesseract OCR non disponible. Installez le package : composer require thiagoalessio/tesseract_ocr',
                ];
            }

            $tesseract = new TesseractOCR($filePath);
            $tesseract->lang('fra');
            $tesseract->psm(3);

            if ($this->tesseractPath !== 'tesseract') {
                $tesseract->executable($this->tesseractPath);
            }

            $text = $tesseract->run();

            return [
                'success' => true,
                'text' => $text,
                'method' => 'tesseract',
                'confidence' => $this->estimateConfidence($text),
            ];
        } catch (\Exception $e) {
            Log::error('Tesseract OCR error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * OCR via Google Cloud Vision.
     */
    private function ocrViaGoogleVision(string $filePath, string $mimeType): array
    {
        try {
            if (!$this->googleCredentials) {
                return ['success' => false, 'error' => 'Google Cloud Vision non configuré.'];
            }

            $imageData = base64_encode(file_get_contents($filePath));

            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => false,
            ])->post('https://vision.googleapis.com/v1/images:annotate?key=' . $this->googleCredentials, [
                'requests' => [[
                    'image' => ['content' => $imageData],
                    'features' => [['type' => 'TEXT_DETECTION', 'maxResults' => 1]],
                ]],
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'error' => $response->body()];
            }

            $data = $response->json();
            $text = $data['responses'][0]['textAnnotations'][0]['description'] ?? '';

            return [
                'success' => true,
                'text' => $text,
                'method' => 'google_vision',
                'confidence' => $data['responses'][0]['textAnnotations'][0]['confidence'] ?? 95.0,
            ];
        } catch (\Exception $e) {
            Log::error('Google Vision OCR error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Extraction de texte d'un PDF natif.
     * Utilise d'abord spatie/pdf-to-text (pdftotext), fallback smalot/pdfparser.
     */
    private function extractPdfText(string $filePath): string
    {
        // 1. spatie/pdf-to-text (meilleure qualité, nécessite pdftotext binaire)
        try {
            if (class_exists(SpatiePdfToText::class)) {
                $binPath = config('services.ocr.pdftotext_path', '');
                $pdf = $binPath ? new SpatiePdfToText($binPath) : new SpatiePdfToText();
                return $pdf->getText($filePath);
            }
        } catch (\Exception $e) {
            Log::debug('spatie/pdf-to-text failed, falling back to smalot/pdfparser', ['error' => $e->getMessage()]);
        }

        // 2. smalot/pdfparser (fallback PHP pur)
        try {
            if (class_exists(PdfParser::class)) {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($filePath);
                return $pdf->getText();
            }
        } catch (\Exception $e) {
            Log::debug('PDF text extraction failed (may be scanned)', ['error' => $e->getMessage()]);
        }

        return '';
    }

    /**
     * Extraction d'entités (montants, dates, noms) depuis le texte.
     */
    private function extractEntities(string $text): array
    {
        $entities = [
            'amounts' => [],
            'dates'   => [],
            'emails'  => [],
            'phones'  => [],
            'ifu'     => [],
        ];

        // Montants (chiffres avec 2 décimales, optionnellement avec séparateurs)
        preg_match_all('/\b\d{1,3}(?:\s?\d{3})*(?:[.,]\d{2})?\s*(?:€|EUR|FCFA|CFA)?\b/', $text, $amountMatches);
        foreach ($amountMatches[0] as $match) {
            $num = (float) str_replace([' ', '€', 'EUR', 'FCFA', 'CFA', ','], ['', '', '', '', '', '.'], $match);
            if ($num > 0) $entities['amounts'][] = $num;
        }

        // Dates (format français jj/mm/aaaa ou jj-mm-aaaa)
        preg_match_all('/\b\d{2}[/-]\d{2}[/-]\d{4}\b/', $text, $dateMatches);
        $entities['dates'] = $dateMatches[0];

        // Emails
        preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $emailMatches);
        $entities['emails'] = $emailMatches[0];

        // Téléphones (format Bénin +229 XX XX XX XX)
        preg_match_all('/(?:\+229|00229)?\s?\d{2}\s?\d{2}\s?\d{2}\s?\d{2}\s?\d{2}/', $text, $phoneMatches);
        $entities['phones'] = $phoneMatches[0];

        // IFU (9 chiffres)
        preg_match_all('/\b\d{9}\b/', $text, $ifuMatches);
        $entities['ifu'] = $ifuMatches[0];

        return $entities;
    }

    /**
     * Estimation naïve de la confiance OCR basée sur la longueur du texte.
     */
    private function estimateConfidence(string $text): float
    {
        $length = strlen(trim($text));
        if ($length > 500) return 92.0;
        if ($length > 100) return 85.0;
        if ($length > 20)  return 70.0;
        return 50.0;
    }
}
