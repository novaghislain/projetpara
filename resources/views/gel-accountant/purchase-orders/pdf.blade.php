<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de Commande {{ $po->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 13px; color: #333; margin: 0; padding: 20px; }
        .header { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #0284c7; padding-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; color: #0284c7; }
        .invoice-title { font-size: 24px; font-weight: bold; text-align: right; color: #555; }
        .invoice-number { font-size: 16px; text-align: right; color: #777; }
        .meta-table { width: 100%; margin-bottom: 30px; }
        .meta-table td { vertical-align: top; width: 50%; }
        .label { font-size: 11px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 5px; }
        .client-name { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .lines-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .lines-table th { background-color: #0284c7; color: white; padding: 10px; text-align: left; font-size: 12px; }
        .lines-table th.right { text-align: right; }
        .lines-table th.center { text-align: center; }
        .lines-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .lines-table td.right { text-align: right; }
        .lines-table td.center { text-align: center; }
        .totals-table { width: 40%; float: right; border-collapse: collapse; }
        .totals-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .totals-table td.right { text-align: right; font-weight: bold; }
        .grand-total { font-size: 16px; font-weight: bold; background-color: #f0f9ff; color: #0284c7; }
        .notes-section { clear: both; margin-top: 50px; font-size: 12px; color: #555; }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td>
                <div class="company-name">{{ auth()->user()->name ?? 'Mon Cabinet' }}</div>
                <div style="color: #666; margin-top: 5px;">Cabinet d'Expertise Comptable</div>
                <div style="color: #888; font-size: 11px;">IFU : 1234567890123</div>
            </td>
            <td>
                <div class="invoice-title">BON DE COMMANDE</div>
                <div class="invoice-number">{{ $po->invoice_number }}</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td>
                <div class="label">FOURNISSEUR</div>
                <div class="client-name">
                    @if($po->partner)
                        {{ $po->partner->company_name ?: $po->partner->first_name.' '.$po->partner->last_name }}
                    @else
                        {{ $po->partner_name ?? '-' }}
                    @endif
                </div>
                @if($po->partner)
                    <div>{{ $po->partner->address }}</div>
                    @if($po->partner->tax_id)
                    <div>IFU: {{ $po->partner->tax_id }}</div>
                    @endif
                @endif
            </td>
            <td>
                <div class="label">DÉTAILS</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 3px 0; color: #555; width: 40%;">Date :</td>
                        <td style="padding: 3px 0; font-weight: bold; text-align: right;">{{ \Carbon\Carbon::parse($po->invoice_date)->format('d/m/Y') }}</td>
                    </tr>
                    @if($po->delivery_date)
                    <tr>
                        <td style="padding: 3px 0; color: #555;">Livraison :</td>
                        <td style="padding: 3px 0; font-weight: bold; text-align: right;">{{ \Carbon\Carbon::parse($po->delivery_date)->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                    @if($po->terms_conditions)
                    <tr>
                        <td style="padding: 3px 0; color: #555;">Réf. Vendeur :</td>
                        <td style="padding: 3px 0; font-weight: bold; text-align: right;">{{ $po->terms_conditions }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table class="lines-table">
        <thead>
            <tr>
                <th>Désignation</th>
                <th class="center">Qté</th>
                <th class="right">Prix unit.</th>
                <th class="center">TVA</th>
                <th class="right">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->lines as $line)
            <tr>
                <td><strong>{{ $line->description }}</strong></td>
                <td class="center">{{ rtrim(rtrim(number_format($line->quantity, 2, ',', ' '), '0'), ',') }}</td>
                <td class="right">{{ number_format($line->unit_price, 0, ',', ' ') }} F</td>
                <td class="center">{{ $line->vat_rate }}%</td>
                <td class="right"><strong>{{ number_format($line->subtotal, 0, ',', ' ') }} F</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td style="color: #555;">Sous-total HT</td>
            <td class="right">{{ number_format($po->subtotal, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td style="color: #555;">TVA</td>
            <td class="right">{{ number_format($po->tax_amount, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr class="grand-total">
            <td style="padding: 12px; font-weight: bold;">Total TTC</td>
            <td class="right" style="padding: 12px;">{{ number_format($po->total, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    <div class="notes-section">
        @if($po->notes)
        <div class="label">NOTES & INSTRUCTIONS</div>
        <p>{{ $po->notes }}</p>
        @endif
    </div>

</body>
</html>
