@extends('layouts.gel')

@section('title', 'Balance — GEL Cabinet')

@section('styles')
<style>
    .total-row { background:#f8f9fa; font-weight: 700; border-top: 2px solid var(--gel-border); }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Balance comptable</h1>
        <p class="page-subtitle">Balance générale des comptes.</p>
    </div>
    <div>
        <a href="{{ route('gel.comptabilite.ecritures.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="card-dashboard mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Classe</label>
                <select name="classe" class="form-select">
                    <option value="">Toutes les classes</option>
                    <option value="1" {{ request('classe') == '1' ? 'selected' : '' }}>Classe 1 — Capitaux</option>
                    <option value="2" {{ request('classe') == '2' ? 'selected' : '' }}>Classe 2 — Immobilisations</option>
                    <option value="3" {{ request('classe') == '3' ? 'selected' : '' }}>Classe 3 — Stocks</option>
                    <option value="4" {{ request('classe') == '4' ? 'selected' : '' }}>Classe 4 — Tiers</option>
                    <option value="5" {{ request('classe') == '5' ? 'selected' : '' }}>Classe 5 — Trésorerie</option>
                    <option value="6" {{ request('classe') == '6' ? 'selected' : '' }}>Classe 6 — Charges</option>
                    <option value="7" {{ request('classe') == '7' ? 'selected' : '' }}>Classe 7 — Produits</option>
                    <option value="8" {{ request('classe') == '8' ? 'selected' : '' }}>Classe 8 — Engagements</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Exercice</label>
                <select name="exercice_id" class="form-select">
                    <option value="">Tous</option>
                    @foreach($exercices as $ex)
                    <option value="{{ $ex->id }}" {{ request('exercice_id') == $ex->id ? 'selected' : '' }}>{{ $ex->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Du</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Au</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Consulter</button>
            </div>
        </form>
    </div>
</div>

<div class="card-dashboard">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0" style="font-size:0.85rem;">
                <thead>
                    <tr style="background:#f8f9fa;border-bottom:2px solid var(--gel-border);">
                        <th class="px-3 py-3">Compte</th>
                        <th class="py-3">Intitulé</th>
                        <th class="py-3 text-end">Solde initial D</th>
                        <th class="py-3 text-end">Solde initial C</th>
                        <th class="py-3 text-end">Mouvement D</th>
                        <th class="py-3 text-end">Mouvement C</th>
                        <th class="py-3 text-end">Solde final D</th>
                        <th class="py-3 text-end">Solde final C</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balances as $b)
                    <tr>
                        <td class="px-3 py-2" style="font-family:monospace;font-weight:600;">{{ $b['code'] }}</td>
                        <td class="py-2">{{ $b['intitule'] }}</td>
                        <td class="py-2 text-end">—</td>
                        <td class="py-2 text-end">—</td>
                        <td class="py-2 text-end" style="color:#0ca678;">{{ $b['total_debit'] > 0 ? number_format((float) $b['total_debit'], 0, ',', ' ') : '—' }}</td>
                        <td class="py-2 text-end" style="color:#e03131;">{{ $b['total_credit'] > 0 ? number_format((float) $b['total_credit'], 0, ',', ' ') : '—' }}</td>
                        <td class="py-2 text-end fw-bold" style="color:#0ca678;">{{ $b['solde_debiteur'] > 0 ? number_format((float) $b['solde_debiteur'], 0, ',', ' ') : '—' }}</td>
                        <td class="py-2 text-end fw-bold" style="color:#e03131;">{{ $b['solde_crediteur'] > 0 ? number_format((float) $b['solde_crediteur'], 0, ',', ' ') : '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-calculator" style="font-size:2rem;"></i>
                            <p class="mt-2">Aucune donnée de balance trouvée.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" class="px-3 py-3">Totaux</td>
                        <td class="py-3 text-end">—</td>
                        <td class="py-3 text-end">—</td>
                        <td class="py-3 text-end" style="color:#0ca678;">{{ number_format((float) $totalGeneralDebit, 0, ',', ' ') }}</td>
                        <td class="py-3 text-end" style="color:#e03131;">{{ number_format((float) $totalGeneralCredit, 0, ',', ' ') }}</td>
                        <td class="py-3 text-end fw-bold" style="color:#0ca678;">{{ number_format((float) $totalGeneralDebit, 0, ',', ' ') }}</td>
                        <td class="py-3 text-end fw-bold" style="color:#e03131;">{{ number_format((float) $totalGeneralCredit, 0, ',', ' ') }}</td>
                    </tr>
                    <tr style="font-size:0.9rem;">
                        <td colspan="8" class="px-3 py-2 text-center text-muted">
                            @if(abs($totalGeneralDebit - $totalGeneralCredit) < 0.01)
                                <span style="color:#0ca678;font-weight:600;">✓ Balance équilibrée</span>
                            @else
                                <span style="color:#e03131;font-weight:600;">
                                    ✗ Balance déséquilibrée
                                    (Diff: {{ number_format(abs($totalGeneralDebit - $totalGeneralCredit), 0, ',', ' ') }})
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
