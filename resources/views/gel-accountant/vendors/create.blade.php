@extends('layouts.gel-accountant')

@section('title', 'Ajouter un fournisseur')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-truck-loading" style="color:var(--gel-primary); margin-right:8px;"></i> Ajouter un fournisseur</h1>
        <p class="gel-page-subtitle">Créez une fiche fournisseur complète avec ses coordonnées et conditions commerciales.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="{{ route('gel-accountant.partners.store') }}" id="vendorForm">
@csrf

<div class="gel-card p-4 mb-4">
    <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-building" style="color:var(--gel-primary);"></i> Informations générales</h3>
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Raison sociale *</label>
            <input type="text" name="company_name" class="doc-input" required placeholder="Ex: SARL Approvisionnement Pro">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Nom du contact</label>
            <input type="text" name="last_name" class="doc-input" placeholder="Nom de famille">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Prénom du contact</label>
            <input type="text" name="first_name" class="doc-input" placeholder="Prénom">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Email</label>
            <input type="email" name="email" class="doc-input" placeholder="contact@fournisseur.com">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Téléphone</label>
            <input type="tel" name="phone" class="doc-input" placeholder="+241 XX XX XX XX">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Mobile</label>
            <input type="tel" name="mobile" class="doc-input" placeholder="+241 0X XX XX XX">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Site web</label>
            <input type="url" name="website" class="doc-input" placeholder="https://www.fournisseur.com">
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
    <div class="gel-card p-4 mb-4">
        <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-map-marker-alt" style="color:var(--gel-primary);"></i> Adresse</h3>
        <div class="doc-form-grid" style="grid-template-columns:1fr;">
            <div class="doc-form-group">
                <label class="doc-label">Adresse</label>
                <input type="text" name="address" class="doc-input" placeholder="Rue, quartier...">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="doc-form-group">
                    <label class="doc-label">Ville</label>
                    <input type="text" name="city" class="doc-input" placeholder="Libreville">
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Pays</label>
                    <select name="country" class="doc-input">
                        <option value="BJ" selected>Bénin</option>
                        <option value="GA">Gabon</option>
                        <option value="CM">Cameroun</option>
                        <option value="CG">Congo</option>
                        <option value="CI">Côte d'Ivoire</option>
                        <option value="SN">Sénégal</option>
                        <option value="FR">France</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>
            </div>
            <div class="doc-form-group">
                <label class="doc-label">Code Postal</label>
                <input type="text" name="postal_code" class="doc-input" placeholder="BP XXXX">
            </div>
        </div>
    </div>

    <div class="gel-card p-4 mb-4">
        <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-file-invoice" style="color:var(--gel-primary);"></i> Informations fiscales & bancaires</h3>
        <div class="doc-form-grid" style="grid-template-columns:1fr;">
            <div class="doc-form-group">
                <label class="doc-label">IFU (Identifiant Fiscal Unique)</label>
                <input type="text" name="tax_id" class="doc-input" placeholder="Numéro IFU">
            </div>
            <div class="doc-form-group">
                <label class="doc-label">RCCM</label>
                <input type="text" name="rccm" class="doc-input" placeholder="Numéro RCCM">
            </div>
            <div class="doc-form-group">
                <label class="doc-label">IBAN</label>
                <input type="text" name="iban" class="doc-input" placeholder="GA00 0000 0000 0000 0000 0000 000">
            </div>
            <div class="doc-form-group">
                <label class="doc-label">Code SWIFT</label>
                <input type="text" name="swift" class="doc-input" placeholder="BICIGABX">
            </div>
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-cog" style="color:var(--gel-primary);"></i> Conditions commerciales</h3>
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Devise par défaut</label>
            <select name="currency" class="doc-input">
                <option value="XAF" selected>FCFA (XAF)</option>
                <option value="EUR">Euro (EUR)</option>
                <option value="USD">Dollar (USD)</option>
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Délai de paiement (jours)</label>
            <input type="number" name="payment_term_days" class="doc-input" value="30" min="0">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Méthode de paiement préférée</label>
            <select name="payment_method" class="doc-input">
                <option value="virement">Virement bancaire</option>
                <option value="cheque">Chèque</option>
                <option value="especes">Espèces</option>
                <option value="mobile_money">Mobile Money</option>
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Limite de Crédit (FCFA)</label>
            <input type="number" name="credit_limit" class="doc-input" value="0" min="0">
        </div>
        <div class="doc-form-group" style="grid-column: span 2;">
            <label class="doc-label">Notes internes</label>
            <textarea name="notes" class="doc-input" rows="3" placeholder="Informations complémentaires sur ce fournisseur..."></textarea>
        </div>
    </div>
</div>

<input type="hidden" name="type" value="fournisseur">

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer le fournisseur</button>
</div>
</form>


@endsection

