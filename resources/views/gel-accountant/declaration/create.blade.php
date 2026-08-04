@extends('layouts.gel-accountant')

@section('title', 'Déclaration Fiscale / Sociale')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-file-contract" style="color:var(--gel-primary); margin-right:8px;"></i> Déclaration Fiscale / Sociale</h1>
        <p class="gel-page-subtitle">Enregistrez et suivez les obligations déclaratives de votre entreprise ou de vos clients.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="declarationForm">
@csrf

<div style="display:grid; grid-template-columns: 1fr 340px; gap:20px; margin-bottom:20px;">
    {{-- Colonne gauche --}}
    <div>
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-info-circle" style="color:var(--gel-primary);"></i> Détails de la Déclaration</h3>
            <div class="doc-form-grid">
                <div class="doc-form-group">
                    <label class="doc-label">Type de Déclaration *</label>
                    <select name="type" class="doc-input" required>
                        <option value="">— Sélectionner —</option>
                        <option value="tva">Déclaration de TVA (Mensuelle)</option>
                        <option value="is">Impôt sur les Sociétés (IS)</option>
                        <option value="cnps">Cotisations Sociales (CNPS / Sécurité Sociale)</option>
                        <option value="cnamgs">Assurance Maladie (CNAMGS, etc.)</option>
                        <option value="patente">Patente / Licence</option>
                        <option value="autre">Autre Déclaration</option>
                    </select>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Période fiscale *</label>
                    <select name="period" class="doc-input" required>
                        <option value="{{ date('Y-m', strtotime('-1 month')) }}">Mois précédent ({{ date('m/Y', strtotime('-1 month')) }})</option>
                        <option value="{{ date('Y-m') }}" selected>Mois en cours ({{ date('m/Y') }})</option>
                        <option value="{{ date('Y') }}">Exercice annuel {{ date('Y') }}</option>
                    </select>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Date butoir *</label>
                    <input type="date" name="due_date" class="doc-input" value="{{ date('Y-m-15', strtotime('+1 month')) }}" required>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Montant À  payer (FCFA) *</label>
                    <input type="number" name="amount" class="doc-input" value="0" min="0" required>
                </div>
                <div class="doc-form-group" style="grid-column: span 2;">
                    <label class="doc-label">Administration bénéficiaire</label>
                    <input type="text" name="administration" class="doc-input" placeholder="Ex: Direction Générale des Impôts">
                </div>
            </div>
        </div>

        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);"><i class="fas fa-tasks" style="color:var(--gel-primary);"></i> Statut de traitement</h3>
            <div class="doc-form-grid">
                <div class="doc-form-group">
                    <label class="doc-label">Statut</label>
                    <select name="status" class="doc-input">
                        <option value="a_faire">À préparer</option>
                        <option value="en_cours">En cours de préparation</option>
                        <option value="declaree">Déclarée (en attente de paiement)</option>
                        <option value="payee">Payée et clôturée</option>
                    </select>
                </div>
                <div class="doc-form-group">
                    <label class="doc-label">Responsable (Interne)</label>
                    <select name="assigned_to" class="doc-input">
                        <option value="{{ auth()->id() }}">Moi-même</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Colonne droite --}}
    <div>
        <div class="gel-card p-4 mb-4">
            <label class="doc-label">Notes et Observations</label>
            <textarea name="notes" class="doc-input" rows="4" placeholder="Ex: Pénalités de retard À  prévoir, ou exonération exceptionnelle..."></textarea>
        </div>

        <div class="gel-card p-4 mb-4">
            <label class="doc-label">Preuve de Déclaration / paiement</label>
            <div style="border:2px dashed var(--gel-border); border-radius:8px; padding:30px; text-align:center; cursor:pointer;" onclick="document.getElementById('fileInput').click()">
                <i class="fas fa-file-pdf" style="font-size:28px; color:var(--gel-text-muted); margin-bottom:8px;"></i>
                <p style="font-size:13px; color:var(--gel-text-secondary);">Ajouter le reçu de l'administration</p>
                <input type="file" id="fileInput" name="attachment" style="display:none;" accept="image/*,.pdf">
            </div>
        </div>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer la Déclaration</button>
</div>
</form>


@endsection

