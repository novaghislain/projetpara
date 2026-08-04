@extends('layouts.gel-accountant')

@section('title', 'Flux de Trésorerie SYSCOHADA')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-coins" style="color:var(--gel-primary); margin-right:8px;"></i> État des Flux de Trésorerie</h1>
        <p class="gel-page-subtitle">Suivi des encaissements et décaissements SYSCOHADA.</p>
    </div>
</div>

<div class="gel-card p-4">
    <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
        <h3 style="font-size:16px; font-weight:700;">FLUX DE TRÉSORERIE AU {{ date('d/m/Y') }}</h3>
        <a href="?type=tresorerie&export=pdf" target="_blank" class="gel-btn gel-btn-secondary"><i class="fas fa-download"></i> Exporter PDF</a>
    </div>

    @php
        $treso_exp = $activite_encaissements - $activite_decaissements;
        $treso_inv = $investissement_cessions - $investissement_acquisitions;
        $treso_fin = $financement_capitaux + $financement_emprunts - $financement_remboursements - $financement_dividendes;
    @endphp

    <table class="gel-table">
        <thead>
            <tr>
                <th style="width:70%;">Flux</th>
                <th style="width:30%; text-align:right;">Montant Net (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background:#F4F5F8;">
                <td style="font-weight:700;" colspan="2">A - Flux de trésorerie liés à l'activité opérationnelle</td>
            </tr>
            <tr>
                <td style="padding-left:24px;">Encaissements liés à l'activité</td>
                <td style="text-align:right;">{{ number_format($activite_encaissements, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="padding-left:24px;">Décaissements liés à l'activité</td>
                <td style="text-align:right;">{{ number_format($activite_decaissements, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="font-weight:600; text-align:right;">Trésorerie Nette d'Exploitation</td>
                <td style="text-align:right; font-weight:600;">{{ number_format($treso_exp, 0, ',', ' ') }}</td>
            </tr>

            <tr style="background:#F4F5F8;">
                <td style="font-weight:700;" colspan="2">B - Flux de trésorerie liés aux activités d'investissement</td>
            </tr>
            <tr>
                <td style="padding-left:24px;">Décaissements pour acquisition d'immobilisations</td>
                <td style="text-align:right;">{{ number_format($investissement_acquisitions, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="padding-left:24px;">Encaissements liés aux cessions d'immobilisations</td>
                <td style="text-align:right;">{{ number_format($investissement_cessions, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="font-weight:600; text-align:right;">Trésorerie Nette d'Investissement</td>
                <td style="text-align:right; font-weight:600;">{{ number_format($treso_inv, 0, ',', ' ') }}</td>
            </tr>

            <tr style="background:#F4F5F8;">
                <td style="font-weight:700;" colspan="2">C - Flux de trésorerie liés aux activités de financement</td>
            </tr>
            <tr>
                <td style="padding-left:24px;">Encaissements suite à l'émission d'emprunts</td>
                <td style="text-align:right;">{{ number_format($financement_emprunts, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="padding-left:24px;">Remboursements d'emprunts</td>
                <td style="text-align:right;">{{ number_format($financement_remboursements, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td style="font-weight:600; text-align:right;">Trésorerie Nette de Financement</td>
                <td style="text-align:right; font-weight:600;">{{ number_format($treso_fin, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background:#ECFDF5; font-weight:700;">
                <td>VARIATION GLOBALE DE LA TRÉSORERIE (A + B + C)</td>
                <td style="text-align:right; color:var(--gel-success); font-size:16px;">{{ number_format($treso_exp + $treso_inv + $treso_fin, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tfoot>
    </table>
</div>

@endsection
