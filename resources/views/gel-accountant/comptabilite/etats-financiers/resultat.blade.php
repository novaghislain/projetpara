@extends('layouts.gel-accountant')

@section('title', 'Compte de Résultat SYSCOHADA')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-chart-line" style="color:var(--gel-primary); margin-right:8px;"></i> Compte de Résultat</h1>
        <p class="gel-page-subtitle">État financier conforme au SYSCOHADA révisé.</p>
    </div>
</div>

<div class="gel-card p-4">
    <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
        <h3 style="font-size:16px; font-weight:700;">COMPTE DE RÉSULTAT AU {{ date('d/m/Y') }}</h3>
        <a href="?type=resultat&export=pdf" target="_blank" class="gel-btn gel-btn-secondary"><i class="fas fa-download"></i> Exporter PDF</a>
    </div>

    @php
        $ch_ord = $charges['achats_marchandises'] + $charges['achats_matieres'] + $charges['autres_achats'] + $charges['services_exterieurs'] + $charges['impots'] + $charges['personnel'] + $charges['dotations'];
        $ch_fin = $charges['financieres'];
        $ch_hao = $charges['hao'];

        $pr_ord = $produits['ventes_marchandises'] + $produits['ventes_produits'] + $produits['services_vendus'] + $produits['accessoires'] + $produits['subventions'] + $produits['reprises'];
        $pr_fin = $produits['financiers'];
        $pr_hao = $produits['hao'];
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        {{-- CHARGES --}}
        <div>
            <h4 style="background:var(--gel-danger); color:white; padding:8px 12px; font-size:14px; margin-bottom:0;">CHARGES</h4>
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Rubrique</th>
                        <th style="text-align:right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight:600;">Activités Ordinaires</td>
                        <td style="text-align:right;">{{ number_format($ch_ord, 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Charges Financières</td>
                        <td style="text-align:right;">{{ number_format($ch_fin, 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Hors Activités Ordinaires</td>
                        <td style="text-align:right;">{{ number_format($ch_hao, 0, ',', ' ') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background:#F4F5F8; font-weight:700;">
                        <td>TOTAL CHARGES</td>
                        <td style="text-align:right; color:var(--gel-danger);">{{ number_format($total_charges, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        {{-- PRODUITS --}}
        <div>
            <h4 style="background:var(--gel-success); color:white; padding:8px 12px; font-size:14px; margin-bottom:0;">PRODUITS</h4>
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Rubrique</th>
                        <th style="text-align:right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight:600;">Activités Ordinaires</td>
                        <td style="text-align:right;">{{ number_format($pr_ord, 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Produits Financiers</td>
                        <td style="text-align:right;">{{ number_format($pr_fin, 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;">Hors Activités Ordinaires</td>
                        <td style="text-align:right;">{{ number_format($pr_hao, 0, ',', ' ') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background:#F4F5F8; font-weight:700;">
                        <td>TOTAL PRODUITS</td>
                        <td style="text-align:right; color:var(--gel-success);">{{ number_format($total_produits, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="mt-4 p-3" style="background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0; font-size:16px;">RÉSULTAT NET (Bénéfice ou Perte)</h3>
        <span style="font-size:20px; font-weight:bold; color:{{ $resultat_net >= 0 ? 'var(--gel-success)' : 'var(--gel-danger)' }};">
            {{ number_format($resultat_net, 0, ',', ' ') }} FCFA
        </span>
    </div>
</div>

@endsection
