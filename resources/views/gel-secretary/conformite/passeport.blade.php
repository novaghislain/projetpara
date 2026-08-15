<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Passeport Entreprise GEL® - {{ $client->nom_entreprise }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; margin: 0; padding: 20px; font-size: 14px; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #0d9488; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #0d9488; margin-bottom: 5px; }
        .title { font-size: 20px; text-transform: uppercase; letter-spacing: 1px; color: #1e293b; }
        
        .section { margin-bottom: 30px; }
        .section-title { font-size: 16px; font-weight: bold; color: #0f766e; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 15px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { font-weight: bold; color: #64748b; width: 40%; }
        
        .score-box { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 30px; }
        .score-value { font-size: 48px; font-weight: bold; margin: 10px 0; }
        .score-label { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        
        .color-ok { color: #059669; }
        .color-attention { color: #d97706; }
        .color-ko { color: #dc2626; }
        
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .bg-ok { background: #dcfce7; color: #059669; }
        .bg-attention { background: #fef9c3; color: #d97706; }
        .bg-ko { background: #fee2e2; color: #dc2626; }
        
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        
        /* Utility for 50/50 layout */
        .row { width: 100%; display: block; }
        .col-half { width: 48%; display: inline-block; vertical-align: top; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">GEL ACCOUNTING</div>
        <div class="title">Passeport Entreprise GEL®</div>
        <div style="color: #64748b; margin-top: 5px;">Généré le {{ date('d/m/Y') }}</div>
    </div>

    @php
        $scoreColorClass = $score >= 80 ? 'color-ok' : ($score >= 50 ? 'color-attention' : 'color-ko');
        $scoreLabel = $score >= 80 ? 'Conforme' : ($score >= 50 ? 'Partiellement conforme' : 'Non conforme');
    @endphp

    <div class="score-box">
        <div style="color: #64748b; font-size: 16px;">Score de Conformité Global</div>
        <div class="score-value {{ $scoreColorClass }}">{{ $score }} / 100</div>
        <div class="score-label {{ $scoreColorClass }}">{{ $scoreLabel }}</div>
    </div>

    <div class="row">
        <div class="col-half" style="margin-right: 2%;">
            <div class="section">
                <div class="section-title">Identité Administrative</div>
                <table>
                    <tr><th>Raison Sociale</th><td>{{ $client->nom_entreprise }}</td></tr>
                    <tr><th>IFU</th><td>{{ $client->ifu ?? 'Non renseigné' }}</td></tr>
                    <tr><th>RCCM</th><td>{{ $client->rc ?? 'Non renseigné' }}</td></tr>
                    <tr><th>Secteur</th><td>{{ $client->secteur ?? 'Non renseigné' }}</td></tr>
                    <tr><th>Ville</th><td>{{ $client->ville ?? 'Non renseigné' }}</td></tr>
                </table>
            </div>
        </div>
        <div class="col-half">
            <div class="section">
                <div class="section-title">Indicateurs Financiers</div>
                <table>
                    <tr><th>Chiffre d'Affaires (Est.)</th><td>{{ number_format($ca, 0, ',', ' ') }} FCFA</td></tr>
                    <tr><th>Dernière Activité</th><td>{{ $client->updated_at ? $client->updated_at->format('d/m/Y') : 'N/A' }}</td></tr>
                    <tr><th>Statut Client</th><td>{{ ucfirst($client->statut) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="section" style="page-break-inside: auto;">
        <div class="section-title">Détail des Obligations ({{ $items->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Catégorie</th>
                    <th style="width: 45%;">Obligation</th>
                    <th style="width: 15%;">Statut</th>
                    <th style="width: 15%;">Expiration</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    @php
                        $bgClass = $item->statut === 'ok' ? 'bg-ok' : ($item->statut === 'attention' ? 'bg-attention' : 'bg-ko');
                        $label = $item->statut === 'ok' ? 'OK' : ($item->statut === 'attention' ? 'ATTENTION' : 'KO');
                    @endphp
                    <tr>
                        <td style="text-transform: uppercase; font-size: 12px; color: #64748b;">{{ $item->categorie }}</td>
                        <td style="font-weight: bold;">{{ $item->titre }}</td>
                        <td><span class="badge {{ $bgClass }}">{{ $label }}</span></td>
                        <td style="font-size: 12px;">{{ $item->date_expiration ? $item->date_expiration->format('d/m/Y') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Document confidentiel généré automatiquement par la plateforme GEL Accounting. <br>
        Ce passeport reflète la situation administrative et fiscale au {{ date('d/m/Y à H:i') }}.
    </div>

</body>
</html>
