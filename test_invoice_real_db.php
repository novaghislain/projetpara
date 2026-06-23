<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;
use App\Models\ErpInvoice;
use App\Models\ErpInvoiceItem;
use App\Http\Controllers\Gel\Erp\InvoicePdfController;

$client = Client::firstOrCreate(
    ['email' => 'test@client.com'],
    [
        'company_name' => 'ENTREPRISE DE TEST',
        'ifu' => '1234567890123',
        'address' => 'Cotonou',
        'phone' => '+229 00 00 00 00',
    ]
);

$invoice = ErpInvoice::create([
    'invoice_number' => 'TEST-VR-'.time(),
    'invoice_date' => now(),
    'client_name' => 'WECYCLE SARL',
    'total_ht' => 50000,
    'tax_amount' => 9000, // 18% TVA
    'total_ttc' => 59000,
    'status' => 'emise',
    'client_id' => $client->id,
]);

ErpInvoiceItem::create([
    'erp_invoice_id' => $invoice->id,
    'designation' => 'Prestation de service',
    'quantity' => 1,
    'unit_price' => 50000,
    'total_price' => 50000,
]);

// Auth bypass pour le contexte PDF (qui cherche auth()->user())
// L'InvoicePdfController gère le cas sans auth en mettant $company à null.

$controller = app(InvoicePdfController::class);

// Capture response
$response = $controller->download($invoice->id);
file_put_contents(public_path('facture_vraie_logique.pdf'), $response->getContent());

echo "Généré avec succès : " . url('facture_vraie_logique.pdf') . "\n";
