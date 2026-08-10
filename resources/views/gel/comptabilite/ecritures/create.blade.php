@extends('layouts.gel')

@section('title', 'Nouvelle écriture — Comptabilité')

@section('styles')
<style>
    /* QuickBooks Online Takeover Style */
    .qbo-takeover {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        min-height: calc(100vh - 120px);
        display: flex;
        flex-direction: column;
    }
    .qbo-header {
        padding: 1.5rem 2rem;
        border-bottom: 2px solid #2ca01c; /* QBO Green accent */
    }
    .qbo-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1.5rem;
    }
    
    /* Header Fields */
    .qbo-field-group { margin-bottom: 1rem; }
    .qbo-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    .qbo-input, .qbo-select {
        border: 1px solid #d1d5db;
        border-radius: 4px;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        width: 100%;
        color: #111827;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .qbo-input:focus, .qbo-select:focus {
        outline: none;
        border-color: #2ca01c;
        box-shadow: 0 0 0 2px rgba(44, 160, 28, 0.2);
    }

    /* Spreadsheet Grid */
    .qbo-grid-wrapper {
        flex-grow: 1;
        padding: 0;
        overflow-x: auto;
    }
    .qbo-grid {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }
    .qbo-grid th {
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
        border-top: 1px solid #e5e7eb;
    }
    .qbo-grid td {
        padding: 0;
        border-bottom: 1px solid #e5e7eb;
        position: relative;
    }
    
    /* Cell Inputs */
    .cell-input {
        width: 100%;
        height: 100%;
        min-height: 40px;
        padding: 0.5rem 1rem;
        border: none;
        background: transparent;
        font-size: 0.9rem;
        color: #111827;
        outline: none;
    }
    .cell-input:focus {
        background: #ffffff;
        box-shadow: inset 0 0 0 2px #2ca01c;
        position: relative;
        z-index: 10;
    }
    .cell-select {
        width: 100%;
        height: 100%;
        min-height: 40px;
        padding: 0.5rem 1rem;
        border: none;
        background: transparent;
        font-size: 0.9rem;
        color: #111827;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
    }
    .cell-select:focus {
        background: #ffffff;
        box-shadow: inset 0 0 0 2px #2ca01c;
        position: relative;
        z-index: 10;
    }
    
    .cell-amount { text-align: right; font-variant-numeric: tabular-nums; }
    
    /* Hover on row */
    .qbo-grid tbody tr:hover td { background: #f3f4f6; }
    
    .remove-row-btn {
        color: #9ca3af;
        background: none;
        border: none;
        padding: 0.5rem;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.2s, color 0.2s;
    }
    .qbo-grid tbody tr:hover .remove-row-btn { opacity: 1; }
    .remove-row-btn:hover { color: #ef4444; }

    /* Footer & Totals */
    .qbo-footer {
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    
    .qbo-totals-box {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        min-width: 300px;
    }
    .total-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        color: #4b5563;
    }
    .total-row.grand-total {
        font-size: 1.1rem;
        font-weight: 700;
        color: #111827;
        border-top: 1px solid #e5e7eb;
        padding-top: 0.5rem;
        margin-bottom: 0;
    }
    .difference-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }
    .diff-ok { background: #def7ec; color: #03543f; }
    .diff-error { background: #fde8e8; color: #9b1c1c; }

    .btn-qbo-primary {
        background-color: #2ca01c;
        color: white;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-qbo-primary:hover { background-color: #238016; color: white; }
    .btn-qbo-primary:disabled { background-color: #9ca3af; cursor: not-allowed; }
    
    .btn-qbo-secondary {
        background-color: transparent;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 0.6rem 1.5rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-qbo-secondary:hover { background-color: #f3f4f6; color: #374151; }
    
    .add-lines-btn {
        background: transparent;
        border: 1px dashed #d1d5db;
        color: #4b5563;
        width: 100%;
        padding: 0.75rem;
        text-align: center;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s;
    }
    .add-lines-btn:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
</style>
@endsection

@section('content')
<form action="{{ route('gel.comptabilite.ecritures.store') }}" method="POST" id="ecritureForm">
    @csrf
    <div class="qbo-takeover">
        <!-- HEADER -->
        <div class="qbo-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="qbo-title mb-0">Créer une écriture de journal</h1>
                <a href="{{ route('gel.comptabilite.ecritures.index') }}" class="btn-qbo-secondary">
                    <i class="bi bi-x-lg me-1"></i> Fermer
                </a>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="qbo-field-group">
                        <label class="qbo-label">Journal *</label>
                        <select name="journal_id" class="qbo-select" required>
                            <option value="">Sélectionner un journal</option>
                            @foreach($journaux as $j)
                            <option value="{{ $j->id }}" {{ old('journal_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->code }} — {{ $j->libelle }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="qbo-field-group">
                        <label class="qbo-label">Date *</label>
                        <input type="date" name="date_ecriture" class="qbo-input" value="{{ old('date_ecriture', now()->format('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="qbo-field-group">
                        <label class="qbo-label">N° de Pièce</label>
                        <input type="text" name="reference_piece" class="qbo-input" value="{{ old('reference_piece') }}" placeholder="Facultatif">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="qbo-field-group">
                        <label class="qbo-label">Libellé de l'écriture *</label>
                        <input type="text" name="libelle" class="qbo-input" value="{{ old('libelle') }}" placeholder="Ex: Saisie des ventes du mois" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRID -->
        <div class="qbo-grid-wrapper">
            <table class="qbo-grid" id="journalGrid">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="width: 35%;">COMPTE</th>
                        <th style="width: 25%;">DESCRIPTION</th>
                        <th style="width: 15%; text-align: right;">DÉBIT</th>
                        <th style="width: 15%; text-align: right;">CRÉDIT</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="gridBody">
                    <!-- Javascript will populate rows here -->
                </tbody>
            </table>
            <button type="button" class="add-lines-btn" onclick="addRows(2)">
                <i class="bi bi-plus-lg"></i> Ajouter 2 lignes
            </button>
        </div>

        <!-- FOOTER & TOTALS -->
        <div class="qbo-footer">
            <div class="d-flex gap-3">
                <button type="submit" class="btn-qbo-primary" id="btnSubmit">
                    @can('comptabilite.valider') Enregistrer et valider @else Enregistrer (Brouillon) @endcan
                </button>
                <button type="button" class="btn-qbo-secondary" onclick="document.getElementById('ecritureForm').reset(); calculateTotals();">
                    Effacer tout
                </button>
            </div>
            
            <div class="qbo-totals-box">
                <div class="total-row">
                    <span>Total Débit</span>
                    <span id="displayDebit">0,00</span>
                </div>
                <div class="total-row">
                    <span>Total Crédit</span>
                    <span id="displayCredit">0,00</span>
                </div>
                <div class="total-row grand-total mt-2">
                    <span>Différence</span>
                    <span id="displayDiff">0,00</span>
                </div>
                <div class="text-end">
                    <span id="statusBadge" class="difference-badge diff-ok">Équilibré</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden inputs for validation logic -->
    <input type="hidden" name="exercice_id" id="hiddenExerciceId" value="{{ $exercices->first()?->id }}">
</form>

<!-- Template for a single row -->
<template id="rowTemplate">
    <tr>
        <td class="text-center text-muted" style="font-size:0.8rem;" data-row-num>1</td>
        <td>
            <select class="cell-select compte-select" name="lignes[__INDEX__][compte_id]" required>
                <option value="">Sélectionnez un compte...</option>
                <!-- Omiting the full loop in template to avoid massive HTML, injected via JS -->
            </select>
        </td>
        <td>
            <input type="text" class="cell-input" name="lignes[__INDEX__][libelle_ligne]" placeholder="Description (optionnel)">
        </td>
        <td>
            <input type="number" step="0.01" class="cell-input cell-amount debit-input" name="lignes[__INDEX__][debit]" placeholder="0,00" onchange="handleAmountChange(this, 'debit')">
        </td>
        <td>
            <input type="number" step="0.01" class="cell-input cell-amount credit-input" name="lignes[__INDEX__][credit]" placeholder="0,00" onchange="handleAmountChange(this, 'credit')">
        </td>
        <td class="text-center">
            <button type="button" class="remove-row-btn" onclick="removeRow(this)" title="Supprimer la ligne">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

@endsection

@section('scripts')
<script>
    // Load accounts as JS array to populate dynamically
    const comptes = [
        @foreach(\App\Models\Gel\PlanComptableSyscohada::where('est_actif', true)->orderBy('numero')->get() as $c)
            { id: "{{ $c->id }}", text: "{{ $c->numero }} - {{ addslashes($c->libelle) }}" },
        @endforeach
    ];

    let rowIndex = 0;

    function addRows(count = 1) {
        const template = document.getElementById('rowTemplate').innerHTML;
        const tbody = document.getElementById('gridBody');
        
        for (let i = 0; i < count; i++) {
            let tr = document.createElement('tr');
            tr.innerHTML = template.replace(/__INDEX__/g, rowIndex);
            tbody.appendChild(tr);
            
            // Populate select options
            let select = tr.querySelector('.compte-select');
            comptes.forEach(c => {
                let option = new Option(c.text, c.id);
                select.add(option);
            });

            rowIndex++;
        }
        updateRowNumbers();
    }

    function removeRow(btn) {
        if (document.querySelectorAll('#gridBody tr').length <= 2) {
            alert('Vous devez avoir au moins 2 lignes pour une écriture.');
            return;
        }
        btn.closest('tr').remove();
        updateRowNumbers();
        calculateTotals();
    }

    function updateRowNumbers() {
        const rows = document.querySelectorAll('#gridBody tr');
        rows.forEach((row, index) => {
            let td = row.querySelector('[data-row-num]');
            if (td) td.textContent = index + 1;
        });
    }

    function handleAmountChange(input, type) {
        let val = parseFloat(input.value);
        if (isNaN(val) || val < 0) {
            input.value = '';
        } else {
            input.value = val.toFixed(2);
        }
        
        // Clear the opposite field on the same row
        let row = input.closest('tr');
        if (type === 'debit' && input.value !== '') {
            row.querySelector('.credit-input').value = '';
        } else if (type === 'credit' && input.value !== '') {
            row.querySelector('.debit-input').value = '';
        }
        
        calculateTotals();
    }

    function calculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll('.debit-input').forEach(input => {
            let val = parseFloat(input.value);
            if (!isNaN(val)) totalDebit += val;
        });

        document.querySelectorAll('.credit-input').forEach(input => {
            let val = parseFloat(input.value);
            if (!isNaN(val)) totalCredit += val;
        });

        let diff = Math.abs(totalDebit - totalCredit);
        let isBalanced = (totalDebit === totalCredit) && (totalDebit > 0);

        document.getElementById('displayDebit').textContent = formatCurrency(totalDebit);
        document.getElementById('displayCredit').textContent = formatCurrency(totalCredit);
        document.getElementById('displayDiff').textContent = formatCurrency(diff);

        let badge = document.getElementById('statusBadge');
        let btnSubmit = document.getElementById('btnSubmit');

        if (isBalanced) {
            badge.className = 'difference-badge diff-ok';
            badge.textContent = 'Équilibré';
            btnSubmit.disabled = false;
        } else {
            badge.className = 'difference-badge diff-error';
            badge.textContent = 'Déséquilibré';
            btnSubmit.disabled = true;
        }
    }

    function formatCurrency(num) {
        return num.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Keyboard navigation (Tab/Enter)
    document.getElementById('gridBody').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent form submission
            // Move focus to next input
            let focusable = Array.from(document.querySelectorAll('.cell-input, .cell-select'));
            let index = focusable.indexOf(document.activeElement);
            if (index > -1 && index < focusable.length - 1) {
                focusable[index + 1].focus();
            } else {
                addRows(1);
                setTimeout(() => {
                    let newInputs = document.querySelectorAll('.cell-select');
                    newInputs[newInputs.length - 1].focus();
                }, 50);
            }
        }
    });

    // Initialize with 4 empty rows
    document.addEventListener('DOMContentLoaded', () => {
        addRows(4);
        calculateTotals();
    });
</script>
@endsection
