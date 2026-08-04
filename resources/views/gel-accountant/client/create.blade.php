@extends('layouts.gel-accountant')

@section('title', 'Ajouter un client')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-user-plus" style="color:var(--gel-primary); margin-right:8px;"></i> Ajouter un client</h1>
        <p class="gel-page-subtitle">Créez une nouvelle fiche client avec ses coordonnées, adresses et préférences de facturation.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="{{ route('gel-accountant.partners.store') }}" id="clientForm">
@csrf

<div class="gel-card p-4 mb-4">
    <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-building" style="color:var(--gel-primary);"></i> Informations générales</h3>
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Nom de l'entreprise (ou nom complet si particulier) *</label>
            <input type="text" name="company_name" class="doc-input" required placeholder="Ex: Entreprise ABCD / Jean Dupont">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Type de client</label>
            <select name="customer_type" class="doc-input">
                <option value="entreprise">Entreprise (B2B)</option>
                <option value="particulier">Particulier (B2C)</option>
            </select>
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Email principal</label>
            <input type="email" name="email" class="doc-input" placeholder="contact@client.com">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Téléphone fixe</label>
            <input type="tel" name="phone" class="doc-input" placeholder="+241 XX XX XX XX">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Mobile</label>
            <input type="tel" name="mobile" class="doc-input" placeholder="+241 0X XX XX XX">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Site web</label>
            <input type="url" name="website" class="doc-input" placeholder="https://www.client.com">
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
    <div class="gel-card p-4 mb-4">
        <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-map-marker-alt" style="color:var(--gel-primary);"></i> Adresse de facturation</h3>
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
        <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-file-invoice" style="color:var(--gel-primary);"></i> Informations fiscales & conditions</h3>
        <div class="doc-form-grid" style="grid-template-columns:1fr;">
            <div class="doc-form-group">
                <label class="doc-label">NIF / IFU</label>
                <input type="text" name="tax_id" class="doc-input" placeholder="Numéro d'Identification Fiscale">
            </div>
            <div class="doc-form-group">
                <label class="doc-label">Devise de facturation</label>
                <select name="currency" class="doc-input">
                    <option value="XAF" selected>FCFA (XAF)</option>
                    <option value="EUR">Euro (EUR)</option>
                    <option value="USD">Dollar (USD)</option>
                </select>
            </div>
            <div class="doc-form-group">
                <label class="doc-label">Délai de paiement par défaut</label>
                <select name="payment_term_days" class="doc-input">
                    <option value="0">Payable À  Réception</option>
                    <option value="15">Net 15 jours</option>
                    <option value="30" selected>Net 30 jours</option>
                    <option value="60">Net 60 jours</option>
                </select>
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
        </div>
    </div>
</div>

<div class="gel-card p-4 mb-4">
    <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-sticky-note" style="color:var(--gel-primary);"></i> Notes & Options supplémentaires</h3>
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Solde d'ouverture (FCFA)</label>
            <input type="number" name="opening_balance" class="doc-input" value="0" min="0">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Date du solde</label>
            <input type="date" name="opening_date" class="doc-input" value="{{ date('Y-m-d') }}">
        </div>
        <div class="doc-form-group" style="grid-column: span 2;">
            <label class="doc-label">Notes internes</label>
            <textarea name="notes" class="doc-input" rows="3" placeholder="Informations complémentaires sur ce client..."></textarea>
        </div>
    </div>
</div>

<input type="hidden" name="type" value="client">

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer le client</button>
</div>
</form>


@endsection

