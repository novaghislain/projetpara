@extends('layouts.gel-accountant')

@section('title', 'Extraction OCR (IA)')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-file-invoice" style="color:var(--gel-primary); margin-right:8px;"></i>
            Extraction de données OCR
        </h1>
        <p class="gel-page-subtitle">Uploadez vos factures ou reçus pour une extraction automatique des données (Montant, TVA, Fournisseur).</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="gel-card p-5 text-center">
            
            <div id="dropzone" style="border: 2px dashed var(--gel-primary); border-radius: 12px; padding: 40px; background: var(--gel-primary-light); cursor: pointer; transition: all 0.2s;">
                <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: var(--gel-primary); margin-bottom: 20px;"></i>
                <h3 style="font-size: 18px; font-weight: 600; color: var(--gel-text-primary); margin-bottom: 10px;">
                    Glissez-déposez vos documents ici
                </h3>
                <p style="color: var(--gel-text-secondary); margin-bottom: 20px;">
                    Formats acceptés : PDF, JPG, PNG (Max 5MB)
                </p>
                <form id="ocr-form" action="{{ route('gel-accountant.expenses.ocr-scan') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="receipt" id="file-input" style="display: none;" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="gel-btn gel-btn-primary" onclick="document.getElementById('file-input').click()">
                        Parcourir les fichiers
                    </button>
                </form>
            </div>
            
            <div id="loading-state" style="display: none; margin-top: 30px;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Analyse en cours...</span>
                </div>
                <h4 style="margin-top: 15px; font-weight: 600; color: var(--gel-text-primary);">Analyse IA en cours...</h4>
                <p style="color: var(--gel-text-secondary);">Veuillez patienter pendant l'extraction des données.</p>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('file-input');
        const form = document.getElementById('ocr-form');
        const dropzone = document.getElementById('dropzone');
        const loadingState = document.getElementById('loading-state');
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                dropzone.style.display = 'none';
                loadingState.style.display = 'block';
                form.submit();
            }
        });

        // Drag & Drop effects
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.style.backgroundColor = '#dbeafe'; // Lighter blue
        });

        dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropzone.style.backgroundColor = 'var(--gel-primary-light)';
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.style.backgroundColor = 'var(--gel-primary-light)';
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                dropzone.style.display = 'none';
                loadingState.style.display = 'block';
                form.submit();
            }
        });
    });
</script>

@endsection