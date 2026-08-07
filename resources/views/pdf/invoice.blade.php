<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; }
        .header { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #0D9488; padding-bottom: 20px; }
        .header td { vertical-align: top; }
        .company-name { font-size: 24px; font-weight: bold; color: #0D9488; }
        .invoice-title { font-size: 28px; text-transform: uppercase; color: #333; text-align: right; }
        
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { width: 50%; vertical-align: top; }
        .box { border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #f9f9f9; }
        .box h4 { margin: 0 0 10px 0; color: #0D9488; text-transform: uppercase; font-size: 12px; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #0D9488; color: white; padding: 10px; text-align: left; font-size: 12px; text-transform: uppercase; }
        .items-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals-table { width: 50%; float: right; border-collapse: collapse; }
        .totals-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .totals-table .grand-total { font-size: 18px; font-weight: bold; color: #0D9488; border-bottom: none; }
        
        .footer { position: fixed; bottom: -20px; width: 100%; text-align: center; font-size: 10px; color: #888; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td>
                <div class="company-name">{{ $invoice->client->nom_entreprise ?? 'Mon Entreprise' }}</div>
                <div style="color: #666; margin-top:5px;">
                    123 Avenue de l'Expertise<br>
                    Libreville, Gabon<br>
                    Tél : +241 11 22 33 44
                </div>
            </td>
            <td class="text-right">
                <div class="invoice-title">FACTURE</div>
                <div style="margin-top: 10px;">
                    <strong>N° :</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Date :</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}<br>
                    <strong>Échéance :</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="padding-right: 15px;">
                <div class="box">
                    <h4>Facturé à</h4>
                    <strong>{{ $invoice->partner->company_name ?? ($invoice->partner->first_name . ' ' . $invoice->partner->last_name) }}</strong><br>
                    {{ $invoice->partner->address ?? 'Adresse non renseignée' }}<br>
                    {{ $invoice->partner->city ?? '' }} {{ $invoice->partner->country ?? '' }}<br>
                    {{ $invoice->partner->email ?? '' }}
                </div>
            </td>
            <td>
                <!-- Espace vide ou infos supplémentaires -->
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-center">Qté</th>
                <th class="text-right">Prix Unitaire</th>
                <th class="text-center">TVA</th>
                <th class="text-right">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
            <tr>
                <td>{{ $line->description }}</td>
                <td class="text-center">{{ $line->quantity }}</td>
                <td class="text-right">{{ number_format($line->unit_price, 2, ',', ' ') }}</td>
                <td class="text-center">{{ $line->vat_rate }}%</td>
                <td class="text-right">{{ number_format($line->subtotal, 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="width: 100%; display: inline-block;">
        <table class="totals-table">
            <tr>
                <td><strong>Total HT</strong></td>
                <td class="text-right">{{ number_format($invoice->subtotal, 2, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            <tr>
                <td><strong>Total TVA</strong></td>
                <td class="text-right">{{ number_format($invoice->vat_total, 2, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            <tr>
                <td class="grand-total">TOTAL TTC</td>
                <td class="text-right grand-total">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Document généré par GEL Accountant le {{ date('d/m/Y à H:i') }}
    </div>

</body>
</html>
