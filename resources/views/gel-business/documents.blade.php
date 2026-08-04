{{-- ============================================================
-- Vue  : gel-business/documents
-- Rôle : Page de gestion des documents comptables et administratifs
--         de l'entreprise (factures, relevés, Déclarations).
-- Appelée par : GelBusiness\DocumentController@index
-- Variables attendues : aucune (état statique pour le moment)
-- ============================================================ --}}
@php $currentSection = 'documents'; @endphp
@extends('layouts.gel-business')

@section('title', 'Documents - Mon Entreprise')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Documents</h1>
        <p class="gel-page-subtitle">Gérez vos documents comptables et administratifs</p>
    </div>
    <button class="gel-btn gel-btn-primary" onclick="openUploadPanel()">
        <i class="fas fa-upload"></i> Importer un document
    </button>
</div>

<div class="gel-card" style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
    <div class="gel-empty" style="text-align: center;">
        <div style="width:64px;height:64px;border-radius:50%;background:var(--gel-body-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i class="fas fa-folder-open" style="font-size:24px;color:var(--gel-text-muted);"></i>
        </div>
        <h3 style="font-size:16px;margin:0 0 8px;">Aucun document</h3>
        <p style="color:var(--gel-text-secondary);max-width:300px;margin:0 auto 16px;">Importez vos documents (factures, relevés, contrats) pour les retrouver facilement et les partager avec votre comptable.</p>
        <button class="gel-btn gel-btn-secondary" onclick="openUploadPanel()">Parcourir les fichiers</button>
    </div>
</div>

<template id="uploadPanelTemplate">
    <div style="padding: 24px; text-align: center; border: 2px dashed var(--gel-border); border-radius: var(--gel-radius-lg); background: var(--gel-body-bg); position: relative; cursor: pointer;">
        <input type="file" multiple style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;" onchange="document.getElementById('fileNameDisplay').innerText = this.files.length + ' fichier(s) sélectionné(s)'">
        <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: var(--gel-primary); margin-bottom: 12px;"></i>
        <div style="font-weight: 600; margin-bottom: 4px;" id="fileNameDisplay">Cliquez ou glissez des fichiers ici</div>
        <div style="font-size: 13px; color: var(--gel-text-secondary);">PDF, JPG, PNG (Max 5Mo)</div>
    </div>
</template>

<template id="uploadPanelFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="showToast('Téléchargement en cours...', 'info'); closePanel();">Confirmer l'import</button>
</template>

<script>
    function openUploadPanel() {
        var bodyHtml = document.getElementById('uploadPanelTemplate').innerHTML;
        var footerHtml = document.getElementById('uploadPanelFooter').innerHTML;
        openPanel('Importer des documents', bodyHtml, footerHtml);
    }
</script>
@endsection

