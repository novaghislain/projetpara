@extends('gel-accountant.comptabilite.etats-financiers.pdf.layout')

@section('title', 'État des Flux de Trésorerie')
@section('document_title', 'État des Flux de Trésorerie')

@section('content')

@php
    $treso_exp = $activite_encaissements - $activite_decaissements;
    $treso_inv = $investissement_cessions - $investissement_acquisitions;
    $treso_fin = $financement_capitaux + $financement_emprunts - $financement_remboursements - $financement_dividendes;
@endphp

<div class="grid-container">
    <table class="syscohada-table">
        <thead>
            <tr>
                <th class="text-left">FLUX DE TRÉSORERIE</th>
                <th width="150">MONTANT NET (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-title">
                <td colspan="2">A - Flux de trésorerie liés à l'activité opérationnelle</td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">Encaissements liés à l'activité</td>
                <td>{{ number_format($activite_encaissements, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">Décaissements liés à l'activité</td>
                <td>{{ number_format($activite_decaissements, 0, ',', ' ') }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td class="text-right">Trésorerie Nette d'Exploitation</td>
                <td>{{ number_format($treso_exp, 0, ',', ' ') }}</td>
            </tr>

            <tr class="section-title">
                <td colspan="2">B - Flux de trésorerie liés aux activités d'investissement</td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">Décaissements pour acquisition d'immobilisations</td>
                <td>{{ number_format($investissement_acquisitions, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">Encaissements liés aux cessions d'immobilisations</td>
                <td>{{ number_format($investissement_cessions, 0, ',', ' ') }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td class="text-right">Trésorerie Nette d'Investissement</td>
                <td>{{ number_format($treso_inv, 0, ',', ' ') }}</td>
            </tr>

            <tr class="section-title">
                <td colspan="2">C - Flux de trésorerie liés aux activités de financement</td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">Encaissements suite à l'émission d'emprunts</td>
                <td>{{ number_format($financement_emprunts, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 20px;">Remboursements d'emprunts</td>
                <td>{{ number_format($financement_remboursements, 0, ',', ' ') }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td class="text-right">Trésorerie Nette de Financement</td>
                <td>{{ number_format($treso_fin, 0, ',', ' ') }}</td>
            </tr>
            
            <tr class="total-row">
                <td class="text-right">VARIATION GLOBALE DE LA TRÉSORERIE (A + B + C)</td>
                <td>{{ number_format($treso_exp + $treso_inv + $treso_fin, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
