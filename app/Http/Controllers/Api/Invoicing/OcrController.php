<?php

namespace App\Http\Controllers\Api\Invoicing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ai\OcrInvoiceService;

class OcrController extends Controller
{
    protected OcrInvoiceService $ocrService;

    public function __construct(OcrInvoiceService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    public function extract(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $file = $request->file('document');
        $mediaType = $file->getMimeType();
        
        // Convertir le fichier en base64
        $base64Image = base64_encode(file_get_contents($file->getRealPath()));

        try {
            $extractedData = $this->ocrService->extractDataFromImage($base64Image, $mediaType);

            return response()->json([
                'status' => 'success',
                'data' => $extractedData,
                'message' => 'Données extraites avec succès via l\'IA.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'extraction OCR : ' . $e->getMessage()
            ], 500);
        }
    }
}
