<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\ExpenseReport;
use App\Models\Accounting\ExpenseReportLine;
use App\Services\Ai\OcrInvoiceService;

class ExpenseReportController extends Controller
{
    protected OcrInvoiceService $ocrService;

    public function __construct(OcrInvoiceService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    public function createReport(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'title' => 'required|string',
        ]);

        $report = ExpenseReport::create([
            'client_id' => $request->client_id,
            'title' => $request->title,
            'status' => 'draft'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $report,
            'message' => 'Note de frais créée.'
        ]);
    }

    public function addLineWithOcr(Request $request, $reportId)
    {
        $report = ExpenseReport::findOrFail($reportId);

        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $path = $request->file('file')->store('receipts');
        $fullPath = storage_path('app/' . $path);

        // Appel de l'IA pour lire le reçu
        try {
            $ocrResult = $this->ocrService->extractInvoiceData($fullPath);
            $ocrData = json_decode($ocrResult, true);

            $line = ExpenseReportLine::create([
                'expense_report_id' => $report->id,
                'date' => $ocrData['date'] ?? now()->toDateString(),
                'merchant' => $ocrData['supplier_name'] ?? 'Inconnu',
                'amount' => $ocrData['total_amount'] ?? 0,
                'tax_amount' => $ocrData['tax_amount'] ?? 0,
                'receipt_path' => $path,
                'ocr_data' => $ocrData
            ]);

            // Mettre à jour le total de la note
            $report->update([
                'total_amount' => $report->lines()->sum('amount')
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $line,
                'message' => 'Reçu analysé et ajouté.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
