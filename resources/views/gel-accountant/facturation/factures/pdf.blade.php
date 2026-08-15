<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            margin-bottom: 40px;
        }
        .header td {
            vertical-align: top;
        }
        .company-details {
            width: 50%;
        }
        .client-details {
            width: 50%;
            text-align: right;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .invoice-meta {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 30px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.items th, table.items td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        table.items th {
            background-color: #f8f9fa;
            color: #2c3e50;
            text-align: left;
            text-transform: uppercase;
            font-size: 12px;
        }
        table.items td {
            color: #333;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            width: 40%;
            margin-left: auto;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 8px 0;
        }
        .totals .border-bottom {
            border-bottom: 1px solid #eee;
        }
        .total-row {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="company-details">
                <h1>Mon Cabinet (Cabinet)</h1>
                <div>123 Rue de l'Expertise</div>
                <div>Abidjan, Côte d'Ivoire</div>
                <div>RCCM: CI-ABJ-2023-B-12345</div>
            </td>
            <td class="client-details">
                <div style="color: #7f8c8d; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Facturé à</div>
                <div style="font-size: 18px; font-weight: bold; color: #2c3e50;">
                    {{ $facture->contact->company ?? ($facture->contact->first_name . ' ' . $facture->contact->last_name) }}
                </div>
                @if($facture->contact->address) <div>{{ $facture->contact->address }}</div> @endif
                @if($facture->contact->email) <div>{{ $facture->contact->email }}</div> @endif
                @if($facture->contact->ifu) <div>IFU: {{ $facture->contact->ifu }}</div> @endif
            </td>
        </tr>
    </table>

    <div style="border-bottom: 2px solid #2c3e50; margin-bottom: 20px; padding-bottom: 10px;">
        <span style="font-size: 20px; font-weight: bold; color: #2c3e50;">FACTURE</span>
        <span style="float: right; font-size: 16px; color: #7f8c8d;">N° {{ $facture->numero }}</span>
    </div>
    
    <div class="invoice-meta">
        <div>Date de facturation : {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</div>
        @if($facture->date_echeance)
        <div>Date d'échéance : {{ \Carbon\Carbon::parse($facture->date_echeance)->format('d/m/Y') }}</div>
        @endif
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qté</th>
                <th class="text-right">Prix Unitaire HT</th>
                <th class="text-right">TVA</th>
                <th class="text-right">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->lignes as $ligne)
            <tr>
                <td>{{ $ligne->description }}</td>
                <td class="text-right">{{ $ligne->quantite }}</td>
                <td class="text-right">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                <td class="text-right">{{ $ligne->taux_tva }}%</td>
                <td class="text-right">{{ number_format($ligne->total_ht, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="border-bottom">Total HT</td>
                <td class="text-right border-bottom">{{ number_format($facture->montant_ht, 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td class="border-bottom">Total TVA</td>
                <td class="text-right border-bottom">{{ number_format($facture->montant_tva, 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td class="total-row pt-10">Net à Payer (TTC)</td>
                <td class="text-right total-row pt-10">{{ number_format($facture->montant_ttc, 0, ',', ' ') }} FCFA</td>
            </tr>
        </table>
    </div>

    @if($facture->notes)
    <div style="margin-top: 40px;">
        <div style="font-weight: bold; margin-bottom: 5px;">Notes :</div>
        <div style="color: #7f8c8d; font-size: 13px;">{{ $facture->notes }}</div>
    </div>
    @endif

    <div class="footer">
        Document généré par GEL Accountant - Le {{ date('d/m/Y') }}<br>
        En cas de retard de paiement, des pénalités peuvent s'appliquer conformément à la législation en vigueur.
    </div>
</body>
</html>
