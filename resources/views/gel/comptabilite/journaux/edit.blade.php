@extends('layouts.gel')

@section('title', "Modifier {$journal->code} — Journal")

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Modifier {{ $journal->code }} — {{ $journal->libelle }}</h1>
        <p class="page-subtitle">Mettez à jour les informations du journal.</p>
    </div>
    <div>
        <a href="{{ route('gel.comptabilite.journaux.show', $journal->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="card-dashboard">
    <div class="card-body p-4">
        <form action="{{ route('gel.comptabilite.journaux.update', $journal->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Code</label>
                    <input type="text" class="form-control" value="{{ $journal->code }}" disabled>
                    <small class="text-muted">Le code ne peut pas être modifié.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror"
                           value="{{ old('libelle', $journal->libelle) }}" required>
                    @error('libelle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Type</label>
                    <select name="type" class="form-select">
                        <option value="">—</option>
                        @foreach(['achats','ventes','banque','caisse','divers','paie','immobilisations'] as $t)
                        <option value="{{ $t }}" {{ old('type', $journal->type) == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Actif</label>
                    <div class="pt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="actif" value="1"
                                   {{ old('actif', $journal->actif) ? 'checked' : '' }} id="actifSwitch">
                            <label class="form-check-label" for="actifSwitch">Journal actif</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Enregistrer</button>
                <a href="{{ route('gel.comptabilite.journaux.show', $journal->id) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
