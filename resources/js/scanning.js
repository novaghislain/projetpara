/**
 * GEL Cabinet — Scanner caméra (Section 3.1)
 * ───────────────────────────────────────────────────────────────────────────
 * Pipeline :
 *   1) Navette vidéo (navigator.mediaDevices.getUserMedia) — mobile & desktop
 *   2) Détection auto des bords + recadrage (jscanify), correcteur manuel,
 *      contraste/luminosité, option Noir & Blanc
 *   3) Multi-pages → assemblage PDF (jspdf, une image par page A4)
 *   4) POST /gel-secretary/documents/scan  avec les MÊMES métadonnées que
 *      l'upload classique + scan_image (dataURL) pour l'OCR Claude Vision.
 *
 * Règle non-négociable : l'IA propose, n'applique jamais un classement seule.
 * Le formulaire de validation reste 100% humain.
 */
import jscanify from 'jscanify/client';
import { jsPDF } from 'jspdf';

// OpenCV.js alimente jscanify (détection des bords). On le charge une seule
// fois, de façon asynchrone ; tant qu'il n'est pas prêt, on capture la frame
// brute (repli rectangulaire) — JAMAIS de résultat fictif.
let openCvReady = new Promise((resolve) => {
    if (typeof cv !== 'undefined' && cv.Mat) {
        resolve();
        return;
    }
    const s = document.createElement('script');
    s.src = 'https://docs.opencv.org/4.7.0/opencv.js';
    s.async = true;
    s.onload = () => {
        if (cv && cv.Mat) {
            resolve();
        } else if (cv && cv['onRuntimeInitialized']) {
            cv['onRuntimeInitialized'] = () => resolve();
        } else {
            resolve(); // pas de cv exploitable → repli frame brute
        }
    };
    s.onerror = () => resolve(); // repli frame brute
    document.head.appendChild(s);
});

async function waitOpenCv(timeoutMs = 15000) {
    return Promise.race([
        openCvReady,
        new Promise((r) => setTimeout(r, timeoutMs)),
    ]);
}

const CSS = `
#secScannerModal{display:none;position:fixed;inset:0;z-index:100000;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:16px}
#secScannerModal.open{display:flex}
.scan-box{width:min(760px,100%);background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25);max-height:92vh;display:flex;flex-direction:column;font-family:-apple-system,'Segoe UI',Roboto,sans-serif}
.scan-head{background:#1e293b;color:#fff;padding:12px 18px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0}
.scan-head h3{margin:0;font-size:15px;font-weight:700;color:#fff}
.scan-close{background:transparent;border:none;color:#94a3b8;font-size:24px;cursor:pointer;line-height:1}
.scan-body{padding:16px 18px;overflow-y:auto;flex:1}
.scan-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:16px}
@media(max-width:720px){.scan-grid{grid-template-columns:1fr}}
.scan-stage{background:#0f172a;border-radius:12px;overflow:hidden;position:relative;min-height:280px;display:flex;align-items:center;justify-content:center}
.scan-stage video,.scan-stage canvas{width:100%;height:auto;display:block;background:#0f172a}
.scan-stage .scan-placeholder{color:#64748b;text-align:center;padding:20px;font-size:13px}
.scan-stage .scan-placeholder i{font-size:44px;color:#475569;display:block;margin-bottom:10px}
.scan-toolbar{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.scan-btn{border:none;border-radius:8px;padding:9px 14px;font-size:12.5px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:background .15s}
.scan-btn.primary{background:#0d9488;color:#fff}.scan-btn.primary:hover{background:#0f766e}
.scan-btn.light{background:#f1f5f9;color:#334155}.scan-btn.light:hover{background:#e2e8f0}
.scan-btn.danger{background:#ef4444;color:#fff}.scan-btn.danger:hover{background:#dc2626}
.scan-btn:disabled{opacity:.5;cursor:not-allowed}
.scan-pages{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.scan-page-thumb{position:relative;width:64px;height:80px;border-radius:8px;overflow:hidden;border:2px solid #e2e8f0;cursor:pointer;flex-shrink:0;background:#f8fafc}
.scan-page-thumb.active{border-color:#0d9488}
.scan-page-thumb img{width:100%;height:100%;object-fit:cover}
.scan-page-thumb .scan-page-del{position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;width:18px;height:18px;border-radius:50%;border:none;font-size:11px;cursor:pointer;line-height:18px;padding:0}
.scan-page-thumb .scan-page-num{position:absolute;bottom:0;left:0;right:0;background:rgba(15,23,42,.7);color:#fff;font-size:9px;font-weight:700;text-align:center;padding:2px 0}
.scan-field label{display:block;font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;text-transform:uppercase;letter-spacing:.3px}
.scan-field input,.scan-field select{width:100%;box-sizing:border-box;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:12.5px}
.scan-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
.scan-opt{display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:8px 12px;cursor:pointer}
.scan-opt input{margin:0}
.scan-error{background:#FEF2F2;color:#991B1B;border:1px solid #FECACA;padding:10px 14px;border-radius:10px;font-size:12.5px;margin-top:10px;display:none}
.scan-success{background:#F0FDF4;color:#166534;border:1px solid #BBF7D0;padding:10px 14px;border-radius:10px;font-size:12.5px;margin-top:10px;display:none}
`;

