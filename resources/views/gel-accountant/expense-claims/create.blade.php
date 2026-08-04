@extends('layouts.gel-accountant')

@section('title', 'Soumettre une note de frais')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-receipt" style="color:var(--gel-primary); margin-right:8px;"></i> Soumettre une note de frais</h1>
        <p class="gel-page-subtitle">Enregistrez les frais professionnels d'un employé</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="{{ route('gel-accountant.expense-claims.store') }}" enctype="multipart/form-data">
@csrf

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px; color:var(--gel-text-primary);"><i class="fas fa-info-circle" style="color:var(--gel-primary);"></i> Détails de la dépense</h3>
            
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label">Employé concerné *</label>
                <select name="employee_id" class="form-select" required>
                    <option value="">— Sélectionner l'employé —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->position }})
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Si la liste est vide, assurez-vous d'avoir enregistré des employés actifs dans le module RH.</div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Catégorie *</label>
                    <select name="category" class="form-select" required>
                        <option value="">— Sélectionner —</option>
                        <option value="repas" {{ old('category') == 'repas' ? 'selected' : '' }}>Repas / Restauration</option>
                        <option value="transport" {{ old('category') == 'transport' ? 'selected' : '' }}>Déplacement / Transport</option>
                        <option value="hebergement" {{ old('category') == 'hebergement' ? 'selected' : '' }}>Hébergement / Hôtel</option>
                        <option value="fournitures" {{ old('category') == 'fournitures' ? 'selected' : '' }}>Fournitures / Matériel</option>
                        <option value="autre" {{ old('category') == 'autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Montant (FCFA) *</label>
                    <input type="number" name="amount" class="form-control" min="0" step="1" value="{{ old('amount') }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description / Motif *</label>
                <textarea name="description" class="form-control" rows="3" required placeholder="Ex: Déjeuner d'affaires avec le client X">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label">Justificatif (Reçu, Facture)</label>
                <input type="file" name="receipt" class="form-control" accept="image/*,.pdf">
                <div class="form-text">Formats acceptés : PDF, JPG, PNG. Taille max : 5Mo.</div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('gel-accountant.expense-claims.index') }}" class="gel-btn gel-btn-secondary">Annuler</a>
                <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Soumettre</button>
            </div>
        </div>
    </div>
</div>
</form>

@endsection
