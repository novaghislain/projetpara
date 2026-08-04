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
    /**
     * Contrôleur de génération des factures PDF dans le module ERP.
     * Prend en charge la certification e-MECeF et l'intégration du QR code
     * dans les factures normalisées.
     */

    /**
     * Télécharge une facture au format PDF avec QR code e-MECeF.
     *
     * @param int $id L'identifiant de la facture à télécharger
     * @return \Illuminate\Http\Response Le fichier PDF téléchargeable
     */
    public function download($id)
    {
        // Chargement de la facture avec ses relations (client et lignes)
        $invoice = ErpInvoice::with(['client', 'lineItems'])->findOrFail($id);
        $company = auth()->check() ? (auth()->user()->activeClient ?? auth()->user()->client) : null;

        // Génération du QR code en Base64 (SVG) à partir de la chaîne e-MECeF
        $qrCodeBase64 = null;

        // Certification e-MECeF si le QR code n'a pas encore été généré
        if (empty($invoice->emecef_qr)) {
            \App\Services\EmecefService::certifyInvoice($invoice);
            $invoice->refresh(); // Rechargement des attributs après certification
        }

        // Génération du QR code à partir des données certifiées
        if (!empty($invoice->emecef_qr)) {
            $options = new QROptions;
            $options->scale = 5;

            $qrcode = new QRCode($options);
            // La méthode render() retourne "data:image/svg+xml;base64,..." par défaut (outputBase64=true)
            $qrCodeBase64 = $qrcode->render($invoice->emecef_qr);
        }

        // Conversion du montant total TTC en toutes lettres
        $amountInWords = NumberToWords::toWords((int) $invoice->total_ttc);

        // Préparation des données pour la vue PDF
        $data = [
            'invoice'       => $invoice,
            'company'       => $company,
            'qrCodeBase64'  => $qrCodeBase64,
            'amountInWords' => $amountInWords,
        ];

        // Génération du PDF à partir de la vue dédiée
        $pdf = Pdf::loadView('accounting.pdf.facture_normalisee', $data);
        $pdf->setPaper('A4', 'portrait');

        // Téléchargement du fichier PDF
        return $pdf->download("Facture_{$invoice->invoice_number}.pdf");
    }
}