const state = {
    stream: null,
    pages: [],           // dataURLs (JPEG, first page en tête)
    activePage: 0,
    currentCanvas: null, // canvas "should" validé (bords détectés)
    folderId: null,
    folderName: '',
    csrf: '',
    submitting: false,
};

let root = null;

function ensureDom() {
    root = document.getElementById('secScannerRoot');
    if (!root) return;
    if (document.getElementById('secScannerModal')) return;

    const style = document.createElement('style');
    style.textContent = CSS;
    document.head.appendChild(style);

    root.innerHTML = `
      <div id="secScannerModal">
        <div class="scan-box">
          <div class="scan-head">
            <h3><i class="fas fa-camera"></i> &nbsp;Scanner un document</h3>
            <button type="button" class="scan-close" onclick="SecScanner.close()">&times;</button>
          </div>
          <div class="scan-body">
            <div class="scan-grid">
              <div>
                <div class="scan-stage" id="scanStage">
                  <video id="scanVideo" autoplay playsinline muted></video>
                  <div class="scan-placeholder" id="scanPlaceholder">
                    <i class="fas fa-video"></i>
                    Camera disponible — cliquez sur « Démarrer ».<br>
                    Dans un mois clôturé, le scan est désactivé.
                  </div>
                </div>
                <div class="scan-toolbar">
                  <button type="button" class="scan-btn primary" id="btnStart"><i class="fas fa-play"></i> Démarrer</button>
                  <button type="button" class="scan-btn primary" id="btnCapture" style="display:none"><i class="fas fa-camera"></i> Capturer</button>
                  <button type="button" class="scan-btn light" id="btnRedo" style="display:none"><i class="fas fa-sync"></i> Recadrer</button>
                  <button type="button" class="scan-btn light" id="btnContrast" style="display:none"><i class="fas fa-adjust"></i> Contraste</button>
                  <button type="button" class="scan-btn light" id="btnBW" style="display:none"><i class="fas fa-circle"></i> N&amp;B</button>
                </div>
                <div class="scan-pages" id="scanPages"></div>
              </div>
              <div>
                <div class="scan-field">
                  <label>Dossier de destination</label>
                  <div style="position:relative">
                    <input type="text" id="scanFolderSearch" placeholder="Rechercher : Documents / Année / Mois / sous-dossier…" autocomplete="off">
                    <div id="scanFolderResults" style="position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #e2e8f0;border-radius:8px;max-height:180px;overflow-y:auto;z-index:60;display:none;box-shadow:0 10px 25px rgba(0,0,0,.12)"></div>
                  </div>
                </div>
                <div class="scan-meta">
                  <div class="scan-field">
                    <label>Catégorie</label>
                    <select id="scanCategory">
                      <option value="Divers">Divers</option>
                      <option value="Courrier">Courrier</option>
                      <option value="Facture">Facture</option>
                      <option value="Comptabilité">Comptabilité</option>
                      <option value="RH">RH</option>
                      <option value="Rapport">Rapport</option>
                    </select>
                  </div>
                  <div class="scan-field">
                    <label>Confidentialité</label>
                    <select id="scanPrivacy">
                      <option value="standard">Standard</option>
                      <option value="interne">Interne</option>
                      <option value="confidentiel">Confidentiel</option>
                    </select>
                  </div>
                </div>
                <div class="scan-meta">
                  <div class="scan-field"><label>Année</label><input type="number" id="scanYear"></div>
                  <div class="scan-field"><label>Mois</label><select id="scanMonth"></select></div>
                </div>
                <div class="scan-meta">
                  <div class="scan-field"><label>Date du document</label><input type="date" id="scanDocDate"></div>
                  <div class="scan-field"><label>Priorité</label>
                    <select id="scanPriority">
                      <option value="normale">Normale</option>
                      <option value="urgente">Urgente</option>
                    </select>
                  </div>
                </div>
                <label class="scan-opt" style="margin-top:12px;">
                  <input type="checkbox" id="scanUseIA" checked> Analyser avec l'IA (OCR → texte indexé + tags proposés)
                </label>
                <div class="scan-error" id="scanError"></div>
                <div class="scan-success" id="scanSuccess"></div>
                <div style="display:flex;gap:10px;margin-top:14px;">
                  <button type="button" class="scan-btn light" onclick="SecScanner.close()">Annuler</button>
                  <button type="button" class="scan-btn primary" id="btnSave" style="flex:1;justify-content:center" disabled>
                    <i class="fas fa-check"></i> Enregistrer &amp; classer le scan
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>`;

    bind();
}

