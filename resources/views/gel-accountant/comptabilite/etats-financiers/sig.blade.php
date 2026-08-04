@extends('layouts.gel-accountant')

@section('title', 'Soldes Intermédiaires de Gestion (SIG)')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-chart-area" style="color:var(--gel-primary); margin-right:8px;"></i> Soldes Intermédiaires de Gestion</h1>
        <p class="gel-page-subtitle">Indicateurs de performance SYSCOHADA (Marge, Valeur Ajoutée, EBE...)</p>
    </div>
</div>

<div class="gel-card p-4">
    <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
        <h3 style="font-size:16px; font-weight:700;">SIG AU {{ date('d/m/Y') }}</h3>
        <a href="?type=sig&export=pdf" target="_blank" class="gel-btn gel-btn-secondary"><i class="fas fa-download"></i> Exporter PDF</a>
    </div>

    <table class="gel-table">
        <thead>
            <tr>
                <th style="width:70%;">Solde / Indicateur</th>
                <th style="width:30%; text-align:right;">Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight:600;">Marge Commerciale</td>
                <td style="text-align:right;">{{ number_format($marge_commerciale, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="font-weight:600;">Valeur Ajoutée (VA)</td>
                <td style="text-align:right;">{{ number_format($valeur_ajoutee, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="font-weight:600;">Excédent Brut d'Exploitation (EBE)</td>
                <td style="text-align:right;">{{ number_format($ebe, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="font-weight:600;">Résultat d'Exploitation (RE)</td>
                <td style="text-align:right;">{{ number_format($resultat_exploitation, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="font-weight:600;">Résultat Financier</td>
                <td style="text-align:right;">{{ number_format($resultat_financier, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="font-weight:600;">Résultat des Activités Ordinaires (RAO)</td>
                <td style="text-align:right;">{{ number_format($rao, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="font-weight:600;">Résultat Hors Activités Ordinaires (RHAO)</td>
                <td style="text-align:right;">{{ number_format($rhao, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background:#ECFDF5; font-weight:700;">
                <td>RÉSULTAT NET</td>
                <td style="text-align:right; color:var(--gel-success); font-size:16px;">{{ number_format($resultat_net, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tfoot>
    </table>
</div>

@endsection
