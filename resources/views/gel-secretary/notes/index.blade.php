@extends('layouts.gel-secretary')
@section('title', 'Éditeur de Texte & Notes')

@push('styles')
<!-- Quill JS (Theme Snow) -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<!-- html2pdf for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
/* ==========================================================================
   NOTES & EDITOR - BENTO GRID DESIGN
   ========================================================================== */
.editor-layout {
    display: flex;
    gap: 24px;
    height: calc(100vh - 120px);
}

.notes-sidebar {
    width: 300px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
}

.notes-sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #E2E8F0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #F8FAFC;
}

.notes-list {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
}

.note-item {
    padding: 14px 16px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 8px;
    border: 1px solid transparent;
}
.note-item:hover { background: #F8FAFC; }
.note-item.active {
    background: #F0FDFA;
    border-color: #99F6E4;
}

.note-title { font-size: 14px; font-weight: 700; color: #1E293B; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.note-date { font-size: 11px; color: #94A3B8; }

.btn-new-note {
    background: #0D9488;
    color: white;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-new-note:hover { background: #0F766E; transform: translateY(-2px); }

/* Editor Main Area */
.editor-main {
    flex: 1;
    background: white;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.editor-header {
    padding: 16px 24px;
    border-bottom: 1px solid #E2E8F0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #F8FAFC;
}

.editor-title-input {
    font-size: 20px;
    font-weight: 800;
    color: #1E293B;
    border: none;
    background: transparent;
    outline: none;
    width: 100%;
    font-family: inherit;
}

.editor-actions { display: flex; gap: 8px; flex-shrink: 0; }
.btn-action {
    background: white;
    border: 1px solid #E2E8F0;
    color: #475569;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.btn-action:hover { background: #F1F5F9; color: #0F172A; }
.btn-save { background: #1E293B; color: white; border-color: #1E293B; }
.btn-save:hover { background: #0F172A; color: white; }

.quill-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

#editor {
    flex: 1;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    color: #334155;
    background: white;
    overflow-y: auto;
}

/* Customize Quill Toolbar */
.ql-toolbar.ql-snow {
    border: none !important;
    border-bottom: 1px solid #E2E8F0 !important;
    background: #F8FAFC;
    padding: 12px 24px !important;
}
.ql-container.ql-snow {
    border: none !important;
}
.ql-editor {
    padding: 32px 40px !important;
}
</style>
@endpush

@section('content')

<div class="editor-layout">
    <!-- Sidebar -->
    <div class="notes-sidebar">
        <div class="notes-sidebar-header">
            <div style="font-size:16px; font-weight:800; color:#1E293B;">Mes Notes</div>
            <button class="btn-new-note" onclick="createNewNote()" title="Nouvelle note">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <div class="notes-list">
            @forelse($notes as $note)
                <div class="note-item {{ ($currentNote && $currentNote->id == $note->id) ? 'active' : '' }}" onclick="window.location.href='{{ route('gel-secretary.notes.index', ['note_id' => $note->id]) }}'">
                    <div class="note-title">{{ $note->titre ?? 'Nouvelle note' }}</div>
                    <div class="note-date">{{ $note->updated_at->format('d/m/Y H:i') }}</div>
                </div>
            @empty
                <div style="text-align:center; padding:40px 20px; color:#94A3B8;">
                    <i class="fas fa-sticky-note" style="font-size:32px; margin-bottom:12px; opacity:0.5;"></i>
                    <div style="font-size:13px;">Aucune note existante.<br>Créez-en une nouvelle.</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Main Editor -->
    <div class="editor-main">
        <div class="editor-header">
            <div style="flex:1; padding-right:20px;">
                <input type="text" id="noteTitle" class="editor-title-input" value="{{ $currentNote->titre ?? 'Nouvelle note' }}" placeholder="Titre du document...">
                <input type="hidden" id="noteId" value="{{ $currentNote->id ?? '' }}">
            </div>
            <div class="editor-actions">
                <button class="btn-action" onclick="exportToWord()"><i class="fas fa-file-word" style="color:#2563EB;"></i> Word</button>
                <button class="btn-action" onclick="exportToPdf()"><i class="fas fa-file-pdf" style="color:#EF4444;"></i> PDF</button>
                <button class="btn-action btn-save" id="btnSave" onclick="saveNote()"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </div>
        
        <div class="quill-container">
            <div id="editor">{!! $currentNote->contenu ?? '' !!}</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialisation de Quill
    var quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Saisissez vos notes ou votre compte-rendu ici...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                ['blockquote', 'code-block'],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });

    function createNewNote() {
        window.location.href = "{{ route('gel-secretary.notes.index') }}";
    }

    function saveNote() {
        const btn = document.getElementById('btnSave');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sauvegarde...';
        btn.disabled = true;

        const title = document.getElementById('noteTitle').value;
        const content = quill.root.innerHTML;
        const noteId = document.getElementById('noteId').value;

        fetch("{{ route('gel-secretary.notes.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                note_id: noteId,
                titre: title,
                contenu: content
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                if(window.secToast) window.secToast('Note sauvegardée avec succès !', 'success');
                if(!noteId && data.note.id) {
                    // Update ID in DOM to prevent duplicates
                    document.getElementById('noteId').value = data.note.id;
                    // Rewrite URL silently
                    window.history.pushState({}, '', '?note_id=' + data.note.id);
                }
            } else {
                if(window.secToast) window.secToast('Erreur lors de la sauvegarde.', 'err');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if(window.secToast) window.secToast('Erreur réseau.', 'err');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // Export HTML to PDF using html2pdf
    function exportToPdf() {
        const element = document.createElement('div');
        element.innerHTML = quill.root.innerHTML;
        // Styling for PDF
        element.style.padding = '30px';
        element.style.fontFamily = 'Arial, sans-serif';
        element.style.fontSize = '12pt';
        element.style.lineHeight = '1.5';
        
        const title = document.getElementById('noteTitle').value || 'Document';
        const filename = title.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.pdf';
        
        if(window.secToast) window.secToast('Génération du PDF en cours...', 'info');
        
        html2pdf().set({
            margin: 1,
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        }).from(element).save().then(() => {
            if(window.secToast) window.secToast('PDF téléchargé.', 'success');
        });
    }

    // Export HTML to Word (.doc)
    function exportToWord() {
        const title = document.getElementById('noteTitle').value || 'Document';
        const filename = title.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.doc';
        const content = quill.root.innerHTML;
        
        const preHtml = `<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
        <head><meta charset='utf-8'><title>${title}</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12pt; }
            h1 { font-size: 24pt; }
            h2 { font-size: 18pt; }
            p { margin-bottom: 10pt; line-height: 1.5; }
        </style>
        </head><body>`;
        const postHtml = "</body></html>";
        const html = preHtml + content + postHtml;

        const blob = new Blob(['\ufeff', html], {
            type: 'application/msword'
        });
        
        // Create download link
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        
        document.body.appendChild(link);
        if(navigator.msSaveOrOpenBlob ) navigator.msSaveOrOpenBlob( blob, filename); // IE10-11
        else link.click();  // other browsers
        
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        if(window.secToast) window.secToast('Fichier Word téléchargé.', 'success');
    }

    // Auto-save every 30 seconds if changes were made
    let lastSavedContent = quill.root.innerHTML;
    setInterval(() => {
        const currentContent = quill.root.innerHTML;
        const title = document.getElementById('noteTitle').value;
        if (currentContent !== lastSavedContent && title.trim() !== '') {
            saveNote();
            lastSavedContent = currentContent;
        }
    }, 30000);
</script>
@endpush
@endsection