function bind() {
    const now = new Date();
    document.getElementById('scanYear').value = now.getFullYear();
    const monthSel = document.getElementById('scanMonth');
    const months = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    months.forEach((m, i) => {
        const o = document.createElement('option');
        o.value = String(i + 1);
        o.textContent = m;
        monthSel.appendChild(o);
    });
    monthSel.value = String(now.getMonth() + 1);
    document.getElementById('scanDocDate').value = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-01`;

    document.getElementById('btnStart').addEventListener('click', startCamera);
    document.getElementById('btnCapture').addEventListener('click', capture);
    document.getElementById('btnRedo').addEventListener('click', redoCapture);
    document.getElementById('btnContrast').addEventListener('click', enhance);
    document.getElementById('btnBW').addEventListener('click', toBW);
    document.getElementById('btnSave').addEventListener('click', () => save());
    document.getElementById('scanFolderSearch').addEventListener('keyup', (e) => searchFolder(e.target.value));
    document.getElementById('scanFolderResults').addEventListener('click', (e) => {
        const li = e.target.closest('.scan-folder-item');
        if (li) selectFolder(li.dataset.id, li.dataset.path);
    });
}

/* ── Sélection de dossier (même endpoint que la Saisie rapide) ── */
let folderSearchTimeout = null;
function searchFolder(query) {
    const resultsDiv = document.getElementById('scanFolderResults');
    if (query.length < 2) { resultsDiv.style.display = 'none'; return; }
    clearTimeout(folderSearchTimeout);
    folderSearchTimeout = setTimeout(() => {
        fetch(`/gel-secretary/documents/search-folders?q=${encodeURIComponent(query)}`)
            .then((r) => r.json())
            .then((data) => {
                resultsDiv.innerHTML = '';
                if (!data.length) {
                    resultsDiv.innerHTML = '<div style="padding:10px;color:#64748b;font-size:12px">Aucun dossier trouvé</div>';
                    resultsDiv.style.display = 'block';
                    return;
                }
                data.forEach((f) => {
                    const row = document.createElement('div');
                    row.className = 'scan-folder-item';
                    row.dataset.id = f.id;
                    row.dataset.path = f.path;
                    row.style.cssText = 'padding:9px 12px;font-size:12.5px;color:#334155;cursor:pointer;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:8px';
                    row.innerHTML = `<i class="fas fa-folder" style="color:#cbd5e1"></i>${f.path}`;
                    row.onmouseover = () => { row.style.background = '#f8fafc'; };
                    row.onmouseout = () => { row.style.background = '#fff'; };
                    resultsDiv.appendChild(row);
                });
                resultsDiv.style.display = 'block';
            })
            .catch(() => { resultsDiv.style.display = 'none'; });
    }, 300);
}

function selectFolder(id, path) {
    state.folderId = id;
    state.folderName = path;
    document.getElementById('scanFolderSearch').value = path;
    document.getElementById('scanFolderResults').style.display = 'none';
    refreshSave();
}

/* ── Caméra ── */
async function startCamera() {
    const stage = document.getElementById('scanStage');
    const vid = document.getElementById('scanVideo');
    const placeholder = document.getElementById('scanPlaceholder');
    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('getUserMedia non supporté — utilisez un navigateur récent (HTTPS requis hors localhost).');
        }
        state.stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } },
            audio: false,
        });
        vid.srcObject = state.stream;
        await vid.play();
        vid.style.display = 'block';
        placeholder.style.display = 'none';
        document.getElementById('btnStart').style.display = 'none';
        document.getElementById('btnCapture').style.display = 'inline-flex';
        document.getElementById('redoTip')?.remove();
    } catch (e) {
        showError('Impossible de démarrer la caméra : ' + e.message);
    }
}

/* ── Capture + détection des bords (jscanify) — filet de sécurité :
      si OpenCV n'est pas prêt ou si aucun contour n'est détecté, on garde la
      frame brute (recadrage rectangulaire). JAMAIS de résultat fictif. ── */
async function capture() {
    const vid = document.getElementById('scanVideo');
    if (!vid || !vid.videoWidth) return;

    const frame = document.createElement('canvas');
    frame.width = vid.videoWidth;
    frame.height = vid.videoHeight;
    frame.getContext('2d').drawImage(vid, 0, 0);

    let out = null;
    try {
        await waitOpenCv(15000);
        if (typeof cv !== 'undefined' && cv.Mat) {
            const scanner = new jscanify();
            out = scanner.extractPaper(frame, 800, 1100);
        }
    } catch (e) {
        out = null;
    }
    state.currentCanvas = (out && out.width > 0) ? out : frame;
    renderCurrent();
    document.getElementById('btnRedo').style.display = 'inline-flex';
    document.getElementById('btnContrast').style.display = 'inline-flex';
    document.getElementById('btnBW').style.display = 'inline-flex';
}

function renderCurrent() {
    if (!state.currentCanvas) return;
    const stage = document.getElementById('scanStage');
    stage.querySelectorAll('canvas').forEach((c) => c.remove());
    state.currentCanvas.id = 'scanCanvas';
    stage.appendChild(state.currentCanvas);
}

function redoCapture() { capture(); }

/* ── Améliorations (canvas pur — aucune donnée inventée) ── */
function enhance() {
    const c = state.currentCanvas;
    if (!c) return;
    const ctx = c.getContext('2d');
    const img = ctx.getImageData(0, 0, c.width, c.height);
    const d = img.data;
    for (let i = 0; i < d.length; i += 4) {
        d[i] = clamp(1.35 * (d[i] - 128) + 128);
        d[i + 1] = clamp(1.35 * (d[i + 1] - 128) + 128);
        d[i + 2] = clamp(1.35 * (d[i + 2] - 128) + 128);
    }
    ctx.putImageData(img, 0, 0);
}

function toBW() {
    const c = state.currentCanvas;
    if (!c) return;
    const ctx = c.getContext('2d');
    const img = ctx.getImageData(0, 0, c.width, c.height);
    const d = img.data;
    for (let i = 0; i < d.length; i += 4) {
        const g = Math.round((d[i] * 0.3) + (d[i + 1] * 0.59) + (d[i + 2] * 0.11));
        d[i] = d[i + 1] = d[i + 2] = g;
    }
    ctx.putImageData(img, 0, 0);
}

function clamp(v) { return v < 0 ? 0 : v > 255 ? 255 : v; }

/* ── Pages ── */
function addPage() {
    if (!state.currentCanvas) return;
    state.pages.push(state.currentCanvas.toDataURL('image/jpeg', 0.82));
    state.activePage = 0; // première page = OCR
    renderPages();
    refreshSave();
    state.currentCanvas = null;
    document.getElementById('scanStage').innerHTML =
        '<div class="scan-placeholder" id="scanPlaceholder2"><i class="fas fa-check-circle" style="color:#0d9488"></i>Page ajoutée. Recadrez et « Capturer » pour la page suivante, ou enregistrez.</div>';
    document.getElementById('btnRedo').style.display = 'none';
    document.getElementById('btnContrast').style.display = 'none';
    document.getElementById('btnBW').style.display = 'none';
    const vid = document.getElementById('scanVideo');
    if (vid) vid.style.display = 'block';
}

function renderPages() {
    const container = document.getElementById('scanPages');
    container.innerHTML = '';
    state.pages.forEach((page, idx) => {
        const thumb = document.createElement('div');
        thumb.className = 'scan-page-thumb';
        const img = document.createElement('img');
        img.src = page;
        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'scan-page-del';
        del.innerHTML = '&times;';
        del.onclick = (e) => { e.stopPropagation(); state.pages.splice(idx, 1); renderPages(); refreshSave(); };
        const num = document.createElement('div');
        num.className = 'scan-page-num';
        num.textContent = 'P. ' + (idx + 1);
        thumb.appendChild(img);
        thumb.appendChild(del);
        thumb.appendChild(num);
        container.appendChild(thumb);
    });
    // Le bouton capture devient "Ajouter cette page"
    const btnCapture = document.getElementById('btnCapture');
    btnCapture.innerHTML = '<i class="fas fa-plus"></i> Ajouter & continuer';
    btnCapture.onclick = addPage;
}

/* ── Sauvegarde : PDF (jspdf) + scan_image + métadonnées → POST /scan ── */
async function save() {
    if (state.submitting) return;
    if (!state.pages.length) { showError('Capturez au moins une page.'); return; }
    if (!state.folderId) { showError('Choisissez le dossier de destination.'); return; }

    state.submitting = true;
    const btnSave = document.getElementById('btnSave');
    const originalLabel = btnSave.innerHTML;
    btnSave.disabled = true;
    btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement…';
    try {
        // PDF multi-page A4
        const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        state.pages.forEach((page, idx) => {
            if (idx > 0) pdf.addPage('a4', 'portrait');
            pdf.addImage(page, 'JPEG', 5, 5, 200, 287, undefined, 'FAST');
        });
        const pdfBlob = pdf.output('blob');

        const fd = new FormData();
        fd.append('file', pdfBlob, `scan_${Date.now()}.pdf`);
        fd.append('folder_id', state.folderId);
        fd.append('category', document.getElementById('scanCategory').value);
        fd.append('annee_liee', document.getElementById('scanYear').value);
        fd.append('mois_lie', document.getElementById('scanMonth').value);
        fd.append('document_date', document.getElementById('scanDocDate').value);
        fd.append('privacy_level', document.getElementById('scanPrivacy').value);
        fd.append('priority', document.getElementById('scanPriority').value);
        fd.append('scan_image', state.pages[0]);
        fd.append('use_ia', document.getElementById('scanUseIA').checked ? '1' : '0');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
            ?? document.querySelector('input[name="_token"]')?.value ?? '';
        const res = await fetch('/gel-secretary/documents/scan', {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });

        if (!res.ok) {
            let msg = 'Erreur serveur (' + res.status + ')';
            try {
                const j = await res.json();
                if (j.error) msg = j.error;
                if (j.errors) msg = Object.values(j.errors).flat().join(' ');
            } catch (e) {}
            throw new Error(msg);
        }

        const data = await res.json();
        document.getElementById('scanSuccess').style.display = 'block';
        document.getElementById('scanSuccess').textContent =
            'Document enregistré dans « ' + state.folderName + ' ». ' + (data.ocr_note || '');
        setTimeout(() => window.location.reload(), 1400);
    } catch (e) {
        showError(e.message || 'Échec de l’enregistrement.');
        state.submitting = false;
        btnSave.disabled = false;
        btnSave.innerHTML = originalLabel;
    }
}

function refreshSave() {
    const btn = document.getElementById('btnSave');
    if (btn) btn.disabled = !(state.pages.length > 0 && state.folderId);
}

function showError(msg) {
    const el = document.getElementById('scanError');
    if (el) { el.textContent = msg; el.style.display = 'block'; }
}

function stopCamera() {
    if (state.stream) {
        state.stream.getTracks().forEach((t) => t.stop());
        state.stream = null;
    }
}

const SecScanner = {
    open(folderId = null, folderName = '') {
        ensureDom();
        if (folderId) {
            state.folderId = folderId;
            state.folderName = folderName;
            document.getElementById('scanFolderSearch').value = folderName;
        }
        state.pages = [];
        state.activePage = 0;
        state.currentCanvas = null;
        state.submitting = false;
        document.getElementById('scanError').style.display = 'none';
        document.getElementById('scanSuccess').style.display = 'none';
        document.getElementById('scanPages').innerHTML = '';
        const btnCapture = document.getElementById('btnCapture');
        if (btnCapture) {
            btnCapture.style.display = 'none';
            btnCapture.innerHTML = '<i class="fas fa-camera"></i> Capturer';
            btnCapture.onclick = capture;
        }
        const btnStart = document.getElementById('btnStart');
        if (btnStart) btnStart.style.display = 'inline-flex';
        const vid = document.getElementById('scanVideo');
        if (vid) { vid.style.display = 'none'; }
        document.getElementById('scanPlaceholder').style.display = 'block';
        document.getElementById('scanModalClose')?.remove();
        document.getElementById('secScannerModal').classList.add('open');
        refreshSave();
        document.body.style.overflow = 'hidden';
    },

    close() {
        const modal = document.getElementById('secScannerModal');
        if (modal) modal.classList.remove('open');
        document.body.style.overflow = '';
        stopCamera();
    },
};

window.SecScanner = SecScanner;
export default SecScanner;