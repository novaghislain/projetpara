@extends('layouts.gel')

@section('title', 'États financiers — GEL Cabinet')

@section('styles')
<style>
    .section-title { font-size:1rem; font-weight:700; color:var(--gel-primary); border-bottom:2px solid var(--gel-primary); padding-bottom:0.5rem; margin-bottom:1rem; }
    .total-row { font-weight:700; border-top:2px solid var(--gel-border); }
    .resultat-positif { color:#0ca678; font-weight:700; }
    .resultat-negatif { color:#e03131; font-weight:700; }
    .montant-charge { color:#e03131; }
    .montant-produit { color:#0ca678; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">États financiers</h1>
        <p class="page-subtitle">Bilan et compte de résultat.</p>
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
            <div class="col-md-4">
                <label class="form-label fw-semibold">Exercice</label>
                <select name="exercice_id" class="form-select">
                    <option value="">Sélectionnez...</option>
                    @foreach($exercices as $ex)
                    <option value="{{ $ex->id }}" {{ request('exercice_id') == $ex->id ? 'selected' : '' }}>{{ $ex->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Type</label>
                <select name="type" class="form-select">
                    <option value="bilan" {{ request('type', 'bilan') == 'bilan' ? 'selected' : '' }}>Bilan</option>
                    <option value="resultat" {{ request('type') == 'resultat' ? 'selected' : '' }}>Compte de résultat</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Date arrêtée</label>
                <input type="date" name="date_arret" class="form-control" value="{{ request('date_arret', now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-eye"></i> Voir</button>
            </div>
        </form>
    </div>
</div>

@if(request('type', 'bilan') == 'bilan')
    {{-- BILAN --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card-dashboard">
                <div class="card-header">
                    <h5 class="mb-0">Actif</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th class="px-3 py-2">Compte</th>
                                <th class="py-2">Libellé</th>
                                <th class="py-2 text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalActif = 0; @endphp
                            @forelse($actif as $a)
                            @php $totalActif += (float) $a->solde; @endphp
                            <tr>
                                <td class="px-3 py-1" style="font-family:monospace;">{{ $a->code }}</td>
                                <td class="py-1">{{ $a->intitule }}</td>
                                <td class="py-1 text-end montant-produit">{{ number_format((float) $a->solde, 0, ',', ' ') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">Aucun compte d'actif.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2" class="px-3 py-2 text-end">Total Actif</td>
                                <td class="py-2 text-end fw-bold montant-produit fs-5">{{ number_format($totalActif, 0, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-dashboard">
                <div class="card-header">
                    <h5 class="mb-0">Passif</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th class="px-3 py-2">Compte</th>
                                <th class="py-2">Libellé</th>
                                <th class="py-2 text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalPassif = 0; @endphp
                            @forelse($passif as $p)
                            @php $totalPassif += (float) $p->solde; @endphp
                            <tr>
                                <td class="px-3 py-1" style="font-family:monospace;">{{ $p->code }}</td>
                                <td class="py-1">{{ $p->intitule }}</td>
                                <td class="py-1 text-end montant-charge">{{ number_format((float) $p->solde, 0, ',', ' ') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">Aucun compte de passif.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2" class="px-3 py-2 text-end">Total Passif</td>
                                <td class="py-2 text-end fw-bold montant-charge fs-5">{{ number_format($totalPassif, 0, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card-dashboard mt-3 text-center p-3">
                @if(abs($totalActif - $totalPassif) < 0.01)
                    <span style="color:#0ca678;font-weight:700;font-size:1.1rem;">
                        ✓ Bilan équilibré : {{ number_format($totalActif, 0, ',', ' ') }} FCFA
                    </span>
                @else
                    <span style="color:#e03131;font-weight:700;font-size:1.1rem;">
                        ✗ Bilan déséquilibré (écart : {{ number_format(abs($totalActif - $totalPassif), 0, ',', ' ') }} FCFA)
                    </span>
                @endif
            </div>
        </div>
    </div>
@else
    {{-- COMPTE DE RÉSULTAT --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card-dashboard">
                <div class="card-header">
                    <h5 class="mb-0">Charges (Classe 6)</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th class="px-3 py-2">Compte</th>
                                <th class="py-2">Libellé</th>
                                <th class="py-2 text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalCharges = 0; @endphp
                            @forelse($charges as $c)
                            @php $totalCharges += (float) $c->solde; @endphp
                            <tr>
                                <td class="px-3 py-1" style="font-family:monospace;">{{ $c->code }}</td>
                                <td class="py-1">{{ $c->intitule }}</td>
                                <td class="py-1 text-end montant-charge">{{ number_format((float) $c->solde, 0, ',', ' ') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">Aucune charge.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2" class="px-3 py-2 text-end">Total charges</td>
                                <td class="py-2 text-end fw-bold montant-charge fs-5">{{ number_format($totalCharges, 0, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-dashboard">
                <div class="card-header">
                    <h5 class="mb-0">Produits (Classe 7)</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th class="px-3 py-2">Compte</th>
                                <th class="py-2">Libellé</th>
                                <th class="py-2 text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalProduits = 0; @endphp
                            @forelse($produits as $p)
                            @php $totalProduits += (float) $p->solde; @endphp
                            <tr>
                                <td class="px-3 py-1" style="font-family:monospace;">{{ $p->code }}</td>
                                <td class="py-1">{{ $p->intitule }}</td>
                                <td class="py-1 text-end montant-produit">{{ number_format((float) $p->solde, 0, ',', ' ') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">Aucun produit.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2" class="px-3 py-2 text-end">Total produits</td>
                                <td class="py-2 text-end fw-bold montant-produit fs-5">{{ number_format($totalProduits, 0, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card-dashboard text-center p-4">
                @php $resultat = $totalProduits - $totalCharges; @endphp
                <div class="mb-2 text-muted small">Résultat de l'exercice</div>
                <div class="fs-3 fw-bold {{ $resultat >= 0 ? 'resultat-positif' : 'resultat-negatif' }}">
                    {{ $resultat >= 0 ? 'Bénéfice' : 'Perte' }} :
                    {{ number_format(abs($resultat), 0, ',', ' ') }} FCFA
                </div>
                <div class="mt-2 text-muted small">
                    Produits : {{ number_format($totalProduits, 0, ',', ' ') }} FCFA
                    &nbsp;|&nbsp;
                    Charges : {{ number_format($totalCharges, 0, ',', ' ') }} FCFA
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
