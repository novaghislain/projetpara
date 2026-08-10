@extends('layouts.gel')

@section('title', 'États Financiers — GEL Cabinet')

@section('styles')
<style>
    /* QuickBooks Online Style Overrides */
    .qbo-toolbar {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    
    .qbo-grid-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }
    
    .filter-select, .filter-input {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.4rem 0.75rem;
        font-size: 0.85rem;
        color: #374151;
        outline: none;
        background-color: white;
    }
    .filter-select:focus, .filter-input:focus { border-color: #2ca01c; }
    
    .action-btn {
        background: none;
        border: 1px solid #d1d5db;
        color: #374151;
        cursor: pointer;
        padding: 0.4rem 1rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: background 0.15s;
    }
    .action-btn:hover { background: #f3f4f6; }

    .report-header {
        text-align: center;
        padding: 2.5rem 1rem 1.5rem 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .report-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .report-subtitle {
        font-size: 1rem;
        color: #4b5563;
    }

    /* Financial Tables */
    .fin-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .fin-table th {
        text-align: left;
        padding: 0.75rem 1rem;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 2px solid #e5e7eb;
        background: #f9fafb;
    }
    .fin-table td {
        padding: 0.5rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
    }
    .fin-table tbody tr:hover { background-color: #f9fafb; }
    
    .section-header td {
        background: #f9fafb;
        font-weight: 700;
        color: #4b5563;
        padding-top: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #d1d5db;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    
    .total-row td {
        font-weight: 700;
        border-top: 2px solid #d1d5db;
        color: #111827;
        background: #f9fafb;
        padding: 1rem;
    }

    .val-amount { font-variant-numeric: tabular-nums; text-align: right; }
    
    .fin-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .fin-card-header {
        background: #f9fafb;
        padding: 1rem;
        font-weight: 700;
        font-size: 1.1rem;
        border-bottom: 1px solid #e5e7eb;
        text-align: center;
        color: #111827;
    }
    .fin-card-body {
        flex-grow: 1;
        padding: 0;
    }

    .result-box {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        margin-top: 2rem;
    }
    .result-positive { color: #03543f; background: #def7ec; border-color: #31c48d; }
    .result-negative { color: #9b1c1c; background: #fde8e8; border-color: #f8b4b4; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1" style="font-weight: 700; color: #111827;">États Financiers</h1>
        <p class="text-muted" style="font-size: 0.9rem;">Bilan comptable et Compte de Résultat (Format SYSCOHADA).</p>
    </div>
</div>

<div class="qbo-grid-container">
    <form method="GET" id="filter-form">
        <div class="qbo-toolbar flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <select name="type" class="filter-select">
                    <option value="bilan" {{ request('type', 'bilan') == 'bilan' ? 'selected' : '' }}>Bilan Comptable</option>
                    <option value="resultat" {{ request('type') == 'resultat' ? 'selected' : '' }}>Compte de Résultat</option>
                </select>

                <select name="exercice_id" class="filter-select">
                    @foreach($exercices as $ex)
                    <option value="{{ $ex->id }}" {{ request('exercice_id') == $ex->id ? 'selected' : '' }}>{{ $ex->libelle }}</option>
                    @endforeach
                </select>
                
                <button type="submit" class="action-btn" style="background:#2ca01c; color:white; border-color:#2ca01c;">
                    Afficher
                </button>
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" class="action-btn" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Imprimer
                </button>
            </div>
        </div>
    </form>

    <div class="report-header">
        <div class="report-title">
            {{ request('type', 'bilan') == 'bilan' ? 'BILAN COMPTABLE' : 'COMPTE DE RÉSULTAT' }}
        </div>
        @php
            $currentEx = $exercices->firstWhere('id', request('exercice_id'));
            if(!$currentEx) $currentEx = $exercices->first();
        @endphp
        <div class="report-subtitle mt-2">
            Exercice : {{ $currentEx ? $currentEx->libelle : 'N/A' }} 
            (Du {{ $currentEx ? $currentEx->date_debut->format('d/m/Y') : '' }} au {{ $currentEx ? $currentEx->date_fin->format('d/m/Y') : '' }})
        </div>
    </div>

    <div class="p-4">
        @if(request('type', 'bilan') == 'bilan')
            {{-- BILAN --}}
            <div class="row g-4">
                {{-- ACTIF --}}
                <div class="col-lg-6">
                    <div class="fin-card">
                        <div class="fin-card-header">ACTIF</div>
                        <div class="fin-card-body">
                            <table class="fin-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">COMPTE</th>
                                        <th>LIBELLÉ</th>
                                        <th class="text-end">MONTANT NET</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bilan['actif']['sections'] as $section)
                                        @if(count($section['lignes']) > 0)
                                            <tr class="section-header">
                                                <td colspan="3">{{ $section['titre'] }}</td>
                                            </tr>
                                            @foreach($section['lignes'] as $ligne)
                                            <tr>
                                                <td style="font-family: monospace;">{{ $ligne['code'] }}</td>
                                                <td>{{ $ligne['intitule'] }}</td>
                                                <td class="val-amount">{{ number_format($ligne['montant'], 0, ',', ' ') }}</td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="2" class="text-end">TOTAL ACTIF</td>
                                        <td class="val-amount fs-6">{{ number_format($bilan['actif']['total'], 0, ',', ' ') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- PASSIF --}}
                <div class="col-lg-6">
                    <div class="fin-card">
                        <div class="fin-card-header">PASSIF</div>
                        <div class="fin-card-body">
                            <table class="fin-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">COMPTE</th>
                                        <th>LIBELLÉ</th>
                                        <th class="text-end">MONTANT NET</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bilan['passif']['sections'] as $section)
                                        @if(count($section['lignes']) > 0)
                                            <tr class="section-header">
                                                <td colspan="3">{{ $section['titre'] }}</td>
                                            </tr>
                                            @foreach($section['lignes'] as $ligne)
                                            <tr>
                                                <td style="font-family: monospace;">{{ $ligne['code'] }}</td>
                                                <td>{{ $ligne['intitule'] }}</td>
                                                <td class="val-amount">{{ number_format($ligne['montant'], 0, ',', ' ') }}</td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                    
                                    {{-- Ligne de Résultat dans le Passif pour équilibrage théorique --}}
                                    <tr class="section-header">
                                        <td colspan="3">RÉSULTAT DE L'EXERCICE</td>
                                    </tr>
                                    <tr>
                                        <td style="font-family: monospace;">13</td>
                                        <td>Résultat net (Bénéfice ou Perte)</td>
                                        <td class="val-amount fw-bold" style="color: {{ $resultatNet >= 0 ? '#03543f' : '#9b1c1c' }}">
                                            {{ number_format($resultatNet, 0, ',', ' ') }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="2" class="text-end">TOTAL PASSIF (y compris Résultat)</td>
                                        <td class="val-amount fs-6">{{ number_format($bilan['passif']['total'] + $resultatNet, 0, ',', ' ') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contrôle d'équilibre --}}
            @php
                $totalPassifEq = $bilan['passif']['total'] + $resultatNet;
                $ecart = abs($bilan['actif']['total'] - $totalPassifEq);
            @endphp
            <div class="result-box {{ $ecart < 0.01 ? 'result-positive' : 'result-negative' }}">
                @if($ecart < 0.01)
                    <h3 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i> Le Bilan est parfaitement équilibré</h3>
                @else
                    <h3 class="mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> Bilan déséquilibré !</h3>
                    <p class="mb-0">Écart détecté : <strong>{{ number_format($ecart, 0, ',', ' ') }} FCFA</strong></p>
                    <p class="small mt-1 mb-0 text-muted">Vérifiez que toutes les écritures de la période sont validées et équilibrées.</p>
                @endif
            </div>

        @else
            {{-- COMPTE DE RÉSULTAT --}}
            <div class="row g-4">
                {{-- CHARGES --}}
                <div class="col-lg-6">
                    <div class="fin-card">
                        <div class="fin-card-header">CHARGES (Classe 6)</div>
                        <div class="fin-card-body">
                            <table class="fin-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">COMPTE</th>
                                        <th>LIBELLÉ</th>
                                        <th class="text-end">MONTANT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resultat['charges']['lignes'] as $ligne)
                                    <tr>
                                        <td style="font-family: monospace;">{{ $ligne['code'] }}</td>
                                        <td>{{ $ligne['intitule'] }}</td>
                                        <td class="val-amount">{{ number_format($ligne['montant'], 0, ',', ' ') }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted">Aucune charge enregistrée.</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="2" class="text-end">TOTAL DES CHARGES</td>
                                        <td class="val-amount fs-6">{{ number_format($resultat['charges']['total'], 0, ',', ' ') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- PRODUITS --}}
                <div class="col-lg-6">
                    <div class="fin-card">
                        <div class="fin-card-header">PRODUITS (Classe 7)</div>
                        <div class="fin-card-body">
                            <table class="fin-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">COMPTE</th>
                                        <th>LIBELLÉ</th>
                                        <th class="text-end">MONTANT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resultat['produits']['lignes'] as $ligne)
                                    <tr>
                                        <td style="font-family: monospace;">{{ $ligne['code'] }}</td>
                                        <td>{{ $ligne['intitule'] }}</td>
                                        <td class="val-amount">{{ number_format($ligne['montant'], 0, ',', ' ') }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted">Aucun produit enregistré.</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="2" class="text-end">TOTAL DES PRODUITS</td>
                                        <td class="val-amount fs-6">{{ number_format($resultat['produits']['total'], 0, ',', ' ') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Résultat Net --}}
            <div class="result-box {{ $resultatNet >= 0 ? 'result-positive' : 'result-negative' }}">
                <h4 class="mb-1 text-muted text-uppercase" style="letter-spacing: 0.1em; font-size:0.9rem;">RÉSULTAT NET DE L'EXERCICE</h4>
                <h2 class="mb-0 fw-bold" style="font-size: 2.5rem;">
                    {{ $resultatNet >= 0 ? 'BÉNÉFICE' : 'PERTE' }} : {{ number_format(abs($resultatNet), 0, ',', ' ') }} FCFA
                </h2>
            </div>
        @endif
    </div>
</div>
@endsection
