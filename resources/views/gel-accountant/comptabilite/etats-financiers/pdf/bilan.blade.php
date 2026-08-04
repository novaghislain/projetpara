@extends('gel-accountant.comptabilite.etats-financiers.pdf.layout')

@section('title', 'Bilan')
@section('document_title', 'Bilan')

@section('content')

@php
    $actifImmo = $actif['incorporelles'] + $actif['corporelles'] + $actif['financieres'];
    $actifCirc = $actif['stocks'] + $actif['creances_clients'] + $actif['autres_creances'];
    $tresoActif = $actif['tresorerie'];
    
    $capitauxPropres = $passif['capital'] + $passif['reserves'] + $passif['report_nouveau'] + $passif['resultat_net'];
    $dettesFin = $passif['emprunts'];
    $passifCirc = $passif['fournisseurs'] + $passif['dettes_fiscales'];
    $tresoPassif = $passif['decouverts'];
@endphp

<div>
    <!-- Colonne Actif -->
    <div class="col-half">
        <table class="syscohada-table">
            <thead>
                <tr>
                    <th colspan="4">ACTIF</th>
                </tr>
                <tr>
                    <th class="text-left">RUBRIQUE</th>
                    <th>BRUT</th>
                    <th>AMORT/PROV</th>
                    <th>NET</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section-title">
                    <td colspan="4">Actif Immobilisé</td>
                </tr>
                <tr>
                    <td class="text-left">Immobilisations Incorporelles</td>
                    <td>{{ number_format($actif['incorporelles'], 0, ',', ' ') }}</td>
                    <td>0</td>
                    <td>{{ number_format($actif['incorporelles'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Immobilisations Corporelles</td>
                    <td>{{ number_format($actif['corporelles'], 0, ',', ' ') }}</td>
                    <td>0</td>
                    <td>{{ number_format($actif['corporelles'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Immobilisations Financières</td>
                    <td>{{ number_format($actif['financieres'], 0, ',', ' ') }}</td>
                    <td>0</td>
                    <td>{{ number_format($actif['financieres'], 0, ',', ' ') }}</td>
                </tr>
                
                <tr class="section-title">
                    <td colspan="4">Actif Circulant</td>
                </tr>
                <tr>
                    <td class="text-left">Stocks</td>
                    <td>{{ number_format($actif['stocks'], 0, ',', ' ') }}</td>
                    <td>0</td>
                    <td>{{ number_format($actif['stocks'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Créances Clients</td>
                    <td>{{ number_format($actif['creances_clients'], 0, ',', ' ') }}</td>
                    <td>0</td>
                    <td>{{ number_format($actif['creances_clients'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Autres Créances</td>
                    <td>{{ number_format($actif['autres_creances'], 0, ',', ' ') }}</td>
                    <td>0</td>
                    <td>{{ number_format($actif['autres_creances'], 0, ',', ' ') }}</td>
                </tr>

                <tr class="section-title">
                    <td colspan="4">Trésorerie Actif</td>
                </tr>
                <tr>
                    <td class="text-left">Banques, Caisse</td>
                    <td>{{ number_format($actif['tresorerie'], 0, ',', ' ') }}</td>
                    <td>0</td>
                    <td>{{ number_format($actif['tresorerie'], 0, ',', ' ') }}</td>
                </tr>

                <tr class="total-row">
                    <td class="text-left">TOTAL ACTIF</td>
                    <td>{{ number_format($total_actif, 0, ',', ' ') }}</td>
                    <td>0</td>
                    <td>{{ number_format($total_actif, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="col-spacer"></div>

    <!-- Colonne Passif -->
    <div class="col-half">
        <table class="syscohada-table">
            <thead>
                <tr>
                    <th colspan="2">PASSIF</th>
                </tr>
                <tr>
                    <th class="text-left">RUBRIQUE</th>
                    <th>NET</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section-title">
                    <td colspan="2">Capitaux Propres</td>
                </tr>
                <tr>
                    <td class="text-left">Capital</td>
                    <td>{{ number_format($passif['capital'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Réserves</td>
                    <td>{{ number_format($passif['reserves'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Report à nouveau</td>
                    <td>{{ number_format($passif['report_nouveau'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Résultat de l'exercice</td>
                    <td>{{ number_format($passif['resultat_net'], 0, ',', ' ') }}</td>
                </tr>
                
                <tr class="section-title">
                    <td colspan="2">Dettes Financières</td>
                </tr>
                <tr>
                    <td class="text-left">Emprunts et Dettes</td>
                    <td>{{ number_format($passif['emprunts'], 0, ',', ' ') }}</td>
                </tr>

                <tr class="section-title">
                    <td colspan="2">Passif Circulant</td>
                </tr>
                <tr>
                    <td class="text-left">Dettes Fournisseurs</td>
                    <td>{{ number_format($passif['fournisseurs'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Dettes Fiscales et Sociales</td>
                    <td>{{ number_format($passif['dettes_fiscales'], 0, ',', ' ') }}</td>
                </tr>

                <tr class="section-title">
                    <td colspan="2">Trésorerie Passif</td>
                </tr>
                <tr>
                    <td class="text-left">Découverts Bancaires</td>
                    <td>{{ number_format($passif['decouverts'], 0, ',', ' ') }}</td>
                </tr>

                <tr class="total-row">
                    <td class="text-left">TOTAL PASSIF</td>
                    <td>{{ number_format($total_passif, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
