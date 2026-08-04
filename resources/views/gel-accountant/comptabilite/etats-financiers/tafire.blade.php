@extends('layouts.gel-accountant')

@section('title', 'Tableau Financier des Ressources et Emplois (TAFIRE)')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-balance-scale" style="color:var(--gel-primary); margin-right:8px;"></i> TAFIRE</h1>
        <p class="gel-page-subtitle">Tableau Financier des Ressources et Emplois SYSCOHADA.</p>
    </div>
</div>

<div class="gel-card p-4">
    <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
        <h3 style="font-size:16px; font-weight:700;">TAFIRE AU {{ date('d/m/Y') }}</h3>
        <a href="?type=tafire&export=pdf" target="_blank" class="gel-btn gel-btn-secondary"><i class="fas fa-download"></i> Exporter PDF</a>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        {{-- EMPLOIS --}}
        <div>
            <h4 style="background:var(--gel-warning); color:#333; padding:8px 12px; font-size:14px; margin-bottom:0;">EMPLOIS</h4>
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Rubrique</th>
                        <th style="text-align:right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight:600;">Acquisitions d'immobilisations</td>
                        <td style="text-align:right;">{{ number_format($emplois['acquisitions'], 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Remboursements de dettes financières</td>
                        <td style="text-align:right;">{{ number_format($emplois['remboursements'], 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Dividendes versés</td>
                        <td style="text-align:right;">{{ number_format($emplois['dividendes'], 0, ',', ' ') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background:#F4F5F8; font-weight:700;">
                        <td>TOTAL EMPLOIS</td>
                        <td style="text-align:right;">{{ number_format($total_emplois, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        {{-- RESSOURCES --}}
        <div>
            <h4 style="background:var(--gel-success); color:white; padding:8px 12px; font-size:14px; margin-bottom:0;">RESSOURCES</h4>
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Rubrique</th>
                        <th style="text-align:right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight:600;">Capacité d'Autofinancement Globale (CAFG)</td>
                        <td style="text-align:right;">{{ number_format($ressources['cafg'], 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Cessions d'immobilisations</td>
                        <td style="text-align:right;">{{ number_format($ressources['cessions'], 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Augmentation de capital</td>
                        <td style="text-align:right;">{{ number_format($ressources['augmentation_capital'], 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Nouveaux emprunts</td>
                        <td style="text-align:right;">{{ number_format($ressources['emprunts'], 0, ',', ' ') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background:#F4F5F8; font-weight:700;">
                        <td>TOTAL RESSOURCES</td>
                        <td style="text-align:right;">{{ number_format($total_ressources, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection
