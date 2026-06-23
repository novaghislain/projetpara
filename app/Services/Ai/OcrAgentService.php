<?php

namespace App\Services\Ai;

use App\Models\AiSuggestion;
use App\Models\Document;
use App\Services\OcrService;

class OcrAgentService
{
    public function __construct(
        private OcrService $ocrService
    ) {}

    /**
     * Analyser les documents récents sans OCR et suggérer un traitement.
     */
    public function suggestOcr(int $clientId): array
    {
        // Documents non traités par OCR (sans scan, PDF/images récents)
        $pendingDocs = Document::where('client_id', $clientId)
            ->whereIn('mime_type', ['application/pdf', 'image/jpeg', 'image/png', 'image/tiff'])
            ->where(function ($q) {
                $q->whereNull('file_hash')->orWhere('file_hash', '');
            })
            ->limit(10)
            ->get();

        if ($pendingDocs->count() === 0) {
            return ['suggestion_id' => null, 'count' => 0];
        }

        $suggestion = AiSuggestion::create([
            'client_id' => $clientId,
            'agent' => 'ocr',
            'type' => 'suggest_ocr',
            'title' => $pendingDocs->count() . ' document(s) à numériser',
            'description' => 'Ces documents peuvent être analysés par OCR pour extraire leur contenu.',
            'data' => [
                'documents' => $pendingDocs->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->original_name ?? $d->filename,
                    'type' => $d->mime_type,
                    'size' => $d->file_size,
                ])->toArray(),
            ],
            'metadata' => ['agent' => 'OCR', 'version' => '1.0'],
            'status' => 'pending',
        ]);

        return ['suggestion_id' => $suggestion->id, 'count' => $pendingDocs->count()];
    }

    /**
     * Classifier un document par son nom et type.
     */
    public function classifyDocument(Document $doc): array
    {
        $name = strtolower($doc->original_name ?? $doc->filename ?? '');
        $classification = $this->ocrService->classifyDocument($name);

        return [
            'document_id' => $doc->id,
            'classification' => $classification,
        ];
    }

    public function summary(int $clientId): array
    {
        $pending = AiSuggestion::byClient($clientId)->byAgent('ocr')->pending()->count();
        $unscanned = Document::where('client_id', $clientId)
            ->whereIn('mime_type', ['application/pdf', 'image/jpeg', 'image/png'])
            ->where(function ($q) {
                $q->whereNull('file_hash')->orWhere('file_hash', '');
            })
            ->count();

        return [
            'pending_suggestions' => $pending,
            'unscanned_documents' => $unscanned,
            'status' => $unscanned > 0 ? 'attention' : 'ok',
        ];
    }
}
