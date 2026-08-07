<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $link->title }} — GEL Cabinet</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0f172a, #1e3a5f); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 600px; width: 100%; padding: 40px; }
        .logo { text-align:center; margin-bottom:24px; }
        .logo-text { font-size:28px; font-weight:800; color:#0f172a; }
        .logo-sub { font-size:13px; color:#64748b; margin-top:4px; }
        h1 { font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .desc { color: #64748b; font-size: 14px; margin-bottom: 24px; line-height: 1.6; }
        .docs-list { background: #f8fafc; border-radius: 10px; padding: 16px; margin-bottom: 24px; }
        .docs-list h3 { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151; margin-bottom: 6px; }
        .doc-item i { color: #3b82f6; }
        .upload-area { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.2s; margin-bottom: 16px; }
        .upload-area:hover { border-color: #3b82f6; background: #eff6ff; }
        .upload-area i { font-size: 2rem; color: #94a3b8; margin-bottom: 8px; display: block; }
        .upload-area p { color: #64748b; font-size: 14px; }
        .file-list { margin-bottom: 16px; }
        .file-item { display:flex; align-items:center; gap:8px; background:#f1f5f9; border-radius:6px; padding:8px 12px; margin-bottom:6px; font-size:13px; }
        .file-item i { color:#10b981; }
        textarea { width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 14px; font-size:13px; resize:vertical; outline:none; font-family:inherit; }
        textarea:focus { border-color:#3b82f6; }
        .btn-submit { width: 100%; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border: none; border-radius: 10px; padding: 14px; font-size: 15px; font-weight: 700; cursor: pointer; transition: transform 0.1s; }
        .btn-submit:hover { transform: translateY(-1px); }
        .success-box { background: rgba(16,185,129,0.1); border: 1px solid #10b981; border-radius: 10px; padding: 16px; text-align:center; color: #065f46; }
        .expiry { display:flex; align-items:center; gap:6px; font-size:12px; color:#94a3b8; margin-bottom:20px; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <div class="logo-text"><i class="fas fa-calculator" style="color:#3b82f6;"></i> GEL Cabinet</div>
        <div class="logo-sub">Demande de Documents Sécurisée</div>
    </div>

    @if(session('success'))
    <div class="success-box">
        <i class="fas fa-check-circle" style="font-size:2rem; margin-bottom:10px; display:block;"></i>
        <strong>Documents envoyés !</strong><br>{{ session('success') }}
    </div>
    @else

    <h1><i class="fas fa-file-upload" style="color:#3b82f6;"></i> {{ $link->title }}</h1>
    <div class="expiry"><i class="fas fa-clock"></i> Expire le {{ $link->expires_at?->format('d/m/Y à H:i') ?? '—' }}</div>

    @if($link->description)
    <p class="desc">{{ $link->description }}</p>
    @endif

    @if(!empty($link->requested_documents))
    <div class="docs-list">
        <h3><i class="fas fa-list"></i> Documents attendus</h3>
        @foreach($link->requested_documents as $doc)
        <div class="doc-item"><i class="fas fa-file-alt"></i> {{ $doc }}</div>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('magic-link.upload', $link->token) }}" enctype="multipart/form-data" id="uploadForm">
        @csrf

        <div class="upload-area" onclick="document.getElementById('fileInput').click()">
            <i class="fas fa-cloud-upload-alt"></i>
            <p><strong>Cliquez pour sélectionner vos fichiers</strong></p>
            <p style="font-size:12px; margin-top:4px;">PDF, images, Excel, Word — max 10 Mo par fichier</p>
        </div>
        <input type="file" id="fileInput" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.xlsx,.xls,.doc,.docx" style="display:none;" onchange="showFiles(this)">
        <div class="file-list" id="fileList"></div>

        <div style="margin-bottom:16px;">
            <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Message (optionnel)</label>
            <textarea name="message" rows="3" placeholder="Ajoutez une note pour le cabinet..."></textarea>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn" disabled>
            <i class="fas fa-paper-plane"></i> Envoyer mes documents
        </button>
    </form>

    @endif
</div>

<script>
function showFiles(input) {
    const list = document.getElementById('fileList');
    const btn = document.getElementById('submitBtn');
    list.innerHTML = '';
    if (input.files.length > 0) {
        Array.from(input.files).forEach(f => {
            const div = document.createElement('div');
            div.className = 'file-item';
            div.innerHTML = `<i class="fas fa-check-circle"></i> ${f.name} <span style="color:#94a3b8; font-size:11px;">(${(f.size/1024/1024).toFixed(1)} Mo)</span>`;
            list.appendChild(div);
        });
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
}
</script>
</body>
</html>
