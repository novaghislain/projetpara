@extends('gel-accountant.comptabilite.etats-financiers.pdf.layout')

@section('title', 'Tableau Financier des Ressources et Emplois (TAFIRE)')
@section('document_title', 'Tableau Financier des Ressources et Emplois (TAFIRE)')

@section('content')

<div>
    <!-- Colonne Emplois -->
    <div class="col-half">
        <table class="syscohada-table">
            <thead>
                <tr>
                    <th colspan="2">EMPLOIS</th>
                </tr>
                <tr>
                    <th class="text-left">RUBRIQUE</th>
                    <th>MONTANT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left">Acquisitions d'immobilisations</td>
                    <td>{{ number_format($emplois['acquisitions'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Remboursements de dettes financières</td>
                    <td>{{ number_format($emplois['remboursements'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Dividendes versés</td>
                    <td>{{ number_format($emplois['dividendes'], 0, ',', ' ') }}</td>
                </tr>
                <tr class="total-row">
                    <td class="text-left">TOTAL EMPLOIS</td>
                    <td>{{ number_format($total_emplois, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="col-spacer"></div>

    <!-- Colonne Ressources -->
    <div class="col-half">
        <table class="syscohada-table">
            <thead>
                <tr>
                    <th colspan="2">RESSOURCES</th>
                </tr>
                <tr>
                    <th class="text-left">RUBRIQUE</th>
                    <th>MONTANT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left">Capacité d'Autofinancement Globale (CAFG)</td>
                    <td>{{ number_format($ressources['cafg'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Cessions d'immobilisations</td>
                    <td>{{ number_format($ressources['cessions'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Augmentation de capital</td>
                    <td>{{ number_format($ressources['augmentation_capital'], 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="text-left">Nouveaux emprunts</td>
                    <td>{{ number_format($ressources['emprunts'], 0, ',', ' ') }}</td>
                </tr>
                <tr class="total-row">
                    <td class="text-left">TOTAL RESSOURCES</td>
                    <td>{{ number_format($total_ressources, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
