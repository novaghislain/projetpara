<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} - {{ $fiscalYear->year }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #333; }
        h1 { font-size: 16pt; color: #FF7900; margin-bottom: 5px; }
        h2 { font-size: 12pt; color: #333; border-bottom: 2px solid #FF7900; padding-bottom: 3px; margin-top: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header .client { font-size: 14pt; font-weight: bold; }
        .header .meta { font-size: 8pt; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 4px 6px; border: 1px solid #ccc; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; font-size: 9pt; }
        td { font-size: 9pt; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .total-row { background: #f9f9f9; font-weight: bold; }
        .total-global { background: #FF7900; color: white; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 7pt; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="client">{{ $clientName }}</div>
        <h1>{{ $title }}</h1>
        <div class="meta">Arrêté au {{ $bilan['date'] }}</div>
        <div class="meta">Éditée le {{ $date }}</div>
    </div>

    <h2>ACTIF</h2>
    <table>
        <thead>
            <tr><th>Rubrique</th><th class="text-end">Montant</th></tr>
        </thead>
        <tbody>
            @foreach ($bilan['actif']['rubriques'] as $key => $rubrique)
            <tr>
                <td>{{ str_replace('_', ' ', ucfirst($key)) }}</td>
                <td class="text-end">{{ number_format($rubrique['montant'], 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-global">
                <td>TOTAL ACTIF</td>
                <td class="text-end">{{ number_format($bilan['actif']['total'], 2, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    <h2>PASSIF</h2>
    <table>
        <thead>
            <tr><th>Rubrique</th><th class="text-end">Montant</th></tr>
        </thead>
        <tbody>
            @foreach ($bilan['passif']['rubriques'] as $key => $rubrique)
            <tr>
                <td>{{ str_replace('_', ' ', ucfirst($key)) }}</td>
                <td class="text-end">{{ number_format($rubrique['montant'], 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-global">
                <td>TOTAL PASSIF</td>
                <td class="text-end">{{ number_format($bilan['passif']['total'], 2, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">GEL Cabinet — Document conforme SYSCOHADA Révisé</div>
</body>
</html>
