@extends('layouts.gel-accountant')
@section('title', 'Créer un Magic Link')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-magic" style="color:var(--gel-primary); margin-right:8px;"></i> Nouvelle Demande de Document</h1>
        <p class="gel-page-subtitle">Un lien sécurisé sera généré et envoyé à votre client pour uploader ses pièces.</p>
    </div>
    <a href="{{ route('gel-accountant.magic-links.index') }}" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<form method="POST" action="{{ route('gel-accountant.magic-links.store') }}">
@csrf

<div style="display:grid; grid-template-columns:2fr 1fr; gap:20px;">
    <div>
        {{-- Infos principales --}}
        <div class="gel-card p-4 mb-4">
            <div class="invoice-section-title"><i class="fas fa-info-circle"></i> Informations de la demande</div>

            <div class="gel-form-group">
                <label>Client *</label>
                <select name="client_id" class="gel-form-select" required>
                    <option value="">— Sélectionner un client —</option>
                    @foreach(\App\Models\Client::all() as $client)
                        <option value="{{ $client->id }}">{{ $client->company_name ?? $client->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gel-form-group">
                <label>Titre de la demande *</label>
                <input type="text" name="title" class="gel-form-control" placeholder="Ex: Justificatifs dépenses Août 2025" required>
            </div>

            <div class="gel-form-group">
                <label>Description / Instructions pour le client</label>
                <textarea name="description" class="gel-form-control" rows="3" placeholder="Ex: Merci de nous envoyer vos factures de charges d'août..."></textarea>
            </div>

            <div class="gel-form-group">
                <label>Documents attendus (optionnel)</label>
                <div id="docsList" style="display:flex; flex-direction:column; gap:6px;">
                    <div style="display:flex; gap:8px;">
                        <input type="text" name="requested_documents[]" class="gel-form-control" placeholder="Ex: Facture SBEE Août 2025">
                        <button type="button" onclick="addDoc()" class="gel-btn gel-btn-secondary" style="white-space:nowrap;"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        {{-- Paramètres d'envoi --}}
        <div class="gel-card p-4 mb-4">
            <div class="invoice-section-title"><i class="fas fa-cog"></i> Paramètres d'envoi</div>

            <div class="gel-form-group">
                <label>Canal d'envoi *</label>
                <select name="channel" class="gel-form-select">
                    <option value="email">📧 Email</option>
                    <option value="sms">📱 SMS</option>
                    <option value="whatsapp">💬 WhatsApp</option>
                    <option value="all">🔔 Tous les canaux</option>
                </select>
            </div>

            <div class="gel-form-group">
                <label>Expiration (jours) *</label>
                <select name="expires_days" class="gel-form-select">
                    <option value="3">3 jours</option>
                    <option value="7" selected>7 jours</option>
                    <option value="14">14 jours</option>
                    <option value="30">30 jours</option>
                </select>
            </div>

            <div style="background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.2); border-radius:8px; padding:12px; font-size:12px; color:#1e40af;">
                <i class="fas fa-info-circle"></i> <strong>Mode développement :</strong> Le lien sera loggué dans <code>storage/logs/laravel.log</code> et affiché dans le flash message.
            </div>
        </div>

        <button type="submit" class="gel-btn gel-btn-primary" style="width:100%;">
            <i class="fas fa-paper-plane"></i> Générer le Magic Link
        </button>
    </div>
</div>
</form>

<script>
function addDoc() {
    const list = document.getElementById('docsList');
    const div = document.createElement('div');
    div.style.cssText = 'display:flex; gap:8px;';
    div.innerHTML = `<input type="text" name="requested_documents[]" class="gel-form-control" placeholder="Ex: Relevé bancaire...">
        <button type="button" onclick="this.parentElement.remove()" class="gel-btn gel-btn-secondary" style="white-space:nowrap;"><i class="fas fa-times"></i></button>`;
    list.appendChild(div);
}
</script>
@endsection
