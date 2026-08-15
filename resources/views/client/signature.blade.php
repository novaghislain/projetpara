<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Signature Électronique - GEL Cabinet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        body { background: #f8fafc; font-family: 'Inter', sans-serif; }
        .signature-container { max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        canvas { border: 2px dashed #cbd5e1; border-radius: 8px; width: 100%; height: 200px; cursor: crosshair; }
        .brand { color: #FF7900; font-weight: 800; font-size: 24px; text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="signature-container">
        <div class="brand">GEL Cabinet</div>
        <h4 class="text-center mb-3">Signature Électronique</h4>
        <p class="text-muted text-center text-sm mb-4">Veuillez signer dans le cadre ci-dessous pour valider le document <strong>{{ $signature->document->name ?? 'Document' }}</strong>.</p>

        <div class="mb-3">
            <label class="form-label fw-bold">Nom et Prénom du Signataire</label>
            <input type="text" id="signerName" class="form-control" placeholder="Jean Dupont">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Votre Signature</label>
            <canvas id="signatureCanvas"></canvas>
            <div class="text-end mt-2">
                <button type="button" class="btn btn-sm btn-light" id="clearBtn">Effacer</button>
            </div>
        </div>

        <button id="submitBtn" class="btn btn-primary w-100 py-2 fw-bold" style="background:#FF7900;border:none;">Confirmer la signature</button>
    </div>
</div>

<script>
    const canvas = document.getElementById('signatureCanvas');
    // Resize canvas
    function resizeCanvas() {
        const ratio =  Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
    }
    window.onresize = resizeCanvas;
    resizeCanvas();

    const signaturePad = new SignaturePad(canvas);

    document.getElementById('clearBtn').addEventListener('click', () => {
        signaturePad.clear();
    });

    document.getElementById('submitBtn').addEventListener('click', function() {
        if (signaturePad.isEmpty()) {
            alert('Veuillez fournir une signature.');
            return;
        }

        const signerName = document.getElementById('signerName').value;
        if (!signerName) {
            alert('Veuillez renseigner votre nom.');
            return;
        }

        const dataUrl = signaturePad.toDataURL();
        this.innerHTML = "Traitement...";
        this.disabled = true;

        fetch('{{ route("signature.process", $signature->token) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                signature_data: dataUrl,
                signer_name: signerName
            })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message || 'Signature validée avec succès !');
            window.location.href = '/';
        })
        .catch(err => {
            alert("Erreur lors de la signature.");
            this.innerHTML = "Confirmer la signature";
            this.disabled = false;
        });
    });
</script>
</body>
</html>
