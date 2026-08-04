@extends('layouts.gel-accountant')

@section('title', 'Bilan SYSCOHADA (Actif / Passif)')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-file-invoice" style="color:var(--gel-primary); margin-right:8px;"></i> Bilan (Actif / Passif)</h1>
        <p class="gel-page-subtitle">État financier conforme au SYSCOHADA révisé.</p>
    </div>
</div>

<div class="gel-card p-4">
    <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
        <h3 style="font-size:16px; font-weight:700;">BILAN AU {{ date('d/m/Y') }}</h3>
        <a href="?type=bilan&export=pdf" target="_blank" class="gel-btn gel-btn-secondary"><i class="fas fa-download"></i> Exporter PDF</a>
    </div>

    @php
        $actifImmo = $actif['incorporelles'] + $actif['corporelles'] + $actif['financieres'];
        $actifCirc = $actif['stocks'] + $actif['creances_clients'] + $actif['autres_creances'];
        $tresoActif = $actif['tresorerie'];
        
        $capitauxPropres = $passif['capital'] + $passif['reserves'] + $passif['report_nouveau'] + $passif['resultat_net'];
        $dettesFin = $passif['emprunts'];
        $passifCirc = $passif['fournisseurs'] + $passif['dettes_fiscales'];
        $tresoPassif = $passif['decouverts'];
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        {{-- ACTIF --}}
        <div>
            <h4 style="background:var(--gel-primary); color:white; padding:8px 12px; font-size:14px; margin-bottom:0;">ACTIF</h4>
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Rubrique</th>
                        <th style="text-align:right;">Brut</th>
                        <th style="text-align:right;">Amort/Prov</th>
                        <th style="text-align:right;">Net</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight:600;">Actif Immobilisé</td>
                        <td style="text-align:right;">{{ number_format($actifImmo, 0, ',', ' ') }}</td>
                        <td style="text-align:right;">0</td>
                        <td style="text-align:right;">{{ number_format($actifImmo, 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Actif Circulant</td>
                        <td style="text-align:right;">{{ number_format($actifCirc, 0, ',', ' ') }}</td>
                        <td style="text-align:right;">0</td>
                        <td style="text-align:right;">{{ number_format($actifCirc, 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Trésorerie Actif</td>
                        <td style="text-align:right;">{{ number_format($tresoActif, 0, ',', ' ') }}</td>
                        <td style="text-align:right;">0</td>
                        <td style="text-align:right;">{{ number_format($tresoActif, 0, ',', ' ') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background:#F4F5F8; font-weight:700;">
                        <td>TOTAL ACTIF</td>
                        <td style="text-align:right;">{{ number_format($total_actif, 0, ',', ' ') }}</td>
                        <td style="text-align:right;">0</td>
                        <td style="text-align:right; color:var(--gel-primary);">{{ number_format($total_actif, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        {{-- PASSIF --}}
        <div>
            <h4 style="background:var(--gel-primary); color:white; padding:8px 12px; font-size:14px; margin-bottom:0;">PASSIF</h4>
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Rubrique</th>
                        <th style="text-align:right;">Net</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight:600;">Capitaux Propres</td>
                        <td style="text-align:right;">{{ number_format($capitauxPropres, 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Dettes Financières</td>
                        <td style="text-align:right;">{{ number_format($dettesFin, 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Passif Circulant</td>
                        <td style="text-align:right;">{{ number_format($passifCirc, 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Trésorerie Passif</td>
                        <td style="text-align:right;">{{ number_format($tresoPassif, 0, ',', ' ') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background:#F4F5F8; font-weight:700;">
                        <td>TOTAL PASSIF</td>
                        <td style="text-align:right; color:var(--gel-primary);">{{ number_format($total_passif, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection
