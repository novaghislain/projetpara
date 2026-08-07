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
                <div id="ocrDropZone" style="border:2px dashed var(--gel-border); border-radius:8px; padding:30px; text-align:center; cursor:pointer;" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-magic" style="font-size:28px; color:var(--gel-primary); margin-bottom:8px;"></i>
                    <p style="font-size:13px; color:var(--gel-text-secondary); font-weight:600;">Scanner via IA (OCR)</p>
                    <p style="font-size:11px; color:var(--gel-text-muted);">Importez un reçu pour pré-remplir la note de frais</p>
                    <input type="file" id="fileInput" name="receipt" style="display:none;" accept="image/*,.pdf" onchange="handleOcrScan(this.files[0])">
                </div>
                <div id="ocrLoading" style="display:none; text-align:center; padding:20px;">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px; color:var(--gel-primary);"></i>
                    <p style="font-size:12px; margin-top:8px;">Analyse du reçu en cours...</p>
                </div>
                <div id="ocrResult" style="display:none; margin-top:10px; font-size:12px; color:var(--gel-success); font-weight:600; text-align:center;">
                    <i class="fas fa-check-circle"></i> Données extraites avec succès !
                </div>
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

@push('scripts')
<script>
function handleOcrScan(file) {
    if (!file) return;
    
    document.getElementById('ocrDropZone').style.display = 'none';
    document.getElementById('ocrLoading').style.display = 'block';
    document.getElementById('ocrResult').style.display = 'none';

    let formData = new FormData();
    formData.append('document', file);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route("gel-accountant.expenses.ocr-scan") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(res => {
        document.getElementById('ocrLoading').style.display = 'none';
        document.getElementById('ocrDropZone').style.display = 'block';
        
        if (res.success && res.data) {
            document.getElementById('ocrResult').style.display = 'block';
            
            // Pré-remplir les champs
            if (res.data.description) document.querySelector('textarea[name="description"]').value = res.data.description;
            if (res.data.amount_ht) {
                // Pour une note de frais, on met le montant TTC, simulons que le HT de l'OCR + TVA = TTC.
                let tvaRate = res.data.tva_rate ? res.data.tva_rate : 0;
                let ttc = Math.round(res.data.amount_ht * (1 + (tvaRate / 100)));
                document.querySelector('input[name="amount"]').value = ttc;
            }
            if (res.data.account) {
                // Mappage très basique
                let catSelect = document.querySelector('select[name="category"]');
                if(res.data.account.startsWith('625') || res.data.description.toLowerCase().includes('hôtel')) catSelect.value = 'hebergement';
                else if(res.data.account.startsWith('624') || res.data.description.toLowerCase().includes('transport')) catSelect.value = 'transport';
                else if(res.data.description.toLowerCase().includes('repas') || res.data.description.toLowerCase().includes('restaurant')) catSelect.value = 'repas';
                else catSelect.value = 'autre';
            }
        }
    })
    .catch(error => {
        document.getElementById('ocrLoading').style.display = 'none';
        document.getElementById('ocrDropZone').style.display = 'block';
        alert('Erreur lors de l\'analyse du document.');
    });
}
</script>
@endpush
