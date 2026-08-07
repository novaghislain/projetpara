@extends('layouts.gel-accountant')
@section('title', 'Créer un Workflow')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-project-diagram" style="color:var(--gel-primary); margin-right:8px;"></i> Nouveau Workflow d'Approbation</h1>
        <p class="gel-page-subtitle">Configurez une chaîne de validation automatisée pour les opérations sensibles.</p>
    </div>
    <a href="{{ route('gel-accountant.workflows.index') }}" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<form method="POST" action="{{ route('gel-accountant.workflows.store') }}">
    @csrf
    
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px;">
        <div>
            <div class="gel-card p-4 mb-4">
                <div class="invoice-section-title"><i class="fas fa-cog"></i> Configuration générale</div>
                
                <div class="gel-form-group">
                    <label>Nom du Workflow *</label>
                    <input type="text" name="nom" class="gel-form-control" placeholder="Ex: Validation paiements > 500k" required>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="gel-form-group">
                        <label>Événement déclencheur *</label>
                        <select name="type" class="gel-form-select" required>
                            <option value="payment_created">Création d'un paiement</option>
                            <option value="invoice_discount">Remise facture exceptionnelle</option>
                            <option value="expense_created">Saisie de note de frais</option>
                            <option value="custom">Autre (Personnalisé)</option>
                        </select>
                    </div>
                    <div class="gel-form-group">
                        <label>Client applicable</label>
                        <select name="client_id" class="gel-form-select">
                            <option value="">Tous les clients du cabinet</option>
                            @foreach(\App\Models\Client::all() as $client)
                                <option value="{{ $client->id }}">{{ $client->company_name ?? $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="gel-form-group">
                    <label>Condition de déclenchement (Optionnel)</label>
                    <input type="text" name="conditions" class="gel-form-control" placeholder='Ex: {"amount": {">": 500000}}'>
                    <small style="color:var(--gel-text-muted); font-size:11px;">Laissez vide pour déclencher sur toutes les actions du type sélectionné.</small>
                </div>
            </div>

            <div class="gel-card p-4 mb-4">
                <div class="invoice-section-title"><i class="fas fa-users"></i> Étapes de validation (Chaîne d'approbation)</div>
                
                <div id="stepsContainer">
                    <div class="step-item" style="display:flex; gap:12px; align-items:center; margin-bottom:12px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
                        <span style="font-weight:bold; color:var(--gel-primary); background:white; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,0.1);">1</span>
                        <select name="steps[]" class="gel-form-select" style="flex:1;" required>
                            <option value="">-- Sélectionner l'approbateur --</option>
                            <option value="manager">Manager de compte</option>
                            <option value="partner">Associé du cabinet</option>
                            <option value="client_admin">Administrateur du client</option>
                        </select>
                        <button type="button" class="gel-btn gel-btn-secondary" disabled style="opacity:0.5;"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                
                <button type="button" class="gel-btn gel-btn-secondary" onclick="addStep()" style="margin-top:8px; width:100%; border:1px dashed #cbd5e1; background:white; color:#64748b;">
                    <i class="fas fa-plus"></i> Ajouter une étape
                </button>
            </div>
        </div>

        <div>
            <div class="gel-card p-4 mb-4">
                <div class="invoice-section-title"><i class="fas fa-info-circle"></i> Paramètres avancés</div>
                
                <div class="gel-form-group">
                    <label>Fréquence / Type *</label>
                    <select name="frequence" class="gel-form-select" required>
                        <option value="immediate">Immédiate (Temps réel)</option>
                        <option value="quotidienne">Quotidienne (Batch)</option>
                    </select>
                </div>
                
                <div style="background:rgba(59,130,246,0.05); padding:12px; border-radius:8px; border:1px solid rgba(59,130,246,0.1); margin-top:16px;">
                    <h4 style="font-size:12px; font-weight:700; color:#1d4ed8; margin-bottom:6px;"><i class="fas fa-lightbulb"></i> Comment ça marche ?</h4>
                    <p style="font-size:11px; color:#475569; margin:0; line-height:1.5;">
                        Dès que l'événement déclencheur se produit et que la condition est remplie, une <strong>Demande d'Approbation</strong> sera créée.
                        L'action initiale restera bloquée ou en statut "Attente" jusqu'à ce que tous les approbateurs valident.
                    </p>
                </div>
            </div>

            <button type="submit" class="gel-btn gel-btn-primary" style="width:100%; padding:12px;">
                <i class="fas fa-save"></i> Enregistrer le Workflow
            </button>
        </div>
    </div>
</form>

<script>
let stepCount = 1;
function addStep() {
    stepCount++;
    const container = document.getElementById('stepsContainer');
    const div = document.createElement('div');
    div.className = 'step-item';
    div.style.cssText = 'display:flex; gap:12px; align-items:center; margin-bottom:12px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0; animation: fade-in 0.3s;';
    
    div.innerHTML = `
        <span style="font-weight:bold; color:var(--gel-primary); background:white; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,0.1);">${stepCount}</span>
        <select name="steps[]" class="gel-form-select" style="flex:1;" required>
            <option value="">-- Sélectionner l'approbateur --</option>
            <option value="manager">Manager de compte</option>
            <option value="partner">Associé du cabinet</option>
            <option value="client_admin">Administrateur du client</option>
        </select>
        <button type="button" class="gel-btn gel-btn-secondary" onclick="this.parentElement.remove(); renumberSteps();" style="color:#ef4444;"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(div);
}

function renumberSteps() {
    const items = document.querySelectorAll('.step-item span');
    stepCount = items.length;
    items.forEach((span, index) => {
        span.innerText = index + 1;
    });
}
</script>
<style>
@keyframes fade-in { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
