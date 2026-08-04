@extends('gel-accountant.comptabilite.etats-financiers.pdf.layout')

@section('title', 'Compte de Résultat')
@section('document_title', 'Compte de Résultat')

@section('content')

@php
    $ch_ord = $charges['achats_marchandises'] + $charges['achats_matieres'] + $charges['autres_achats'] + $charges['services_exterieurs'] + $charges['impots'] + $charges['personnel'] + $charges['dotations'];
    $ch_fin = $charges['financieres'];
    $ch_hao = $charges['hao'];

    $pr_ord = $produits['ventes_marchandises'] + $produits['ventes_produits'] + $produits['services_vendus'] + $produits['accessoires'] + $produits['subventions'] + $produits['reprises'];
    $pr_fin = $produits['financiers'];
    $pr_hao = $produits['hao'];
@endphp

<div>
    <!-- Colonne Charges -->
    <div class="col-half">
        <table class="syscohada-table">
            <thead>
                <tr>
                    <th colspan="2">CHARGES</th>
                </tr>
                <tr>
                    <th class="text-left">RUBRIQUE</th>
                    <th>MONTANT</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section-title">
                    <td colspan="2">Activités Ordinaires</td>
                </tr>
                <tr>
                    <td class="text-left">Achats de marchandises</td>
                    <td>{{ number_format($charges['achats_marchandises'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Achats de matières premières</td>
                    <td>{{ number_format($charges['achats_matieres'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Autres achats et charges externes</td>
                    <td>{{ number_format($charges['autres_achats'] + $charges['services_exterieurs'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Impôts et taxes</td>
                    <td>{{ number_format($charges['impots'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Charges de personnel</td>
                    <td>{{ number_format($charges['personnel'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Dotations aux amortissements</td>
                    <td>{{ number_format($charges['dotations'], 0, ',', ' ') }}</td>
                </tr>
                
                <tr class="section-title">
                    <td colspan="2">Charges Financières</td>
                </tr>
                <tr>
                    <td class="text-left">Frais financiers</td>
                    <td>{{ number_format($ch_fin, 0, ',', ' ') }}</td>
                </tr>

                <tr class="section-title">
                    <td colspan="2">Hors Activités Ordinaires</td>
                </tr>
                <tr>
                    <td class="text-left">Charges HAO</td>
                    <td>{{ number_format($ch_hao, 0, ',', ' ') }}</td>
                </tr>

                <tr class="total-row">
                    <td class="text-left">TOTAL CHARGES</td>
                    <td>{{ number_format($total_charges, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="col-spacer"></div>

    <!-- Colonne Produits -->
    <div class="col-half">
        <table class="syscohada-table">
            <thead>
                <tr>
                    <th colspan="2">PRODUITS</th>
                </tr>
                <tr>
                    <th class="text-left">RUBRIQUE</th>
                    <th>MONTANT</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section-title">
                    <td colspan="2">Activités Ordinaires</td>
                </tr>
                <tr>
                    <td class="text-left">Ventes de marchandises</td>
                    <td>{{ number_format($produits['ventes_marchandises'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Ventes de produits fabriqués</td>
                    <td>{{ number_format($produits['ventes_produits'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Travaux et services vendus</td>
                    <td>{{ number_format($produits['services_vendus'] + $produits['accessoires'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Subventions & Reprises</td>
                    <td>{{ number_format($produits['subventions'] + $produits['reprises'], 0, ',', ' ') }}</td>
                </tr>
                
                <tr class="section-title">
                    <td colspan="2">Produits Financiers</td>
                </tr>
                <tr>
                    <td class="text-left">Revenus financiers</td>
                    <td>{{ number_format($pr_fin, 0, ',', ' ') }}</td>
                </tr>

                <tr class="section-title">
                    <td colspan="2">Hors Activités Ordinaires</td>
                </tr>
                <tr>
                    <td class="text-left">Produits HAO</td>
                    <td>{{ number_format($pr_hao, 0, ',', ' ') }}</td>
                </tr>

                <tr class="total-row">
                    <td class="text-left">TOTAL PRODUITS</td>
                    <td>{{ number_format($total_produits, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 20px; padding: 10px; border: 2px solid #000; text-align: center; font-size: 14px; font-weight: bold;">
    RÉSULTAT NET DE L'EXERCICE : {{ number_format($resultat_net, 0, ',', ' ') }} FCFA
</div>
@endsection
