<?php

namespace App\Http\Controllers\GelClient;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $request, $slug)
    {
        $contact = Auth::guard('portal')->user();
        $client = \App\Models\Gel\Client::where('portal_slug', $slug)->firstOrFail();
        
        $query = Invoice::where('client_id', $client->id)
            ->where('type', 'customer_invoice')
            ->where('status', '!=', 'draft');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderByDesc('invoice_date')->paginate(15);
        
        $stats = [
            'total' => Invoice::where('client_id', $client->id)->where('type', 'customer_invoice')->where('status', '!=', 'draft')->count(),
            'paid' => Invoice::where('client_id', $client->id)->where('type', 'customer_invoice')->where('status', 'paid')->count(),
            'unpaid' => Invoice::where('client_id', $client->id)->where('type', 'customer_invoice')->whereIn('status', ['sent', 'overdue'])->count(),
            'total_due' => Invoice::where('client_id', $client->id)->where('type', 'customer_invoice')->whereIn('status', ['sent', 'overdue'])->sum('balance_due'),
        ];

        return view('gel-client.invoices.index', compact('invoices', 'stats'));
    }

    public function show(Request $request, $slug, $id)
    {
        $contact = Auth::guard('portal')->user();
        $client = \App\Models\Gel\Client::where('portal_slug', $slug)->firstOrFail();
        
        $invoice = Invoice::where('client_id', $client->id)
            ->where('type', 'customer_invoice')
            ->where('status', '!=', 'draft')
            ->findOrFail($id);

        $qrCodeBase64 = null;
        if ($invoice->emecef_statut === 'emise' && !empty($invoice->emecef_qr)) {
            $options = new QROptions([
                'version'      => QRCode::VERSION_AUTO,
                'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'     => QRCode::ECC_L,
                'imageBase64'  => true,
            ]);
            $qrcode = new QRCode($options);
            $qrCodeBase64 = $qrcode->render($invoice->emecef_qr);
        }

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('gel-accountant.factures.pdf', compact('invoice', 'qrCodeBase64'));
            return $pdf->stream('facture_' . $invoice->invoice_number . '.pdf');
        }

        return view('gel-client.invoices.show', compact('invoice', 'qrCodeBase64'));
    }
}
