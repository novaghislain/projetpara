@extends('layouts.gel')

@section('title', "Modifier écriture {$ecriture->numero}")

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Modifier {{ $ecriture->numero }}</h1>
        <p class="page-subtitle">{{ $ecriture->libelle }}</p>
    </div>
    <div>
        <a href="{{ route('gel.comptabilite.ecritures.show', $ecriture->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="card-dashboard">
    <div class="card-body p-4">
        <form action="{{ route('gel.comptabilite.ecritures.update', $ecriture->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Journal</label>
                    <select name="journal_id" class="form-select" required>
                        @foreach($journaux as $j)
                        <option value="{{ $j->id }}" {{ old('journal_id', $ecriture->journal_id) == $j->id ? 'selected' : '' }}>
                            {{ $j->code }} — {{ $j->libelle }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Exercice</label>
                    <select name="exercice_id" class="form-select" required>
                        @foreach($exercices as $ex)
                        <option value="{{ $ex->id }}" {{ old('exercice_id', $ecriture->exercice_id) == $ex->id ? 'selected' : '' }}>{{ $ex->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" name="date_ecriture" class="form-control" value="{{ old('date_ecriture', $ecriture->date_ecriture->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date pièce</label>
                    <input type="date" name="date_piece" class="form-control" value="{{ old('date_piece', $ecriture->date_piece->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Réf. pièce</label>
                    <input type="text" name="reference_piece" class="form-control" value="{{ old('reference_piece', $ecriture->reference_piece) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">—</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id', $ecriture->client_id) == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Libellé</label>
                    <input type="text" name="libelle" class="form-control" value="{{ old('libelle', $ecriture->libelle) }}" required>
                </div>
            </div>

            <h5 class="fw-bold mb-2" style="color:var(--gel-primary);">Lignes d'écriture</h5>
            <div id="lignesContainer">
                @foreach($ecriture->lignes as $i => $ligne)
                <div class="ligne-entry" id="ligne-{{ $i }}" style="background:#f8f9fa;border-radius:8px;padding:0.75rem;margin-bottom:0.5rem;border:1px solid var(--gel-border);">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <select name="lignes[{{ $i }}][compte_id]" class="form-select form-select-sm" required>
                                <option value="">Compte...</option>
                            </select>
                            <small class="text-muted">{{ $ligne->compte?->code }} — {{ $ligne->compte?->intitule }}</small>
                        </div>
                        <div class="col-md-2">
                            <select name="lignes[{{ $i }}][sens]" class="form-select form-select-sm">
                                <option value="debit" {{ $ligne->sens == 'debit' ? 'selected' : '' }}>Débit</option>
                                <option value="credit" {{ $ligne->sens == 'credit' ? 'selected' : '' }}>Crédit</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="lignes[{{ $i }}][montant]" class="form-control form-control-sm montant-input"
                                   value="{{ $ligne->montant }}" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="lignes[{{ $i }}][libelle_ligne]" class="form-control form-control-sm"
                                   value="{{ $ligne->libelle_ligne }}" placeholder="Libellé ligne">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne({{ $i }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="ajouterLigne()">
                    <i class="bi bi-plus-lg"></i> Ajouter une ligne
                </button>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Enregistrer</button>
                <a href="{{ route('gel.comptabilite.ecritures.show', $ecriture->id) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let ligneIndex = {{ $ecriture->lignes->count() }};
function ajouterLigne() {
    const container = document.getElementById('lignesContainer');
    const div = document.createElement('div');
    div.className = 'ligne-entry';
    div.id = `ligne-${ligneIndex}`;
    div.style = 'background:#f8f9fa;border-radius:8px;padding:0.75rem;margin-bottom:0.5rem;border:1px solid var(--gel-border);';
    div.innerHTML = `
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="lignes[${ligneIndex}][compte_id]" class="form-select form-select-sm" required>
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
                <input type="number" name="lignes[${ligneIndex}][montant]" class="form-control form-control-sm montant-input" placeholder="Montant" step="0.01" min="0.01" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="lignes[${ligneIndex}][libelle_ligne]" class="form-control form-control-sm" placeholder="Libellé ligne">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(${ligneIndex})"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    `;
    container.appendChild(div);
    ligneIndex++;
}
function supprimerLigne(index) {
    const el = document.getElementById(`ligne-${index}`);
    if (el) el.remove();
}
</script>
@endsection
