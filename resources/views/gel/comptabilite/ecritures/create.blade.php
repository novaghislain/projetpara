@extends('layouts.gel')

@section('title', 'Nouvelle écriture — Comptabilité')

@section('styles')
<style>
    .ligne-entry { background:#f8f9fa; border-radius:8px; padding:0.75rem; margin-bottom:0.5rem; border:1px solid var(--gel-border); }
    .ligne-entry:hover { border-color:var(--gel-accent-2); }
    .total-box { font-size:1.2rem; font-weight:800; padding:1rem; border-radius:8px; }
    .total-box.equilibre { background:#e6fcf5; color:#0ca678; }
    .total-box.desequilibre { background:#ffe0e0; color:#e03131; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Nouvelle écriture comptable</h1>
        <p class="page-subtitle">Saisissez une écriture.</p>
    </div>
    <div>
        <a href="{{ route('gel.comptabilite.ecritures.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="card-dashboard">
    <div class="card-body p-4">
        <form action="{{ route('gel.comptabilite.ecritures.store') }}" method="POST" id="ecritureForm">
            @csrf

            {{-- Entête --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Journal <span class="text-danger">*</span></label>
                    <select name="journal_id" class="form-select @error('journal_id') is-invalid @enderror" required>
                        <option value="">Sélectionnez...</option>
                        @foreach($journaux as $j)
                        <option value="{{ $j->id }}" {{ old('journal_id') == $j->id || request('journal_id') == $j->id ? 'selected' : '' }}>
                            {{ $j->code }} — {{ $j->libelle }}
                        </option>
                        @endforeach
                    </select>
                    @error('journal_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Exercice <span class="text-danger">*</span></label>
                    <select name="exercice_id" class="form-select @error('exercice_id') is-invalid @enderror" required>
                        <option value="">Sélectionnez...</option>
                        @foreach($exercices as $ex)
                        <option value="{{ $ex->id }}" {{ old('exercice_id') == $ex->id ? 'selected' : '' }}>{{ $ex->libelle }}</option>
                        @endforeach
                    </select>
                    @error('exercice_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date écriture</label>
                    <input type="date" name="date_ecriture" class="form-control @error('date_ecriture') is-invalid @enderror"
                           value="{{ old('date_ecriture', now()->format('Y-m-d')) }}" required>
                    @error('date_ecriture') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date pièce</label>
                    <input type="date" name="date_piece" class="form-control @error('date_piece') is-invalid @enderror"
                           value="{{ old('date_piece', now()->format('Y-m-d')) }}" required>
                    @error('date_piece') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Référence pièce</label>
                    <input type="text" name="reference_piece" class="form-control" value="{{ old('reference_piece') }}" placeholder="Facture N°...">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">— Aucun —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror"
                           value="{{ old('libelle') }}" placeholder="Libellé de l'écriture" required>
                    @error('libelle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Lignes --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-bold mb-0" style="color:var(--gel-primary);">Lignes d'écriture</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="ajouterLigne()">
                    <i class="bi bi-plus-lg"></i> Ajouter une ligne
                </button>
            </div>

            <div id="lignesContainer">
                <div class="ligne-entry" id="ligne-0">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <select name="lignes[0][compte_id]" class="form-select form-select-sm compte-select" required>
                                <option value="">Compte...</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="lignes[0][sens]" class="form-select form-select-sm" required>
                                <option value="debit">Débit</option>
                                <option value="credit">Crédit</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="lignes[0][montant]" class="form-control form-control-sm montant-input"
                                   placeholder="Montant" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="lignes[0][libelle_ligne]" class="form-control form-control-sm" placeholder="Libellé ligne (optionnel)">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(0)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="ligne-entry" id="ligne-1">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <select name="lignes[1][compte_id]" class="form-select form-select-sm compte-select" required>
                                <option value="">Compte...</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="lignes[1][sens]" class="form-select form-select-sm" required>
                                <option value="debit">Débit</option>
                                <option value="credit" selected>Crédit</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="lignes[1][montant]" class="form-control form-control-sm montant-input"
                                   placeholder="Montant" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="lignes[1][libelle_ligne]" class="form-control form-control-sm" placeholder="Libellé ligne (optionnel)">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(1)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Totaux --}}
            <div class="row mt-3">
                <div class="col-md-6 offset-md-6">
                    <div id="totalBox" class="total-box equilibre text-center">
                        Total Débit: <span id="totalDebit">0</span> FCFA
                        &nbsp;|&nbsp; Total Crédit: <span id="totalCredit">0</span> FCFA
                        &nbsp;|&nbsp; <span id="statutEquilibre" style="font-weight:700;">✓ Équilibrée</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg"></i>
                    @can('comptabilite.valider') Créer et valider @else Créer (brouillon) @endcan
                </button>
                <a href="{{ route('gel.comptabilite.ecritures.index') }}" class="btn btn-outline-secondary btn-lg">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let ligneIndex = 2;

function ajouterLigne() {
    const container = document.getElementById('lignesContainer');
    const div = document.createElement('div');
    div.className = 'ligne-entry';
    div.id = `ligne-${ligneIndex}`;
    div.innerHTML = `
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="lignes[${ligneIndex}][compte_id]" class="form-select form-select-sm compte-select" required>
                    <option value="">Compte...</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="lignes[${ligneIndex}][sens]" class="form-select form-select-sm">
                    <option value="debit">Débit</option>
                    <option value="credit">Crédit</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="lignes[${ligneIndex}][montant]" class="form-control form-control-sm montant-input"
                       placeholder="Montant" step="0.01" min="0.01" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="lignes[${ligneIndex}][libelle_ligne]" class="form-control form-control-sm" placeholder="Libellé ligne">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(${ligneIndex})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(div);

    // Ajouter l'écouteur sur le montant
    div.querySelector('.montant-input').addEventListener('input', calculerTotaux);
    ligneIndex++;
}

function supprimerLigne(index) {
    const el = document.getElementById(`ligne-${index}`);
    if (el) {
        el.remove();
        calculerTotaux();
    }
}

function calculerTotaux() {
    let totalDebit = 0;
    let totalCredit = 0;

    document.querySelectorAll('.ligne-entry').forEach(entry => {
        const sens = entry.querySelector('[name$="[sens]"]')?.value;
        const montant = parseFloat(entry.querySelector('.montant-input')?.value) || 0;
        if (sens === 'debit') totalDebit += montant;
        else totalCredit += montant;
    });

    document.getElementById('totalDebit').textContent = totalDebit.toLocaleString('fr-FR', {minimumFractionDigits:0});
    document.getElementById('totalCredit').textContent = totalCredit.toLocaleString('fr-FR', {minimumFractionDigits:0});

    const box = document.getElementById('totalBox');
    const statut = document.getElementById('statutEquilibre');
    const diff = Math.abs(totalDebit - totalCredit);
    if (diff < 0.01) {
        box.className = 'total-box equilibre text-center';
        statut.textContent = '✓ Équilibrée';
        statut.style.color = '#0ca678';
    } else {
        box.className = 'total-box desequilibre text-center';
        statut.textContent = `✗ Déséquilibrée (${diff.toLocaleString('fr-FR')})`;
        statut.style.color = '#e03131';
    }
}

// Initialiser les calculs
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.montant-input').forEach(el => {
        el.addEventListener('input', calculerTotaux);
    });
    calculerTotaux();
});
</script>
@endsection
