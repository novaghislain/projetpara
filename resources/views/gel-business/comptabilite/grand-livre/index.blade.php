@extends('layouts.gel-business')

@section('title', 'Grand Livre - Mon Entreprise')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Grand Livre</h1>
        <p class="gel-page-subtitle">Consultez les mouvements de votre comptabilité</p>
    </div>
</div>

<div class="gel-card" style="margin-bottom:16px;">
    <div class="gel-card-body">
        <form method="GET" action="{{ route('gel-business.comptabilite.grand-livre') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div class="gel-filter-group">
                <label>Compte</label>
                <select name="compte_id" class="gel-filter-select">
                    <option value="">Tous les comptes</option>
                    @if(isset($comptes) && count($comptes) > 0)
                        @foreach($comptes as $compte)
                            <option value="{{ $compte->id }}" {{ request('compte_id') == $compte->id ? 'selected' : '' }}>{{ $compte->code }} — {{ $compte->intitule }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="gel-filter-group">
                <label>Du</label>
                <input type="date" name="date_from" class="gel-filter-select" value="{{ request('date_from', now()->startOfYear()->format('Y-m-d')) }}">
            </div>
            <div class="gel-filter-group">
                <label>Au</label>
                <input type="date" name="date_to" class="gel-filter-select" value="{{ request('date_to', now()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="gel-btn gel-btn-primary gel-btn-sm">Afficher</button>
        </form>
    </div>
</div>

<div class="gel-card">
    <div class="gel-card-body" style="padding:0;">
        @if(isset($lignes) && $lignes->count() > 0)
            <table class="gel-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>N°</th>
                        <th>Compte</th>
                        <th>Libellé</th>
                        <th class="gel-text-right">Débit</th>
                        <th class="gel-text-right">Crédit</th>
                        <th class="gel-text-right">Solde</th>
                    </tr>
                </thead>
                <tbody>
                    @php $solde = 0; @endphp
                    @foreach($lignes as $ligne)
                        @php
                            $montant = $ligne->montant;
                            if ($ligne->sens === 'debit') { $solde += $montant; } else { $solde -= $montant; }
                        @endphp
                        <tr>
                            <td>{{ $ligne->ecriture->date_ecriture->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $ligne->ecriture->numero ?? '—' }}</td>
                            <td><strong>{{ $ligne->compte->code ?? '—' }}</strong></td>
                            <td>{{ $ligne->libelle_ligne ?? $ligne->ecriture->libelle ?? '—' }}</td>
                            <td class="gel-text-right">{{ $ligne->sens === 'debit' ? number_format($montant, 0, ',', ' ') : '—' }}</td>
                            <td class="gel-text-right">{{ $ligne->sens === 'credit' ? number_format($montant, 0, ',', ' ') : '—' }}</td>
                            <td class="gel-text-right"><strong>{{ number_format($solde, 0, ',', ' ') }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="gel-empty">
                <i class="bi bi-book"></i>
                <h3>Aucun mouvement</h3>
                <p>Votre comptable n'a pas encore saisi d'écritures.</p>
            </div>
        @endif
    </div>
</div>
@endsection
