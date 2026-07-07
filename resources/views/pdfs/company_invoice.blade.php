<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->number }}</title>
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        table { width: 100%; border-collapse: collapse; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .mt-20 { margin-top: 20px; }
        .mb-10 { margin-bottom: 10px; }

        .header-table td { vertical-align: top; }

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

        .info-table td { vertical-align: top; padding: 4px 0; }

        .client-box {
            background-color: #f3f4f6;
            padding: 10px;
            border-radius: 4px;
        }

        .items-table { margin-top: 20px; }
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
        .items-table tfoot td {
            border-top: 2px solid #333;
            border-bottom: none;
            padding: 8px;
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-sent { background-color: #dbeafe; color: #1e40af; }
        .status-draft { background-color: #f3f4f6; color: #374151; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        .status-overdue { background-color: #ffedd5; color: #9a3412; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #999;
            padding: 10px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="50%">
                <div class="company-name">{{ $company->company_name ?? $company->name ?? 'Votre Entreprise' }}</div>
                <div>{{ $company->address ?? '' }}</div>
                @if($company->ifu ?? false)
                    <div>IFU : {{ $company->ifu }}</div>
                @endif
                @if($company->phone ?? false)
                    <div>Tél : {{ $company->phone }}</div>
                @endif
                @if($company->email ?? false)
                    <div>Email : {{ $company->email }}</div>
                @endif
            </td>
            <td width="50%" class="text-right">
                <div class="invoice-title">
                    @if($invoice->type === 'credit_note') AVOIR
                    @elseif($invoice->type === 'devis') DEVIS
                    @else FACTURE
                    @endif
                </div>
                <div>N° {{ $invoice->number }}</div>
                <div>Date : {{ $invoice->issue_date?->format('d/m/Y') ?? $invoice->issue_date }}</div>
                <div>Échéance : {{ $invoice->due_date?->format('d/m/Y') ?? $invoice->due_date }}</div>
                <div class="mt-20">
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ match($invoice->status) {
                            'paid' => 'PAYÉE',
                            'sent' => 'ENVOYÉE',
                            'draft' => 'BROUILLON',
                            'cancelled' => 'ANNULÉE',
                            'overdue' => 'EN RETARD',
                            default => $invoice->status,
                        } }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table mt-20">
        <tr>
            <td width="50%">
                <div class="bold mb-10">CLIENT</div>
                <div><strong>{{ $invoice->recipient_name }}</strong></div>
                @if($invoice->recipient_address)
                    <div>{{ $invoice->recipient_address }}</div>
                @endif
            </td>
            <td width="50%"></td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="40%">Désignation</th>
                <th width="10%" class="text-right">Qté</th>
                <th width="15%" class="text-right">Prix unit.</th>
                <th width="10%" class="text-right">TVA</th>
                <th width="20%" class="text-right">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                <td class="text-right">{{ $item->tax_rate }}%</td>
                <td class="text-right">{{ number_format($item->total_ttc, 0, ',', ' ') }} FCFA</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Aucune ligne</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"></td>
                <td>Total HT</td>
                <td class="text-right">{{ number_format($invoice->total_ht, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if((float) $invoice->total_tva > 0)
            <tr>
                <td colspan="4"></td>
                <td>TVA</td>
                <td class="text-right">{{ number_format($invoice->total_tva, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            <tr>
                <td colspan="4"></td>
                <td>Total TTC</td>
                <td class="text-right">{{ number_format($invoice->total_ttc, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if((float) $invoice->paid_amount > 0)
            <tr>
                <td colspan="4"></td>
                <td>Payé</td>
                <td class="text-right">{{ number_format($invoice->paid_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
        </tfoot>
    </table>

    @if($invoice->notes)
    <div class="mt-20">
        <div class="bold">Notes :</div>
        <div>{{ $invoice->notes }}</div>
    </div>
    @endif

    <div class="footer">
        Document généré par GEL Cabinet — www.gelcabinet.com
    </div>

</body>
</html>
