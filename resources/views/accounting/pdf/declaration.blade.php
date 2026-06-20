<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #333; }
        h1 { font-size: 16pt; color: #FF7900; margin-bottom: 5px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header .client { font-size: 14pt; font-weight: bold; }
        .header .meta { font-size: 8pt; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 5px 8px; border: 1px solid #ccc; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; font-size: 9pt; }
        td { font-size: 9pt; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .total-row { background: #f9f9f9; font-weight: bold; }
        .paye { color: #198754; }
        .en_retard { color: #dc3545; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 7pt; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="client">{{ $clientName }}</div>
        <h1>{{ $title }}</h1>
        <div class="meta">Réf. {{ $declaration->reference }} — Éditée le {{ $date }}</div>
    </div>

    <table>
        <tr><th colspan="2">Informations générales</th></tr>
        <tr><td>Type</td><td>{{ $declaration->libelle_type }}</td></tr>
        <tr><td>Période</td><td>{{ $declaration->period_month ? 'Mois ' . $declaration->period_month . ' ' : '' }}{{ $declaration->period_year }}</td></tr>
        <tr><td>Période couverte</td><td>{{ $declaration->date_debut }} → {{ $declaration->date_fin }}</td></tr>
        <tr><td>Échéance</td><td>{{ $declaration->date_echeance }}</td></tr>
        <tr><td>Statut</td><td>{{ $declaration->status }}</td></tr>
    </table>

    <h2>Montants</h2>
    <table>
        <tr><td>Base imposable</td><td class="text-end">{{ number_format($declaration->base_imposable, 2, ',', ' ') }}</td></tr>
        <tr><td>Taux</td><td class="text-end">{{ $declaration->taux }}%</td></tr>
        <tr class="total-row"><td>Montant dû</td><td class="text-end">{{ number_format($declaration->montant_dut, 2, ',', ' ') }}</td></tr>
        <tr><td>Montant payé</td><td class="text-end">{{ number_format($declaration->montant_paye, 2, ',', ' ') }}</td></tr>
        <tr><td>Pénalités</td><td class="text-end">{{ number_format($declaration->penalites, 2, ',', ' ') }}</td></tr>
        <tr class="total-row"><td>Solde</td><td class="text-end {{ $declaration->solde > 0 ? 'en_retard' : 'paye' }}">{{ number_format($declaration->solde, 2, ',', ' ') }}</td></tr>
    </table>

    @if ($declaration->notes)
    <h2>Notes</h2>
    <p>{{ $declaration->notes }}</p>
    @endif

    <div class="footer">GEL Cabinet — Déclaration fiscale générée automatiquement</div>
</body>
</html>
