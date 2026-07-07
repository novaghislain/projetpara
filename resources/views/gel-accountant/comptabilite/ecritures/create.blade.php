@extends('layouts.gel-accountant')

@section('title', 'Nouvelle écriture - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Nouvelle écriture</h1>
        <p class="gel-page-subtitle">Saisissez une écriture comptable</p>
    </div>
    <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="gel-btn gel-btn-secondary">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<form method="POST" action="{{ route('gel-accountant.comptabilite.ecritures.store') }}" id="ecritureForm">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        {{-- Colonne gauche --}}
        <div class="gel-card">
            <div class="gel-card-header"><strong>Informations générales</strong></div>
            <div class="gel-card-body">
                <div class="gel-form-group">
                    <label>Client *</label>
                    <select name="client_id" class="gel-form-select" required>
                        <option value="">Sélectionner un client...</option>
                        @if(isset($clients) && count($clients) > 0)
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom_entreprise }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="gel-form-group">
                    <label>Journal *</label>
                    <select name="journal_id" class="gel-form-select" required>
                        <option value="">Sélectionner un journal...</option>
                        @if(isset($journaux) && count($journaux) > 0)
                            @foreach($journaux as $journal)
                                <option value="{{ $journal->id }}" {{ old('journal_id') == $journal->id ? 'selected' : '' }}>{{ $journal->code }} — {{ $journal->libelle }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="gel-form-group">
                    <label>Date d'écriture *</label>
                    <input type="date" name="date_ecriture" class="gel-form-control" value="{{ old('date_ecriture', date('Y-m-d')) }}" required>
                </div>

                <div class="gel-form-group">
                    <label>Date de pièce</label>
                    <input type="date" name="date_piece" class="gel-form-control" value="{{ old('date_piece') }}">
                </div>

                <div class="gel-form-group">
                    <label>Référence pièce</label>
                    <input type="text" name="ref_piece" class="gel-form-control" value="{{ old('ref_piece') }}" placeholder="Ex: FAC-2024-001">
                </div>
            </div>
        </div>

        {{-- Colonne droite --}}
        <div class="gel-card">
            <div class="gel-card-header"><strong>Libellé</strong></div>
            <div class="gel-card-body">
                <div class="gel-form-group">
                    <label>Libellé de l'écriture *</label>
                    <textarea name="libelle" class="gel-form-control" rows="3" required>{{ old('libelle') }}</textarea>
                </div>

                <div class="gel-form-group">
                    <label>Notes (interne)</label>
                    <textarea name="notes" class="gel-form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Lignes d'écriture --}}
    <div class="gel-card" style="margin-top:20px;">
        <div class="gel-card-header">
            <strong>Lignes d'écriture</strong>
            <div>
                <span id="totalDebitDisplay" style="margin-right:20px;font-size:13px;color:var(--gel-text-secondary);">
                    Total Débit: <strong style="color:var(--gel-text-primary);">0 FCFA</strong>
                </span>
                <span id="totalCreditDisplay" style="font-size:13px;color:var(--gel-text-secondary);">
                    Total Crédit: <strong style="color:var(--gel-text-primary);">0 FCFA</strong>
                </span>
                <span id="equilibreDisplay" style="margin-left:16px;font-size:13px;font-weight:600;"></span>
            </div>
        </div>
        <div class="gel-card-body" style="padding:0;">
            <table class="gel-table" id="lignesTable">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th style="width:150px;">Compte *</th>
                        <th>Libellé ligne</th>
                        <th style="width:120px;">Débit</th>
                        <th style="width:120px;">Crédit</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody id="lignesBody">
                    <tr class="ligne-row">
                        <td class="gel-text-center ligne-num">1</td>
                        <td>
                            <select name="compte_id[]" class="gel-form-select" required>
                                <option value="">Choisir...</option>
                                @if(isset($comptes) && count($comptes) > 0)
                                    @foreach($comptes as $compte)
                                        <option value="{{ $compte->id }}">{{ $compte->code }} — {{ $compte->intitule }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td>
                            <input type="text" name="libelle_ligne[]" class="gel-form-control" placeholder="Libellé ligne">
                        </td>
                        <td>
                            <input type="text" name="debit[]" class="gel-form-control gel-text-right montant-input debit-input" placeholder="0" oninput="formatMontant(this);recalcTotaux();">
                        </td>
                        <td>
                            <input type="text" name="credit[]" class="gel-form-control gel-text-right montant-input credit-input" placeholder="0" oninput="formatMontant(this);recalcTotaux();">
                        </td>
                        <td>
                            <button type="button" class="gel-btn gel-btn-sm gel-btn-danger" onclick="this.closest('tr').remove();renumero();recalcTotaux();" style="padding:4px 8px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="padding:12px 14px;border-top:1px solid var(--gel-border);">
                <button type="button" class="gel-btn gel-btn-sm gel-btn-secondary" onclick="ajouterLigne()">
                    <i class="bi bi-plus-circle"></i> Ajouter une ligne
                </button>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px;">
        <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="gel-btn gel-btn-secondary">Annuler</a>
        <button type="submit" class="gel-btn gel-btn-primary">
            <i class="bi bi-save"></i> Enregistrer l'écriture
        </button>
    </div>
</form>

<script>
    let ligneCount = 1;

    function ajouterLigne() {
        ligneCount++;
        const tbody = document.getElementById('lignesBody');
        const row = document.createElement('tr');
        row.className = 'ligne-row';
        row.innerHTML = `
            <td class="gel-text-center ligne-num">${ligneCount}</td>
            <td>
                <select name="compte_id[]" class="gel-form-select" required>
                    <option value="">Choisir...</option>
                    @if(isset($comptes) && count($comptes) > 0)
                        @foreach($comptes as $compte)
                            <option value="{{ $compte->id }}">{{ $compte->code }} — {{ $compte->intitule }}</option>
                        @endforeach
                    @endif
                </select>
            </td>
            <td><input type="text" name="libelle_ligne[]" class="gel-form-control" placeholder="Libellé ligne"></td>
            <td><input type="text" name="debit[]" class="gel-form-control gel-text-right montant-input debit-input" placeholder="0" oninput="formatMontant(this);recalcTotaux();"></td>
            <td><input type="text" name="credit[]" class="gel-form-control gel-text-right montant-input credit-input" placeholder="0" oninput="formatMontant(this);recalcTotaux();"></td>
            <td><button type="button" class="gel-btn gel-btn-sm gel-btn-danger" onclick="this.closest('tr').remove();renumero();recalcTotaux();" style="padding:4px 8px;"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(row);
    }

    function renumero() {
        const rows = document.querySelectorAll('.ligne-row');
        rows.forEach((row, i) => {
            row.querySelector('.ligne-num').textContent = i + 1;
        });
        ligneCount = rows.length;
    }

    function formatMontant(input) {
        let val = input.value.replace(/[^0-9]/g, '');
        if (val) input.value = parseInt(val, 10).toLocaleString('fr-FR');
    }

    function recalcTotaux() {
        let totalDebit = 0, totalCredit = 0;
        document.querySelectorAll('.debit-input').forEach(inp => {
            totalDebit += parseInt(inp.value.replace(/[^0-9]/g, '')) || 0;
        });
        document.querySelectorAll('.credit-input').forEach(inp => {
            totalCredit += parseInt(inp.value.replace(/[^0-9]/g, '')) || 0;
        });
        document.getElementById('totalDebitDisplay').innerHTML = 'Total Débit: <strong>' + totalDebit.toLocaleString('fr-FR') + ' FCFA</strong>';
        document.getElementById('totalCreditDisplay').innerHTML = 'Total Crédit: <strong>' + totalCredit.toLocaleString('fr-FR') + ' FCFA</strong>';

        const equilibre = document.getElementById('equilibreDisplay');
        if (totalDebit === totalCredit && totalDebit > 0) {
            equilibre.innerHTML = '<span style="color:var(--gel-success);"><i class="bi bi-check-circle-fill"></i> Équilibrée</span>';
        } else if (totalDebit > 0 || totalCredit > 0) {
            const diff = Math.abs(totalDebit - totalCredit);
            equilibre.innerHTML = '<span style="color:var(--gel-danger);"><i class="bi bi-exclamation-circle-fill"></i> Différence: ' + diff.toLocaleString('fr-FR') + ' FCFA</span>';
        } else {
            equilibre.innerHTML = '';
        }
    }

    // Soumission: convertir les montants formatés en nombres
    document.getElementById('ecritureForm').addEventListener('submit', function() {
        document.querySelectorAll('.montant-input').forEach(inp => {
            inp.value = inp.value.replace(/[^0-9]/g, '');
        });
    });
</script>
@endsection
