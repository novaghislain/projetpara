<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} - {{ $fiscalYear->year }}</title>
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
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .total-row { background: #f9f9f9; font-weight: bold; }
        .sig-pos { color: #198754; }
        .sig-neg { color: #dc3545; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 7pt; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="client">{{ $clientName }}</div>
        <h1>SIG — Soldes Intermédiaires de Gestion</h1>
        <div class="meta">Exercice {{ $fiscalYear->year }} — Éditée le {{ $date }}</div>
    </div>

    <table>
        <thead>
            <tr><th>Indicateur</th><th class="text-end">Montant</th></tr>
        </thead>
        <tbody>
            <tr><td>Ventes</td><td class="text-end">{{ number_format($sig['ventes'], 2, ',', ' ') }}</td></tr>
            <tr class="total-row"><td>Marge commerciale</td><td class="text-end">{{ number_format($sig['marge_commerciale'], 2, ',', ' ') }}</td></tr>
            <tr><td>Services externes</td><td class="text-end">{{ number_format($sig['services_externes'], 2, ',', ' ') }}</td></tr>
            <tr class="total-row"><td>Valeur ajoutée</td><td class="text-end">{{ number_format($sig['valeur_ajoutee'], 2, ',', ' ') }}</td></tr>
            <tr><td>Impôts et taxes</td><td class="text-end">{{ number_format($sig['impots_taxes'], 2, ',', ' ') }}</td></tr>
            <tr><td>Charges de personnel</td><td class="text-end">{{ number_format($sig['charges_personnel'], 2, ',', ' ') }}</td></tr>
            <tr class="total-row"><td>EBITDA</td><td class="text-end {{ $sig['ebitda'] >= 0 ? 'sig-pos' : 'sig-neg' }}">{{ number_format($sig['ebitda'], 2, ',', ' ') }}</td></tr>
            <tr><td>Dotations</td><td class="text-end">{{ number_format($sig['dotations'], 2, ',', ' ') }}</td></tr>
            <tr class="total-row"><td>Résultat d'exploitation</td><td class="text-end {{ $sig['resultat_exploitation'] >= 0 ? 'sig-pos' : 'sig-neg' }}">{{ number_format($sig['resultat_exploitation'], 2, ',', ' ') }}</td></tr>
            <tr><td>Produits financiers</td><td class="text-end">{{ number_format($sig['produits_financiers'], 2, ',', ' ') }}</td></tr>
            <tr><td>Frais financiers</td><td class="text-end">{{ number_format($sig['frais_financiers'], 2, ',', ' ') }}</td></tr>
            <tr><td>Autres produits</td><td class="text-end">{{ number_format($sig['autres_produits'], 2, ',', ' ') }}</td></tr>
            <tr><td>Autres charges</td><td class="text-end">{{ number_format($sig['autres_charges'], 2, ',', ' ') }}</td></tr>
            <tr class="total-row"><td>Résultat avant impôts</td><td class="text-end {{ $sig['resultat_avant_impots'] >= 0 ? 'sig-pos' : 'sig-neg' }}">{{ number_format($sig['resultat_avant_impots'], 2, ',', ' ') }}</td></tr>
        </tbody>
    </table>

    <div class="footer">GEL Cabinet — Analyse financière conforme SYSCOHADA</div>
</body>
</html>
