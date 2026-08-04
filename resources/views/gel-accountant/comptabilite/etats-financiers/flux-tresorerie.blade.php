{{-- ============================================ --}}
{{-- VUE : Tableau des flux de trésorerie          --}}
{{-- Appelée par : GelAccountant\EtatsFinanciersController@fluxTresorerie --}}
{{-- Variables attendues : $client, $flux (tableau structuré en trois sections : exploitation, investissement, financement), --}}
{{--                      $variationTresorerie, $tresorerieOuverture, $tresorerieCloture, $dateFin --}}
{{-- Route : gel-accountant.comptabilite.etats-financiers.flux-tresorerie --}}
{{-- ============================================ --}}
@php $currentSection = 'comptabilite'; @endphp
@extends('layouts.gel-accountant')

@section('title', 'Tableau des flux de trésorerie - GEL Cabinet')

@section('content')
{{-- En-tête de la page --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Tableau des flux de trésorerie</h1>
        <p class="gel-page-subtitle">Exercice arrêté au {{ \Carbon\Carbon::parse($dateFin)->locale('fr')->isoFormat('D MMMM YYYY') }}</p>
    </div>
    <div class="gel-flex gel-gap-sm">
        <a href="{{ route('gel-accountant.comptabilite.etats-financiers') }}" class="gel-btn gel-btn-secondary gel-btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
</div>

{{-- Section : Flux de trésorerie liés À  l'exploitation --}}
<div class="gel-card gel-mb-lg p-4 mb-4">
    <div class="gel-card-header p-4 mb-4">
        <strong style="color:var(--gel-primary);">Flux de trésorerie liés À  l'exploitation</strong>
    </div>
    <div class="gel-card-body gel-p-0 p-4 mb-4">
        <table class="gel-table">
            <thead>
                <tr>
                    <th>Rubrique</th>
                    <th class="gel-text-right">Montant</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($flux['exploitation']) && count($flux['exploitation']) > 0)
                    @foreach($flux['exploitation'] as $rubrique)
                    <tr>
                        <td>{{ $rubrique['intitule'] ?? '—' }}</td>
                        <td class="gel-text-right">{{ number_format($rubrique['montant'] ?? 0, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2" class="gel-text-center gel-py-lg gel-text-muted">Aucune donnée d'exploitation disponible.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- Section : Flux de trésorerie liés aux investissements --}}
<div class="gel-card gel-mb-lg p-4 mb-4">
    <div class="gel-card-header p-4 mb-4">
        <strong style="color:var(--gel-warning);">Flux de trésorerie liés aux investissements</strong>
    </div>
    <div class="gel-card-body gel-p-0 p-4 mb-4">
        <table class="gel-table">
            <thead>
                <tr>
                    <th>Rubrique</th>
                    <th class="gel-text-right">Montant</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($flux['investissement']) && count($flux['investissement']) > 0)
                    @foreach($flux['investissement'] as $rubrique)
                    <tr>
                        <td>{{ $rubrique['intitule'] ?? '—' }}</td>
                        <td class="gel-text-right">{{ number_format($rubrique['montant'] ?? 0, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2" class="gel-text-center gel-py-lg gel-text-muted">Aucune donnée d'investissement disponible.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- Section : Flux de trésorerie liés au financement --}}
<div class="gel-card gel-mb-lg p-4 mb-4">
    <div class="gel-card-header p-4 mb-4">
        <strong style="color:var(--gel-success);">Flux de trésorerie liés au financement</strong>
    </div>
    <div class="gel-card-body gel-p-0 p-4 mb-4">
        <table class="gel-table">
            <thead>
                <tr>
                    <th>Rubrique</th>
                    <th class="gel-text-right">Montant</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($flux['financement']) && count($flux['financement']) > 0)
                    @foreach($flux['financement'] as $rubrique)
                    <tr>
                        <td>{{ $rubrique['intitule'] ?? '—' }}</td>
                        <td class="gel-text-right">{{ number_format($rubrique['montant'] ?? 0, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2" class="gel-text-center gel-py-lg gel-text-muted">Aucune donnée de financement disponible.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- Synthèse de la trésorerie --}}
@if(isset($variationTresorerie) || isset($tresorerieOuverture) || isset($tresorerieCloture))
<div class="gel-card p-4 mb-4" style="border-left:4px solid var(--gel-primary);">
    <div class="gel-card-body p-4 mb-4">
        <div class="gel-flex gel-gap-lg gel-justify-center">
            @if(isset($tresorerieOuverture))
            <div class="gel-text-center">
                <div class="gel-text-sm" style="color:var(--gel-text-muted);">Trésorerie d'ouverture</div>
                <div class="gel-font-bold" style="font-size:16px;">{{ number_format($tresorerieOuverture, 0, ',', ' ') }} FCFA</div>
            </div>
            @endif
            @if(isset($variationTresorerie))
            <div class="gel-text-center">
                <div class="gel-text-sm" style="color:var(--gel-text-muted);">Variation de trésorerie</div>
                <div class="gel-font-bold" style="font-size:16px;{{ $variationTresorerie >= 0 ? 'color:var(--gel-success);' : 'color:var(--gel-danger);' }}">
                    {{ ($variationTresorerie >= 0 ? '+' : '') . number_format($variationTresorerie, 0, ',', ' ') }} FCFA
                </div>
            </div>
            @endif
            @if(isset($tresorerieCloture))
            <div class="gel-text-center">
                <div class="gel-text-sm" style="color:var(--gel-text-muted);">Trésorerie de clôture</div>
                <div class="gel-font-bold" style="font-size:18px;color:var(--gel-primary);">{{ number_format($tresorerieCloture, 0, ',', ' ') }} FCFA</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
@endsection

