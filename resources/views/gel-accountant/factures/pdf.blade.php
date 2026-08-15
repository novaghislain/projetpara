<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 13px; color: #333; margin: 0; padding: 20px; }
        .header { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #16a34a; padding-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; color: #16a34a; }
        .invoice-title { font-size: 28px; font-weight: bold; text-align: right; color: #555; }
        .invoice-number { font-size: 16px; text-align: right; color: #777; }
        .meta-table { width: 100%; margin-bottom: 30px; }
        .meta-table td { vertical-align: top; width: 50%; }
        .label { font-size: 11px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 5px; }
        .client-name { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .lines-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .lines-table th { background-color: #16a34a; color: white; padding: 10px; text-align: left; font-size: 12px; }
        .lines-table th.right { text-align: right; }
        .lines-table th.center { text-align: center; }
        .lines-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .lines-table td.right { text-align: right; }
        .lines-table td.center { text-align: center; }
        .totals-table { width: 40%; float: right; border-collapse: collapse; }
        .totals-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .totals-table td.right { text-align: right; font-weight: bold; }
        .grand-total { font-size: 16px; font-weight: bold; background-color: #f4fdf8; color: #16a34a; }
        .notes-section { clear: both; margin-top: 50px; font-size: 12px; color: #555; }
        .qr-code { float: right; margin-top: 20px; }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td>
                <div class="company-name">{{ auth()->user()->name ?? 'Mon Cabinet' }}</div>
                <div style="color: #666; margin-top: 5px;">Cabinet Comptable</div>
            </td>
            <td>
                <div class="invoice-title">FACTURE</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td>
                <div class="label">FACTURÉ À</div>
                <div class="client-name">{{ $invoice->partner_name ?? '—' }}</div>
                @if($invoice->partner_address)
                    <div>{{ $invoice->partner_address }}</div>
                @endif
                @if($invoice->partner_tax_id)
                    <div>IFU : {{ $invoice->partner_tax_id }}</div>
                @endif
            </td>
            <td>
                <div class="label">DÉTAILS</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 3px 0; color: #555; width: 40%;">Date de facture :</td>
                        <td style="padding: 3px 0; font-weight: bold; text-align: right;">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 0; color: #555;">Date d'échéance :</td>
                        <td style="padding: 3px 0; font-weight: bold; text-align: right;">{{ $invoice->due_date->format('d/m/Y') }}</td>
                    </tr>
                    @if($invoice->payment_term)
                    <tr>
                        <td style="padding: 3px 0; color: #555;">Conditions :</td>
                        <td style="padding: 3px 0; font-weight: bold; text-align: right;">{{ $invoice->payment_term }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table class="lines-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th class="center">Qté</th>
                <th class="right">Prix unit.</th>
                <th class="center">TVA</th>
                <th class="right">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
            <tr>
                <td style="color: #999;">{{ $line->line_number }}</td>
                <td><strong>{{ $line->description }}</strong></td>
                <td class="center">{{ rtrim(rtrim(number_format($line->quantity, 2, ',', ' '), '0'), ',') }}</td>
                <td class="right">{{ number_format($line->unit_price, 0, ',', ' ') }} F</td>
                <td class="center">{{ $line->vat_rate }}%</td>
                <td class="right"><strong>{{ number_format($line->total, 0, ',', ' ') }} F</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td style="color: #555;">Sous-total HT</td>
            <td class="right">{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</td>
        </tr>
        @if($invoice->discount > 0)
        <tr>
            <td style="color: #555;">Remise</td>
            <td class="right" style="color: red;">-{{ number_format($invoice->discount, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endif
        <tr>
            <td style="color: #555;">TVA</td>
            <td class="right">{{ number_format($invoice->vat_total, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr class="grand-total">
            <td style="padding: 12px; font-weight: bold;">Total TTC</td>
            <td class="right" style="padding: 12px;">{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</td>
        </tr>
        @if($invoice->paid_amount > 0)
        <tr>
            <td style="color: #16a34a;">Montant payé</td>
            <td class="right" style="color: #16a34a;">-{{ number_format($invoice->paid_amount, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td style="color: red; font-weight: bold;">Solde dû</td>
            <td class="right" style="color: red;">{{ number_format($invoice->balance_due, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endif
    </table>

    @if($qrCodeBase64)
    <div class="qr-code">
        @if(!empty($invoice->emecef_is_simulation))
        <div style="font-size:10px; color:#92400e; background:#fffbeb; border:2px solid #f59e0b; border-radius:4px; padding:6px 8px; margin-bottom:8px; max-width:220px; float:right;">
            <strong>⚠ SIMULATION — MODE TEST</strong><br>
            Cette facture N'A PAS été transmise à la DGI e-MECeF. Le QR (NIM « SIM-… ») n'est pas vérifiable sur le portail des impôts.
        </div>
        @endif
        <img src="{{ $qrCodeBase64 }}" width="120" alt="QR Code e-MECeF">
    </div>
    @endif

    <div class="notes-section">
        @if($invoice->notes)
        <div class="label">NOTES</div>
        <p>{{ $invoice->notes }}</p>
        @endif
        
        @if($invoice->terms_conditions)
        <div class="label" style="margin-top: 15px;">CONDITIONS GÉNÉRALES</div>
        <p style="white-space: pre-wrap;">{{ $invoice->terms_conditions }}</p>
        @endif
    </div>

</body>
</html>
