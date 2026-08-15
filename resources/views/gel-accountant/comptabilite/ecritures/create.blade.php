@extends('layouts.gel-accountant')

@section('title', 'Saisie d\'une Nouvelle Écriture')

@push('styles')
<style>
/* ==========================================================================
   CREATE ECRITURE - BENTO GRID & DOCUMENT DESIGN
   ========================================================================== */
.form-section {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; padding: 24px; margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.section-title {
    font-size: 15px; font-weight: 700; color: #1E293B; margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;
}
.section-title i { color: var(--gel-primary); }

.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }

.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
.form-control, .form-select {
    width: 100%; padding: 10px 14px; border: 1px solid #E2E8F0;
    border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s;
    background: #F8FAFC; font-family: inherit;
}
.form-control:focus, .form-select:focus {
    background: white; border-color: var(--gel-primary); box-shadow: 0 0 0 3px rgba(13,148,136,0.1);
}

/* Lignes Table */
.lines-table { width: 100%; border-collapse: collapse; }
.lines-table th {
    background: #F1F5F9; font-size: 11px; font-weight: 700; color: #64748B;
    text-transform: uppercase; padding: 12px; text-align: left; border-bottom: 2px solid #E2E8F0;
}
.lines-table td { padding: 12px; border-bottom: 1px solid #E2E8F0; vertical-align: top; }
.line-input {
    width: 100%; padding: 8px 12px; border: 1px solid #E2E8F0; border-radius: 6px;
    font-size: 13px; font-family: inherit; outline: none; background: white;
}
.line-input:focus { border-color: var(--gel-primary); }
.line-amount { font-family: monospace; text-align: right; }
.btn-remove { background: #FEF2F2; color: #EF4444; border: none; padding: 8px 10px; border-radius: 6px; cursor: pointer; }
.btn-remove:hover { background: #FEE2E2; }
.btn-add-line { background: #EFF6FF; color: #3B82F6; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; margin-top: 16px; }
.btn-add-line:hover { background: #DBEAFE; }

/* Totals */
.totals-area {
    display: flex; justify-content: flex-end; align-items: center; gap: 40px;
    margin-top: 24px; padding: 20px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0;
}
.total-box { display: flex; flex-direction: column; align-items: flex-end; }
.total-label { font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase; }
.total-value { font-size: 20px; font-weight: 700; color: #1E293B; font-family: monospace; }
.total-value.unbalanced { color: #EF4444; }
.total-value.balanced { color: #10B981; }

.balance-indicator { font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 20px; display: inline-flex; align-items: center; gap: 8px; }
.balance-indicator.ok { background: #ECFDF5; color: #10B981; border: 1px solid #A7F3D0; }
.balance-indicator.error { background: #FEF2F2; color: #EF4444; border: 1px solid #FECACA; }

/* Actions */
.form-actions { display: flex; justify-content: flex-end; gap: 16px; margin-top: 32px; }
.btn-cancel { background: white; color: #475569; border: 1px solid #E2E8F0; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; }
.btn-submit { background: var(--gel-primary); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
.btn-submit:hover { background: var(--gel-primary-hover); }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" style="font-size:13px; color:#64748B; text-decoration:none; margin-bottom:8px; display:inline-block;"><i class="fas fa-arrow-left"></i> Retour aux écritures</a>
        <h1 class="gel-page-title">Saisie d'une Nouvelle Écriture</h1>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger d-flex align-items-center" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 8px; font-size:14px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-exclamation-circle me-2" style="font-size:18px;"></i>
    {{ $errors->first() }}
</div>
@endif

<form action="{{ route('gel-accountant.comptabilite.ecritures.store') }}" method="POST" id="ecritureForm">
    @csrf

    <!-- Informations Générales -->
    <div class="form-section">
        <div class="section-title"><i class="fas fa-info-circle"></i> Informations Générales</div>
        
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">Client (Dossier)</label>
                <select name="client_id" class="form-select">
                    <option value="">Cabinet (Interne)</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', session('active_client_id')) == $client->id ? 'selected' : '' }}>{{ $client->nom_entreprise }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Journal <span class="text-danger">*</span></label>
                <select name="journal_id" class="form-select" required>
                    <option value="">Sélectionnez un journal...</option>
                    @foreach($journaux as $j)
                        <option value="{{ $j->id }}" {{ old('journal_id') == $j->id ? 'selected' : '' }}>{{ $j->code }} - {{ $j->libelle }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Date de l'écriture <span class="text-danger">*</span></label>
                <input type="date" name="date_ecriture" class="form-control" value="{{ old('date_ecriture', date('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Libellé de l'opération <span class="text-danger">*</span></label>
                <input type="text" name="libelle" class="form-control" value="{{ old('libelle') }}" placeholder="Ex: Paiement Facture Fournisseur..." required oninput="updateLineLabels(this.value)">
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Réf. Pièce (Optionnel)</label>
                    <input type="text" name="ref_piece" class="form-control" value="{{ old('ref_piece') }}" placeholder="Ex: FAC-2023-001">
                </div>
                <div class="form-group">
                    <label class="form-label">Date Pièce (Optionnel)</label>
                    <input type="date" name="date_piece" class="form-control" value="{{ old('date_piece') }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Lignes d'écritures -->
    <div class="form-section">
        <div class="section-title"><i class="fas fa-list-ol"></i> Lignes de l'écriture</div>
        
        <table class="lines-table" id="linesTable">
            <thead>
                <tr>
                    <th style="width:25%;">Compte Général <span class="text-danger">*</span></th>
                    <th style="width:35%;">Libellé Ligne</th>
                    <th style="width:15%; text-align:right;">Débit (F)</th>
                    <th style="width:15%; text-align:right;">Crédit (F)</th>
                    <th style="width:5%;"></th>
                </tr>
            </thead>
            <tbody id="linesContainer">
                <!-- Row 1 (Débit suggéré) -->
                <tr class="ecriture-line">
                    <td>
                        <select name="compte_id[]" class="line-input" required>
                            <option value="">Sélectionner un compte...</option>
                            @foreach($comptes as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->intitule }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="libelle_ligne[]" class="line-input libelle-sync" placeholder="Libellé"></td>
                    <td><input type="number" name="debit[]" class="line-input line-amount debit-input" value="0" min="0" step="1" oninput="validateAmount(this, 'debit')"></td>
                    <td><input type="number" name="credit[]" class="line-input line-amount credit-input" value="0" min="0" step="1" oninput="validateAmount(this, 'credit')"></td>
                    <td><button type="button" class="btn-remove" onclick="removeLine(this)" disabled><i class="fas fa-times"></i></button></td>
                </tr>
                
                <!-- Row 2 (Crédit suggéré) -->
                <tr class="ecriture-line">
                    <td>
                        <select name="compte_id[]" class="line-input" required>
                            <option value="">Sélectionner un compte...</option>
                            @foreach($comptes as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->intitule }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="libelle_ligne[]" class="line-input libelle-sync" placeholder="Libellé"></td>
                    <td><input type="number" name="debit[]" class="line-input line-amount debit-input" value="0" min="0" step="1" oninput="validateAmount(this, 'debit')"></td>
                    <td><input type="number" name="credit[]" class="line-input line-amount credit-input" value="0" min="0" step="1" oninput="validateAmount(this, 'credit')"></td>
                    <td><button type="button" class="btn-remove" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
                </tr>
            </tbody>
        </table>
        
        <button type="button" class="btn-add-line" onclick="addLine()"><i class="fas fa-plus"></i> Ajouter une ligne</button>

        <div class="totals-area">
            <div id="balanceIndicator" class="balance-indicator ok">
                <i class="fas fa-check-circle"></i> Écriture équilibrée
            </div>
            
            <div style="display:flex; gap:40px;">
                <div class="total-box">
                    <div class="total-label">Total Débit</div>
                    <div class="total-value" id="totalDebit">0</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Total Crédit</div>
                    <div class="total-value" id="totalCredit">0</div>
                </div>
            </div>
        </div>
        
        <!-- Notes internes -->
        <div class="form-group" style="margin-top:24px;">
            <label class="form-label">Notes ou Mémo Interne</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Informations pour le cabinet..."></textarea>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="btn-cancel">Annuler</a>
        <button type="submit" class="btn-submit" id="btnSubmit"><i class="fas fa-save"></i> Enregistrer le brouillon</button>
    </div>

</form>

@push('scripts')
<script>
    // Synchroniser le libellé principal avec les lignes vides
    function updateLineLabels(val) {
        document.querySelectorAll('.libelle-sync').forEach(input => {
            if(input.value === '' || input.dataset.synced === "true") {
                input.value = val;
                input.dataset.synced = "true";
            }
        });
    }

    document.querySelectorAll('.libelle-sync').forEach(input => {
        input.addEventListener('input', function() {
            this.dataset.synced = "false";
        });
    });

    // Gestion exclusive Débit/Crédit sur une ligne et Recalcul
    function validateAmount(input, type) {
        const tr = input.closest('tr');
        const debitInput = tr.querySelector('.debit-input');
        const creditInput = tr.querySelector('.credit-input');
        
        const val = parseFloat(input.value) || 0;
        
        if(val > 0) {
            if(type === 'debit') { creditInput.value = 0; }
            if(type === 'credit') { debitInput.value = 0; }
        }
        
        calculateTotals();
    }

    function calculateTotals() {
        let totalD = 0;
        let totalC = 0;
        
        document.querySelectorAll('.debit-input').forEach(input => {
            totalD += parseFloat(input.value) || 0;
        });
        
        document.querySelectorAll('.credit-input').forEach(input => {
            totalC += parseFloat(input.value) || 0;
        });
        
        const debitEl = document.getElementById('totalDebit');
        const creditEl = document.getElementById('totalCredit');
        const indicator = document.getElementById('balanceIndicator');
        const btnSubmit = document.getElementById('btnSubmit');
        
        debitEl.innerText = new Intl.NumberFormat('fr-FR').format(totalD);
        creditEl.innerText = new Intl.NumberFormat('fr-FR').format(totalC);
        
        if (totalD === totalC && totalD > 0) {
            debitEl.classList.remove('unbalanced'); debitEl.classList.add('balanced');
            creditEl.classList.remove('unbalanced'); creditEl.classList.add('balanced');
            indicator.className = 'balance-indicator ok';
            indicator.innerHTML = '<i class="fas fa-check-circle"></i> Écriture équilibrée';
            btnSubmit.disabled = false;
        } else {
            debitEl.classList.remove('balanced'); debitEl.classList.add('unbalanced');
            creditEl.classList.remove('balanced'); creditEl.classList.add('unbalanced');
            indicator.className = 'balance-indicator error';
            
            if (totalD === 0 && totalC === 0) {
                indicator.innerHTML = '<i class="fas fa-info-circle"></i> Saisissez des montants';
                btnSubmit.disabled = true;
            } else {
                const diff = Math.abs(totalD - totalC);
                indicator.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Écart de ${new Intl.NumberFormat('fr-FR').format(diff)}`;
                btnSubmit.disabled = true;
            }
        }
    }

    function addLine() {
        const container = document.getElementById('linesContainer');
        const firstRow = container.querySelector('tr');
        const newRow = firstRow.cloneNode(true);
        
        // Reset values
        newRow.querySelector('select').value = "";
        
        const libelleInput = newRow.querySelector('.libelle-sync');
        const mainLibelle = document.querySelector('input[name="libelle"]').value;
        libelleInput.value = mainLibelle;
        libelleInput.dataset.synced = "true";
        
        newRow.querySelector('.debit-input').value = "0";
        newRow.querySelector('.credit-input').value = "0";
        newRow.querySelector('.btn-remove').disabled = false;
        
        container.appendChild(newRow);
        updateRemoveButtons();
        calculateTotals();
    }

    function removeLine(btn) {
        const container = document.getElementById('linesContainer');
        if (container.children.length > 2) { // Toujours garder au moins 2 lignes
            btn.closest('tr').remove();
            updateRemoveButtons();
            calculateTotals();
        } else {
            alert('Une écriture doit comporter au moins 2 lignes.');
        }
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.ecriture-line');
        rows.forEach((row, index) => {
            const btn = row.querySelector('.btn-remove');
            if (rows.length <= 2) {
                btn.disabled = true;
            } else {
                btn.disabled = false;
            }
        });
    }

    // Init
    calculateTotals();
    updateRemoveButtons();
</script>
@endpush
@endsection
