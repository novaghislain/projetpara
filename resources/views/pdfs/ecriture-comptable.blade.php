<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Écriture {{ $ecriture->numero }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #222; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1a3c6e; padding-bottom: 10px; }
        .header h1 { font-size: 14pt; color: #1a3c6e; margin: 0 0 4px 0; }
        .header p { margin: 0; color: #666; font-size: 8pt; }
        .meta-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .meta-table td { padding: 2px 6px; vertical-align: top; }
        .meta-table .label { font-weight: bold; color: #1a3c6e; width: 120px; }
        .lignes-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .lignes-table th { background: #1a3c6e; color: white; padding: 6px 8px; text-align: left; font-size: 8pt; }
        .lignes-table td { padding: 4px 8px; border-bottom: 1px solid #ddd; }
        .lignes-table .montant { text-align: right; font-family: 'DejaVu Sans Mono', monospace; }
        .lignes-table .total-row { background: #f0f4f8; font-weight: bold; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 8pt; }
        .badge-success { background: #e6fcf5; color: #0ca678; border: 1px solid #0ca678; }
        .badge-warning { background: #fff3bf; color: #e67700; border: 1px solid #e67700; }
        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 7pt; color: #999; border-top: 1px solid #ddd; padding-top: 4px; }
        .info-box { background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 8px 12px; margin-bottom: 12px; }
        .info-box h3 { margin: 0 0 6px 0; font-size: 10pt; color: #1a3c6e; }
    </style>
</head>
<body>

    <div class="header">
        <h1>ÉCRITURE COMPTABLE</h1>
        <p>Extrait du journal {{ $ecriture->journal?->code }} — {{ $ecriture->journal?->libelle }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">Numéro :</td>
            <td><strong>{{ $ecriture->numero }}</strong></td>
            <td class="label">Date écriture :</td>
            <td>{{ $ecriture->date_ecriture->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Exercice :</td>
            <td>{{ $ecriture->exercice?->libelle }}</td>
            <td class="label">Date pièce :</td>
            <td>{{ $ecriture->date_piece->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Réf. pièce :</td>
            <td>{{ $ecriture->reference_piece ?? '—' }}</td>
            <td class="label">Client :</td>
            <td>{{ $ecriture->client?->company_name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Statut :</td>
            <td colspan="3">
                @if($ecriture->valide)
                    <span class="badge badge-success">Validée</span>
                    <span style="font-size:7pt;color:#666;">
                        par {{ $ecriture->validateur?->name ?? '—' }}
                        le {{ $ecriture->date_validation?->format('d/m/Y H:i') ?? '—' }}
                    </span>
                @else
                    <span class="badge badge-warning">Brouillon</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="info-box">
        <h3>Libellé</h3>
        <p style="margin:0;">{{ $ecriture->libelle }}</p>
    </div>

    <table class="lignes-table">
        <thead>
            <tr>
                <th style="width:12%;">Compte</th>
                <th style="width:30%;">Intitulé</th>
                <th style="width:30%;">Libellé ligne</th>
                <th style="width:14%;" class="montant">Débit</th>
                <th style="width:14%;" class="montant">Crédit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ecriture->lignes as $ligne)
            <tr>
                <td style="font-family:'DejaVu Sans Mono',monospace;font-weight:bold;">{{ $ligne->compte?->code }}</td>
                <td>{{ $ligne->compte?->intitule }}</td>
                <td>{{ $ligne->libelle_ligne ?? '—' }}</td>
                <td class="montant">
                    @if($ligne->sens === 'debit')
                        {{ number_format((float) $ligne->montant, 0, ',', ' ') }}
                    @endif
                </td>
                <td class="montant">
                    @if($ligne->sens === 'credit')
                        {{ number_format((float) $ligne->montant, 0, ',', ' ') }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align:right;padding:6px 8px;">TOTAUX</td>
                <td class="montant" style="padding:6px 8px;">{{ number_format((float) $ecriture->total_debit, 0, ',', ' ') }}</td>
                <td class="montant" style="padding:6px 8px;">{{ number_format((float) $ecriture->total_credit, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($ecriture->valide)
    <div style="margin-top:30px;text-align:right;font-size:8pt;color:#666;">
        <p>Validé par : {{ $ecriture->validateur?->name ?? '—' }}</p>
        <p>Le : {{ $ecriture->date_validation?->format('d/m/Y H:i') ?? '—' }}</p>
    </div>
    @endif

    <div class="footer">
        Document généré par GEL Cabinet — {{ $ecriture->cabinet?->name ?? 'Cabinet' }} — {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
