{{-- ============================================ --}}
{{-- VUE : Bilan comptable détaillé              --}}
{{-- Appelée par : GelAccountant\EtatsFinanciersController@bilanDetaille --}}
{{-- Variables attendues : $client, $actif (tableau de rubriques avec code, intitule, brut, amortissement, net, sous-classe), --}}
{{--                      $passif (tableau de rubriques avec code, intitule, montant, sous-classe), --}}
{{--                      $totalActif, $totalPassif, $dateFin --}}
{{-- Route : gel-accountant.comptabilite.etats-financiers.bilan-detaille --}}
{{-- ============================================ --}}
@php $currentSection = 'comptabilite'; @endphp
@extends('layouts.gel-accountant')

@section('title', 'Bilan comptable détaillé - GEL Cabinet')

@section('content')
{{-- En-tête de la page --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Bilan comptable détaillé</h1>
        <p class="gel-page-subtitle">Arrêté au {{ \Carbon\Carbon::parse($dateFin)->locale('fr')->isoFormat('D MMMM YYYY') }}</p>
    </div>
    <div class="gel-flex gel-gap-sm">
        <a href="{{ route('gel-accountant.comptabilite.etats-financiers') }}" class="gel-btn gel-btn-secondary gel-btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <a href="{{ route('gel-accountant.comptabilite.etats-financiers.bilan') }}" class="gel-btn gel-btn-outline gel-btn-sm">
            <i class="bi bi-file-text"></i> Vue synthétique
        </a>
    </div>
</div>

{{-- Sous-section : Actif non courant --}}
<div class="gel-card gel-mb-lg p-4 mb-4">
    <div class="gel-card-header p-4 mb-4">
        <strong style="color:var(--gel-primary);">ACTIF NON COURANT</strong>
    </div>
    <div class="gel-card-body gel-p-0 p-4 mb-4">
        <table class="gel-table">
            <thead>
                <tr>
                    <th>Compte</th>
                    <th>Intitulé</th>
                    <th class="gel-text-right">Brut</th>
                    <th class="gel-text-right">Amort./Prov.</th>
                    <th class="gel-text-right">Net</th>
                </tr>
            </thead>
            <tbody>
                @php $ancFiltered = array_filter($actif ?? [], fn($l) => ($l['sous_classe'] ?? '') === 'non_courant'); @endphp
                @forelse($ancFiltered as $ligne)
                <tr>
                    <td>{{ $ligne['code'] ?? '—' }}</td>
                    <td>{{ $ligne['intitule'] ?? '—' }}</td>
                    <td class="gel-text-right">{{ number_format($ligne['brut'] ?? 0, 0, ',', ' ') }}</td>
                    <td class="gel-text-right">{{ number_format($ligne['amortissement'] ?? 0, 0, ',', ' ') }}</td>
                    <td class="gel-text-right"><strong>{{ number_format($ligne['net'] ?? 0, 0, ',', ' ') }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="gel-text-center gel-py-lg gel-text-muted">Aucune donnée pour l'actif non courant.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Sous-section : Actif courant --}}
<div class="gel-card gel-mb-lg p-4 mb-4">
    <div class="gel-card-header p-4 mb-4">
        <strong style="color:var(--gel-primary);">ACTIF COURANT</strong>
    </div>
    <div class="gel-card-body gel-p-0 p-4 mb-4">
        <table class="gel-table">
            <thead>
                <tr>
                    <th>Compte</th>
                    <th>Intitulé</th>
                    <th class="gel-text-right">Brut</th>
                    <th class="gel-text-right">Amort./Prov.</th>
                    <th class="gel-text-right">Net</th>
                </tr>
            </thead>
            <tbody>
                @php $acFiltered = array_filter($actif ?? [], fn($l) => ($l['sous_classe'] ?? '') === 'courant'); @endphp
                @forelse($acFiltered as $ligne)
                <tr>
                    <td>{{ $ligne['code'] ?? '—' }}</td>
                    <td>{{ $ligne['intitule'] ?? '—' }}</td>
                    <td class="gel-text-right">{{ number_format($ligne['brut'] ?? 0, 0, ',', ' ') }}</td>
                    <td class="gel-text-right">{{ number_format($ligne['amortissement'] ?? 0, 0, ',', ' ') }}</td>
                    <td class="gel-text-right"><strong>{{ number_format($ligne['net'] ?? 0, 0, ',', ' ') }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="gel-text-center gel-py-lg gel-text-muted">Aucune donnée pour l'actif courant.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Total Actif --}}
@if(isset($totalActif))
<div class="gel-card gel-mb-lg p-4 mb-4" style="border-left:4px solid var(--gel-primary);">
    <div class="gel-card-body gel-flex gel-justify-between p-4 mb-4">
        <strong style="font-size:16px;">TOTAL ACTIF</strong>
        <strong style="font-size:18px;color:var(--gel-primary);">{{ number_format($totalActif, 0, ',', ' ') }} FCFA</strong>
    </div>
</div>
@endif

{{-- Sous-section : Passif non courant --}}
<div class="gel-card gel-mb-lg p-4 mb-4">
    <div class="gel-card-header p-4 mb-4">
        <strong style="color:var(--gel-success);">PASSIF NON COURANT</strong>
    </div>
    <div class="gel-card-body gel-p-0 p-4 mb-4">
        <table class="gel-table">
            <thead>
                <tr>
                    <th>Compte</th>
                    <th>Intitulé</th>
                    <th class="gel-text-right">Montant</th>
                </tr>
            </thead>
            <tbody>
                @php $pncFiltered = array_filter($passif ?? [], fn($l) => ($l['sous_classe'] ?? '') === 'non_courant'); @endphp
                @forelse($pncFiltered as $ligne)
                <tr>
                    <td>{{ $ligne['code'] ?? '—' }}</td>
                    <td>{{ $ligne['intitule'] ?? '—' }}</td>
                    <td class="gel-text-right"><strong>{{ number_format($ligne['montant'] ?? 0, 0, ',', ' ') }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="gel-text-center gel-py-lg gel-text-muted">Aucune donnée pour le passif non courant.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Sous-section : Passif courant --}}
<div class="gel-card gel-mb-lg p-4 mb-4">
    <div class="gel-card-header p-4 mb-4">
        <strong style="color:var(--gel-success);">PASSIF COURANT</strong>
    </div>
    <div class="gel-card-body gel-p-0 p-4 mb-4">
        <table class="gel-table">
            <thead>
                <tr>
                    <th>Compte</th>
                    <th>Intitulé</th>
                    <th class="gel-text-right">Montant</th>
                </tr>
            </thead>
            <tbody>
                @php $pcFiltered = array_filter($passif ?? [], fn($l) => ($l['sous_classe'] ?? '') === 'courant'); @endphp
                @forelse($pcFiltered as $ligne)
                <tr>
                    <td>{{ $ligne['code'] ?? '—' }}</td>
                    <td>{{ $ligne['intitule'] ?? '—' }}</td>
                    <td class="gel-text-right"><strong>{{ number_format($ligne['montant'] ?? 0, 0, ',', ' ') }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="gel-text-center gel-py-lg gel-text-muted">Aucune donnée pour le passif courant.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Total Passif --}}
@if(isset($totalPassif))
<div class="gel-card p-4 mb-4" style="border-left:4px solid var(--gel-success);">
    <div class="gel-card-body gel-flex gel-justify-between p-4 mb-4">
        <strong style="font-size:16px;">TOTAL PASSIF</strong>
        <strong style="font-size:18px;color:var(--gel-success);">{{ number_format($totalPassif, 0, ',', ' ') }} FCFA</strong>
    </div>
</div>
@endif
@endsection

