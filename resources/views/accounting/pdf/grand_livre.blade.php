<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} - {{ $fiscalYear->year }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #333; }
        h1 { font-size: 16pt; color: #FF7900; margin-bottom: 5px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header .client { font-size: 14pt; font-weight: bold; }
        .header .meta { font-size: 8pt; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 3px 5px; border: 1px solid #ccc; text-align: left; font-size: 8pt; }
        th { background: #f5f5f5; font-weight: 600; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .total-row { background: #f9f9f9; font-weight: bold; }
        .section-title { background: #FF7900; color: white; font-weight: bold; text-align: center; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 7pt; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="client">{{ $clientName }}</div>
        <h1>{{ $title }}</h1>
        <div class="meta">Exercice {{ $fiscalYear->year }} @if($accountCode) — Compte {{ $accountCode }} @endif</div>
        <div class="meta">Éditée le {{ $date }}</div>
    </div>

    @php
        $currentCode = '';
        $runningBalance = 0;
    @endphp

    @foreach ($lines as $line)
        @if ($currentCode !== $line->code)
            @php $currentCode = $line->code; $runningBalance = 0; @endphp
            <table>
                <tr class="section-title"><td colspan="5">{{ $line->code }} — {{ $line->name }}</td></tr>
                <thead>
                    <tr><th>Date</th><th>Pièce</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th></tr>
                </thead>
                <tbody>
        @endif
        <tr>
            <td>{{ $line->entry_date }}</td>
            <td>{{ $line->numero_piece ?: $line->reference }}</td>
            <td>{{ $line->label }}</td>
            <td class="text-end">{{ number_format($line->debit, 2, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($line->credit, 2, ',', ' ') }}</td>
        </tr>
        @if ($loop->last || $lines[$loop->index + 1]->code !== $currentCode)
                </tbody>
            </table>
        @endif
    @endforeach

    <div class="footer">GEL Cabinet — Document généré automatiquement</div>
</body>
</html>
