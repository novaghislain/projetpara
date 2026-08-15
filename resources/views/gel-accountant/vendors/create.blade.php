@extends('layouts.gel-accountant')

@section('title', 'Nouveau Fournisseur')

@push('styles')
<style>
/* ==========================================================================
   CREATE VENDOR - DESIGN
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

.grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
.form-control, .form-select {
    width: 100%; padding: 10px 14px; border: 1px solid #E2E8F0;
    border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s;
    background: #F8FAFC;
}
.form-control:focus, .form-select:focus { background: white; border-color: var(--gel-primary); }

.form-actions { display: flex; justify-content: flex-end; gap: 16px; margin-top: 32px; }
.btn-cancel { background: white; color: #475569; border: 1px solid #E2E8F0; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; }
.btn-submit { background: var(--gel-primary); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-submit:hover { background: var(--gel-primary-hover); }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <a href="{{ route('gel-accountant.vendors.index') }}" style="font-size:13px; color:#64748B; text-decoration:none; margin-bottom:8px; display:inline-block;"><i class="fas fa-arrow-left"></i> Retour aux fournisseurs</a>
        <h1 class="gel-page-title">Nouveau Fournisseur</h1>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    {{ $errors->first() }}
</div>
@endif

<form action="{{ route('gel-accountant.partners.store') }}" method="POST">
    @csrf
    <input type="hidden" name="type" value="fournisseur">

    <div class="form-section">
        <div class="section-title"><i class="fas fa-industry"></i> Informations de l'entreprise</div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Numéro IFU (Identifiant Fiscal)</label>
                <input type="text" name="tax_id" class="form-control" value="{{ old('tax_id') }}">
            </div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Prénom du contact principal</label>
                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Nom du contact principal</label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-title"><i class="fas fa-address-card"></i> Coordonnées</div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Adresse Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Adresse complète</label>
            <textarea name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
        </div>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">Ville</label>
                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Pays</label>
                <input type="text" name="country" class="form-control" value="{{ old('country', 'Bénin') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Site Web</label>
                <input type="url" name="website" class="form-control" value="{{ old('website') }}" placeholder="https://">
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-title"><i class="fas fa-university"></i> Informations bancaires (Optionnel)</div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Nom de la Banque</label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label">RIB / IBAN</label>
                <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account') }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Notes ou Conditions de paiement</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('gel-accountant.vendors.index') }}" class="btn-cancel">Annuler</a>
        <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer le fournisseur</button>
    </div>
</form>
@endsection
