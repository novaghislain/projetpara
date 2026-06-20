<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} - {{ $fiscalYear->year }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #333; }
        h1 { font-size: 16pt; color: #FF7900; margin-bottom: 5px; }
        h2 { font-size: 12pt; color: #333; border-bottom: 2px solid #FF7900; padding-bottom: 3px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header .client { font-size: 14pt; font-weight: bold; }
        .header .meta { font-size: 8pt; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 4px 6px; border: 1px solid #ccc; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; font-size: 9pt; }
        td { font-size: 9pt; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .total-row { background: #f9f9f9; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 7pt; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
        .solde-pos { color: #198754; }
        .solde-neg { color: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <div class="client">{{ $clientName }}</div>
        <h1>{{ $title }}</h1>
        <div class="meta">Exercice {{ $fiscalYear->year }} ({{ $fiscalYear->date_start->format('d/m/Y') }} → {{ $fiscalYear->date_end->format('d/m/Y') }})</div>
        <div class="meta">Éditée le {{ $date }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Compte</th>
                <th>Libellé</th>
                <th class="text-end">Débit</th>
                <th class="text-end">Crédit</th>
                <th class="text-end">Solde</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($balance as $item)
            <tr>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td class="text-end">{{ number_format($item->total_debit, 2, ',', ' ') }}</td>
                <td class="text-end">{{ number_format($item->total_credit, 2, ',', ' ') }}</td>
                <td class="text-end {{ $item->solde >= 0 ? 'solde-pos' : 'solde-neg' }}">{{ number_format($item->solde, 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2">TOTAUX GÉNÉRAUX</td>
                <td class="text-end">{{ number_format($equilibre['total_debit'], 2, ',', ' ') }}</td>
                <td class="text-end">{{ number_format($equilibre['total_credit'], 2, ',', ' ') }}</td>
                <td class="text-end">{{ number_format($equilibre['difference'], 2, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="text-align:center; margin-top:15px;">
        @if ($equilibre['equilibre'])
        <span style="color:#198754;font-weight:bold;">✓ Balance équilibrée</span>
        @else
        <span style="color:#dc3545;font-weight:bold;">✗ Balance NON équilibrée (différence : {{ number_format($equilibre['difference'], 2, ',', ' ') }})</span>
        @endif
    </p>

    <div class="footer">GEL Cabinet — Document généré automatiquement</div>
</body>
</html>
