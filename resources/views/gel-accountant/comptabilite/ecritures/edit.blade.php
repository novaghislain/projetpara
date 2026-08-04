@extends('layouts.gel-accountant')

@section('title', "Modifier écriture {$ecriture->numero} — GEL Cabinet")

@section('content')
<div class="gel-page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h1 class="gel-page-title" style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;">Modifier {{ $ecriture->numero }}</h1>
        <p class="gel-page-subtitle" style="color: #64748b; margin-top: 4px; font-size: 14px;">{{ $ecriture->libelle }}</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('gel-accountant.comptabilite.ecritures.show', $ecriture->id) }}" class="gel-btn gel-btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="bi bi-arrow-left"></i> Annuler
        </a>
    </div>
</div>

<div class="gel-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0;">
            <i class="bi bi-pencil-square me-2" style="color: #3b82f6;"></i>Modification de l'écriture
        </h3>
    </div>
    <div style="padding: 24px;">
        <form action="{{ route('gel-accountant.comptabilite.ecritures.update', $ecriture->id) }}" method="POST">
            @csrf @method('PUT')

            {{-- En-tête de l'écriture --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Journal *</label>
                    <select name="journal_id" class="gel-form-select" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;" required>
                        @foreach($journaux as $j)
                            <option value="{{ $j->id }}" {{ old('journal_id', $ecriture->journal_id) == $j->id ? 'selected' : '' }}>
                                {{ $j->code }} — {{ $j->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Date écriture *</label>
                    <input type="date" name="date_ecriture" class="gel-form-control" value="{{ old('date_ecriture', \Carbon\Carbon::parse($ecriture->date_ecriture)->format('Y-m-d')) }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Client rattaché</label>
                    <select name="client_id" class="gel-form-select" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <option value="">— Tous —</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ old('client_id', $ecriture->client_id) == $c->id ? 'selected' : '' }}>{{ $c->nom_entreprise }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="grid-column: span 2;">
                    <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Libellé de l'écriture *</label>
                    <input type="text" name="libelle" class="gel-form-control" value="{{ old('libelle', $ecriture->libelle) }}" required placeholder="Libellé général de l'écriture" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>
            </div>

            {{-- Lignes d'écriture --}}
            <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                <h4 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0;">
                    <i class="bi bi-list-columns-reverse me-2" style="color: #059669;"></i>Lignes comptables
                </h4>
                <button type="button" onclick="ajouterLigne()" class="gel-btn gel-btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; font-size: 13px;">
                    <i class="bi bi-plus-lg"></i> Ajouter une ligne
                </button>
            </div>

            {{-- En-têtes colonnes --}}
            <div style="display: grid; grid-template-columns: 1fr 2fr 200px 130px 44px; gap: 8px; margin-bottom: 8px; padding: 0 8px;">
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Sens</div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Compte OHADA</div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Libellé ligne</div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Montant</div>
                <div></div>
            </div>

            <div id="lignesContainer">
                @foreach($ecriture->lignes as $i => $ligne)
                <div class="ligne-entry" id="ligne-{{ $i }}" style="display: grid; grid-template-columns: 1fr 2fr 200px 130px 44px; gap: 8px; align-items: center; margin-bottom: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; {{ $ligne->sens === 'debit' ? 'border-left: 3px solid #10b981;' : 'border-left: 3px solid #ef4444;' }}">
                    <div>
                        <select name="lignes[{{ $i }}][sens]" class="gel-form-select ligne-sens" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 600;" onchange="updateLineBorderColor(this)">
                            <option value="debit" {{ $ligne->sens == 'debit' ? 'selected' : '' }}>⬆ Débit</option>
                            <option value="credit" {{ $ligne->sens == 'credit' ? 'selected' : '' }}>⬇ Crédit</option>
                        </select>
                    </div>
                    <div>
                        <select name="lignes[{{ $i }}][compte_id]" class="gel-form-select" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;" required>
                            <option value="">— Sélectionner —</option>
                            @foreach($comptes as $compte)
                                <option value="{{ $compte->id }}" {{ (old("lignes.{$i}.compte_id", $ligne->compte_id) == $compte->id) ? 'selected' : '' }}>{{ $compte->code }} — {{ $compte->intitule }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="text" name="lignes[{{ $i }}][libelle_ligne]" class="gel-form-control" value="{{ $ligne->libelle_ligne }}" placeholder="Libellé..." style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                    </div>
                    <div>
                        <input type="number" name="lignes[{{ $i }}][montant]" class="gel-form-control montant-input" value="{{ $ligne->montant }}" step="1" min="1" required placeholder="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; text-align: right;" oninput="updateTotaux()">
                    </div>
                    <div>
                        <button type="button" onclick="supprimerLigne({{ $i }})" style="background: none; border: 1px solid #e2e8f0; border-radius: 6px; padding: 7px 10px; color: #94a3b8; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.color='#ef4444';this.style.borderColor='#ef4444'" onmouseout="this.style.color='#94a3b8';this.style.borderColor='#e2e8f0'">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Totaux --}}
            <div id="totauxDisplay" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-top: 16px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="text-align: center;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Débit</div>
                    <div id="totalDebit" style="font-size: 18px; font-weight: 700; color: #059669;">0 FCFA</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Crédit</div>
                    <div id="totalCredit" style="font-size: 18px; font-weight: 700; color: #dc2626;">0 FCFA</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Équilibre</div>
                    <div id="totalBalance" style="font-size: 16px; font-weight: 700; color: #f59e0b;">— FCFA</div>
                </div>
            </div>

            {{-- Actions --}}
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('gel-accountant.comptabilite.ecritures.show', $ecriture->id) }}" class="gel-btn gel-btn-secondary">Annuler</a>
                <button type="submit" class="gel-btn gel-btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-check-lg"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let ligneIndex = {{ $ecriture->lignes->count() }};

function updateTotaux() {
    let totDebit = 0, totCredit = 0;
    document.querySelectorAll('.ligne-entry').forEach(function(entry) {
        const sens = entry.querySelector('[name$="[sens]"]')?.value;
        const montant = parseFloat(entry.querySelector('[name$="[montant]"]')?.value) || 0;
        if (sens === 'debit') totDebit += montant;
        else totCredit += montant;
    });

    const fmt = n => n.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('totalDebit').textContent = fmt(totDebit);
    document.getElementById('totalCredit').textContent = fmt(totCredit);

    const diff = totDebit - totCredit;
    const balEl = document.getElementById('totalBalance');
    if (diff === 0 && totDebit > 0) {
        balEl.textContent = '✔ Équilibrée';
        balEl.style.color = '#10b981';
    } else {
        balEl.textContent = (diff !== 0 ? 'Écart: ' + Math.abs(diff).toLocaleString('fr-FR') + ' FCFA' : '—');
        balEl.style.color = diff !== 0 ? '#ef4444' : '#f59e0b';
    }
}

function updateLineBorderColor(selectEl) {
    const entry = selectEl.closest('.ligne-entry');
    if (selectEl.value === 'debit') {
        entry.style.borderLeft = '3px solid #10b981';
    } else {
        entry.style.borderLeft = '3px solid #ef4444';
    }
    updateTotaux();
}

function getComptesOptions(selectedId) {
    const comptes = @json($comptes->map(fn($c) => ['id' => $c->id, 'code' => $c->code, 'intitule' => $c->intitule]));
    return '<option value="">— Sélectionner —</option>' + comptes.map(c =>
        `<option value="${c.id}" ${c.id == selectedId ? 'selected' : ''}>${c.code} — ${c.intitule}</option>`
    ).join('');
}

function ajouterLigne() {
    const container = document.getElementById('lignesContainer');
    const div = document.createElement('div');
    div.className = 'ligne-entry';
    div.id = `ligne-${ligneIndex}`;
    div.style.cssText = 'display: grid; grid-template-columns: 1fr 2fr 200px 130px 44px; gap: 8px; align-items: center; margin-bottom: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; border-left: 3px solid #10b981;';

    div.innerHTML = `
        <div>
            <select name="lignes[${ligneIndex}][sens]" class="gel-form-select ligne-sens" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 600;" onchange="updateLineBorderColor(this)">
                <option value="debit">⬆ Débit</option>
                <option value="credit">⬇ Crédit</option>
            </select>
        </div>
        <div>
            <select name="lignes[${ligneIndex}][compte_id]" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;" required>
                ${getComptesOptions(null)}
            </select>
        </div>
        <div>
            <input type="text" name="lignes[${ligneIndex}][libelle_ligne]" placeholder="Libellé..." style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
        </div>
        <div>
            <input type="number" name="lignes[${ligneIndex}][montant]" class="montant-input" step="1" min="1" placeholder="0" required style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; text-align: right;" oninput="updateTotaux()">
        </div>
        <div>
            <button type="button" onclick="supprimerLigne(${ligneIndex})" style="background: none; border: 1px solid #e2e8f0; border-radius: 6px; padding: 7px 10px; color: #94a3b8; cursor: pointer;" onmouseover="this.style.color='#ef4444';this.style.borderColor='#ef4444'" onmouseout="this.style.color='#94a3b8';this.style.borderColor='#e2e8f0'">
                <i class="bi bi-trash3"></i>
            </button>
        </div>
    `;
    container.appendChild(div);
    ligneIndex++;
    updateTotaux();
}

function supprimerLigne(index) {
    const el = document.getElementById(`ligne-${index}`);
    if (el) { el.remove(); updateTotaux(); }
}

// Calcul initial
document.addEventListener('DOMContentLoaded', updateTotaux);
</script>
@endsection
