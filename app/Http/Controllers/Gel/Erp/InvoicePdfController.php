<?php

namespace App\Http\Controllers\Gel\Erp;

use App\Http\Controllers\Controller;
use App\Models\ErpInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Helpers\NumberToWords;

class InvoicePdfController extends Controller
{
    public function download($id)
    {
        $invoice = ErpInvoice::with(['client', 'lineItems'])->findOrFail($id);
        $company = auth()->check() ? (auth()->user()->activeClient ?? auth()->user()->client) : null;

        // Generate Base64 QR Code (SVG) from e-MECeF string, embed directly in PDF
        $qrCodeBase64 = null;
        
        // La vraie logique de certification
        if (empty($invoice->emecef_qr)) {
            \App\Services\EmecefService::certifyInvoice($invoice);
            $invoice->refresh(); // Recharger les attributs
        }

        if (!empty($invoice->emecef_qr)) {
            $options = new QROptions;
            $options->scale = 5;

            $qrcode = new QRCode($options);
            // render() returns "data:image/svg+xml;base64,..." by default (outputBase64=true)
            $qrCodeBase64 = $qrcode->render($invoice->emecef_qr);
        }

        // Amount to words
        $amountInWords = NumberToWords::toWords((int) $invoice->total_ttc);

        $data = [
            'invoice'       => $invoice,
            'company'       => $company,
            'qrCodeBase64'  => $qrCodeBase64,
            'amountInWords' => $amountInWords,
        ];

        $pdf = Pdf::loadView('accounting.pdf.facture_normalisee', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("Facture_{$invoice->invoice_number}.pdf");
    }
}
