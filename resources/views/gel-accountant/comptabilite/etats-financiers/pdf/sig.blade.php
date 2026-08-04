@extends('gel-accountant.comptabilite.etats-financiers.pdf.layout')

@section('title', 'Soldes Intermédiaires de Gestion (SIG)')
@section('document_title', 'Soldes Intermédiaires de Gestion (SIG)')

@section('content')
<div class="grid-container">
    <table class="syscohada-table">
        <thead>
            <tr>
                <th class="text-left">SOLDE / INDICATEUR</th>
                <th width="150">MONTANT (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left">Marge Commerciale</td>
                <td>{{ number_format($marge_commerciale, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-left">Valeur Ajoutée (VA)</td>
                <td>{{ number_format($valeur_ajoutee, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-left">Excédent Brut d'Exploitation (EBE)</td>
                <td>{{ number_format($ebe, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-left">Résultat d'Exploitation (RE)</td>
                <td>{{ number_format($resultat_exploitation, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-left">Résultat Financier</td>
                <td>{{ number_format($resultat_financier, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-left">Résultat des Activités Ordinaires (RAO)</td>
                <td>{{ number_format($rao, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-left">Résultat Hors Activités Ordinaires (RHAO)</td>
                <td>{{ number_format($rhao, 0, ',', ' ') }}</td>
            </tr>
            <tr class="total-row">
                <td class="text-left">RÉSULTAT NET</td>
                <td>{{ number_format($resultat_net, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
