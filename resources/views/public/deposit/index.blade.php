<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dépôt Sécurisé - {{ $client->company_name }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0D9488;
            --primary-hover: #0F766E;
            --bg: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --border: #E2E8F0;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0; padding: 0;
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
        }
        .container {
            width: 100%; max-width: 500px; padding: 20px;
        }
        .card {
            background: var(--card-bg); border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            padding: 40px; border: 1px solid var(--border); text-align: center;
        }
        .icon-header {
            width: 72px; height: 72px; background: #F0FDFA; color: var(--primary);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 32px; margin: 0 auto 24px;
        }
        h1 { margin: 0 0 8px; font-size: 20px; font-weight: 700; }
        p { margin: 0 0 32px; color: var(--text-muted); font-size: 14px; line-height: 1.5; }
        .drop-zone {
            border: 2px dashed var(--border); border-radius: 12px; padding: 40px 20px;
            background: #F8FAFC; cursor: pointer; transition: all 0.2s;
            margin-bottom: 24px; position: relative;
        }
        .drop-zone:hover, .drop-zone.dragover { border-color: var(--primary); background: #F0FDFA; }
        .drop-zone i { font-size: 32px; color: var(--text-muted); margin-bottom: 12px; display: block; }
        .drop-zone .dz-text { font-size: 14px; font-weight: 600; color: var(--text-main); }
        .drop-zone .dz-sub { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
        input[type="file"] {
            position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer;
        }
        .form-group { text-align: left; margin-bottom: 24px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-main); }
        textarea {
            width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;
            font-family: inherit; font-size: 14px; box-sizing: border-box; resize: none;
        }
        textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(13,148,136,0.1); }
        .btn {
            background: var(--primary); color: white; border: none; width: 100%; padding: 14px;
            border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.2s;
        }
        .btn:hover { background: var(--primary-hover); }
        .btn:disabled { background: #CBD5E1; cursor: not-allowed; }
        
        #file-name {
            display: none; background: #EEF2FF; border: 1px solid #C7D2FE; color: #4F46E5;
            padding: 12px; border-radius: 8px; margin-bottom: 24px; font-size: 13px; font-weight: 600;
            text-align: left;
        }
        #file-name i { margin-right: 8px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="icon-header">
            <i class="fas fa-cloud-upload-alt"></i>
        </div>
        <h1>Portail de Dépôt</h1>
        <p>Bienvenue <strong>{{ $client->company_name }}</strong>.<br>Veuillez glisser-déposer votre document ci-dessous.</p>

        @if($errors->any())
            <div style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; padding:12px; border-radius:8px; margin-bottom:24px; font-size:13px; text-align:left;">
                <ul style="margin:0; padding-left:20px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('public.deposit.upload', $token) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="drop-zone" id="drop-zone">
                <i class="fas fa-file-upload"></i>
                <div class="dz-text">Cliquez ou glissez un fichier ici</div>
                <div class="dz-sub">PDF, Image, Word, Excel (Max 10MB)</div>
                <input type="file" name="file" id="file-input" required accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
            </div>

            <div id="file-name"><i class="fas fa-paperclip"></i> <span id="file-name-text"></span></div>

            <div class="form-group">
                <label for="description">Message ou description (optionnel)</label>
                <textarea name="description" id="description" rows="3" placeholder="Ex: Voici le KBIS demandé..."></textarea>
            </div>

            <button type="submit" class="btn" id="submit-btn" disabled>Envoyer le document</button>
        </form>
    </div>
    <div style="text-align:center; margin-top:20px; color:#94A3B8; font-size:12px;">
        <i class="fas fa-lock"></i> Transfert sécurisé de bout en bout
    </div>
</div>

<script>
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('drop-zone');
    const fileNameDiv = document.getElementById('file-name');
    const fileNameText = document.getElementById('file-name-text');
    const submitBtn = document.getElementById('submit-btn');

    fileInput.addEventListener('change', function(e) {
        if(e.target.files.length > 0) {
            dropZone.style.display = 'none';
            fileNameDiv.style.display = 'block';
            fileNameText.textContent = e.target.files[0].name;
            submitBtn.disabled = false;
        }
    });

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false)
    });
    function preventDefaults (e) { e.preventDefault(); e.stopPropagation(); }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false)
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false)
    });

    function highlight(e) { dropZone.classList.add('dragover'); }
    function unhighlight(e) { dropZone.classList.remove('dragover'); }
</script>

</body>
</html>
