<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture Normalisée - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .mt-20 { margin-top: 20px; }
        .mt-10 { margin-top: 10px; }

        /* Header */
        .header-table td {
            vertical-align: top;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #000;
        }

        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }

        /* Information section */
        .info-table td {
            vertical-align: top;
            padding: 5px 0;
        }

        .client-box {
            background-color: #f3f4f6;
            padding: 10px;
            border-radius: 4px;
            width: 250px;
            float: right;
        }

        /* Items Table */
        .items-table {
            margin-top: 20px;
            width: 100%;
        }
        .items-table th {
            background-color: #f9fafb;
            border-bottom: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        /* Impôts & Paiements */
        .summary-section {
            margin-top: 20px;
        }
        .summary-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .totals-table th, .totals-table td {
            padding: 5px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .totals-table th {
            background-color: #f9fafb;
        }

        .grand-total {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }

        .amount-in-words {
            margin-top: 20px;
            font-style: italic;
        }

        /* Security Elements */
        .security-box {
            margin-top: 30px;
            border: 2px solid #e0f2fe;
            padding: 15px;
            page-break-inside: avoid;
        }

        .security-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .qr-code {
            width: 120px;
            height: 120px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="50%">
                <div class="company-name">{{ $company->company_name ?? 'NOM DE L\'ENTREPRISE' }}</div>
                <div>IFU : {{ $company->ifu ?? 'N/A' }}</div>
            </td>
            <td width="50%" class="text-right">
                <div class="invoice-title">FACTURE DE VENTE</div>
                <div>Facture # {{ $invoice->invoice_number }}</div>
                <div>Date: {{ $invoice->invoice_date->format('d/m/Y') }}</div>
                <div>Vendeur: {{ $invoice->creator ? $invoice->creator->name : 'SFE en ligne' }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table mt-20">
        <tr>
            <td width="50%">
                <div>Adresse : {{ $company->address ?? '' }} {{ $company->city ?? 'N/A' }}</div>
                <div>Contact : {{ $company->phone ?? 'N/A' }}</div>
                <div class="mt-10 bold">VMCF : {{ $invoice->emecef_nim ?? 'N/A' }}</div>
            </td>
            <td width="50%">
                <div class="client-box">
                    <div class="bold mb-5">CLIENT</div>
                    <div>Nom : {{ $invoice->client_name ?? ($invoice->client ? $invoice->client->name : '') }}</div>
                    @if($invoice->client && $invoice->client->ifu)
                    <div>IFU : {{ $invoice->client->ifu }}</div>
                    @endif
                    @if($invoice->client && $invoice->client->address)
                    <div>Adresse : {{ $invoice->client->address }}</div>
                    @endif
                    @if($invoice->client && $invoice->client->phone)
                    <div>Contact : {{ $invoice->client->phone }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table mt-20">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="45%">Nom</th>
                <th width="15%" class="text-right">Prix unitaire</th>
                <th width="10%" class="text-right">Quantité</th>
                <th width="25%" class="text-right">Montant T.T.C.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lineItems as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->designation }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">
                    {{ number_format($item->total_price, 0, ',', '.') }} 
                    @if($invoice->tax_amount > 0)
                        [A]
                    @else
                        [E]
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-section">
        <div class="summary-title">--VENTILATION DES IMPÔTS--</div>
        <table class="totals-table" style="width: 70%; margin: 0 auto;">
            <thead>
                <tr>
                    <th>Groupe</th>
                    <th>Total</th>
                    <th>Imposable</th>
                    <th>Impôt</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td>A - 18%</td>
                    <td>{{ number_format($invoice->total_ttc, 0, ',', '.') }}</td>
                    <td>{{ number_format($invoice->total_ht, 0, ',', '.') }}</td>
                    <td>{{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @else
                <tr>
                    <td>E - TPS</td>
                    <td>{{ number_format($invoice->total_ttc, 0, ',', '.') }}</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="grand-total">
        Total: {{ number_format($invoice->total_ttc, 0, ',', '.') }}
    </div>

    <div class="summary-section mt-20">
        <div class="summary-title">--RÉPARTITION DES PAIEMENTS--</div>
        <table class="totals-table" style="width: 50%; margin: 0 auto;">
            <thead>
                <tr>
                    <th>Type de paiement</th>
                    <th>Payé</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>VIREMENT/CHEQUE</td>
                    <td>{{ number_format($invoice->total_ttc, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="amount-in-words text-center">
        Arrêté la présente facture à la somme de {{ $amountInWords ?: $invoice->total_ttc }} francs CFA TTC
    </div>

    <div class="security-box">
        <div class="security-title">-- ELÉMENTS DE SÉCURITÉ DE LA FACTURE NORMALISÉE --</div>
        <table style="width: 100%;">
            <tr>
                <td width="30%" class="text-center">
                    @if($qrCodeBase64)
                        <img src="{{ $qrCodeBase64 }}" class="qr-code" alt="QR Code">
                    @else
                        <div style="width:120px; height:120px; border:1px dashed #ccc; display:inline-block; line-height:120px; color:#999; font-size:11px;">Pas de QR</div>
                    @endif
                </td>
                <td width="70%" style="font-size: 11px;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="bold">Code MECeF/DGI</td>
                            <td>: {{ $invoice->emecef_statut === 'emise' ? 'OUI' : ($invoice->emecef_statut ?? 'En attente') }}</td>
                        </tr>
                        <tr>
                            <td class="bold">MECeF NIM</td>
                            <td>: {{ $invoice->emecef_nim ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="bold">MECeF Compteurs</td>
                            <td>: {{ $invoice->emecef_compteur ?? 'N/A' }} / {{ $invoice->emecef_compteur ?? 'N/A' }} FV</td>
                        </tr>
                        <tr>
                            <td class="bold">MECeF Heure</td>
                            <td>: {{ $invoice->emecef_datetime ? $invoice->emecef_datetime->format('d/m/Y H:i:s') : 'N/A' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
