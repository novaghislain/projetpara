<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;
use App\Models\ErpInvoice;
use App\Models\ErpInvoiceItem;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Helpers\NumberToWords;

// Mock Company
$company = new Client([
    'company_name' => 'NOVATECH VISION',
    'ifu' => '0202262760921',
    'address' => 'Carrefour des trois banques, Immeuble SOBEBRA',
    'city' => 'Cotonou',
    'phone' => '+229 01 91 34 85 57'
]);

// Mock Customer
$customer = new Client([
    'company_name' => 'WECYCLE SARL',
    'ifu' => '0987654321098',
    'address' => 'Zongo, Rue des Artisans',
    'phone' => '+229 01 60 00 00 00'
]);

// Mock Invoice
$invoice = new ErpInvoice([
    'invoice_number' => 'EM01596573-7',
    'invoice_date' => now(),
    'client_name' => 'WECYCLE SARL',
    'total_ht' => 132000,
    'tax_amount' => 0,
    'total_ttc' => 132000,
    'emecef_nim' => 'NIM-BJ-2025-0001234',
    'emecef_compteur' => '1234',
    'emecef_statut' => 'valide',
    'emecef_datetime' => now(),
    'emecef_qr' => 'NIM-BJ-2025-0001234|1234|a1b2c3d4e5f6|132000|20250620120000',
]);
$invoice->setRelation('client', $customer);

// Mock Items
$items = [
    new ErpInvoiceItem(['designation' => 'Antivirus Kaspersky', 'unit_price' => 10000, 'quantity' => 10, 'total_price' => 100000]),
    new ErpInvoiceItem(['designation' => 'Mémoire RAM DDR2 8 Go', 'unit_price' => 16000, 'quantity' => 2, 'total_price' => 32000]),
];
$invoice->setRelation('lineItems', collect($items));

// QR Code as SVG data URI (DOMPDF compatible via <img> tag)
$qrCodeBase64 = null;
if (!empty($invoice->emecef_qr)) {
    $options = new QROptions;
    $options->scale = 5;

    $qrcode = new QRCode($options);
    $qrCodeBase64 = $qrcode->render($invoice->emecef_qr);
}

$amountInWords = NumberToWords::toWords((int) $invoice->total_ttc);

$data = [
    'invoice'       => $invoice,
    'company'       => $company,
    'qrCodeBase64'  => $qrCodeBase64,
    'amountInWords' => $amountInWords,
];

$pdf = Pdf::loadView('accounting.pdf.facture_normalisee', $data);
$pdf->setPaper('A4', 'portrait');
$pdf->save(public_path('exemple_facture.pdf'));

echo "PDF généré : " . public_path('exemple_facture.pdf') . "\n";
