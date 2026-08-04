{{-- ═══════════════════════════════════════════════════════════════
     GEL Business — Scripts partagés
     Fonctions JS communes : toast, dropdown, panel coulissant, modal
════════════════════════════════════════════════════════════════ --}}

{{-- ─── Slide Panel (global, réutilisable) ─── --}}
<div class="gel-panel-overlay" id="gelPanelOverlay" onclick="closePanel()"></div>
<div class="gel-panel" id="gelPanel">
    <div class="gel-panel-header">
        <span class="gel-panel-title" id="gelPanelTitle">—</span>
        <button class="gel-panel-close" onclick="closePanel()"><i class="fas fa-times"></i></button>
    </div>
    <div class="gel-panel-body" id="gelPanelBody"></div>
    <div class="gel-panel-footer" id="gelPanelFooter"></div>
</div>

{{-- ─── Toast container ─── --}}
<div class="gel-toast-container" id="gelToastContainer"></div>

{{-- ─── Flash messages → auto-toast ─── --}}
@if(session('success'))
    <div style="display:none" id="gel-flash" data-msg="{{ session('success') }}" data-type="success"></div>
@elseif(session('error'))
    <div style="display:none" id="gel-flash" data-msg="{{ session('error') }}" data-type="error"></div>
@elseif(session('info'))
    <div style="display:none" id="gel-flash" data-msg="{{ session('info') }}" data-type="info"></div>
@elseif(session('warning'))
    <div style="display:none" id="gel-flash" data-msg="{{ session('warning') }}" data-type="warning"></div>
@endif

<script>
/* ═══════════════════════════════════════════════════════════════
   GEL Business — Core JS
════════════════════════════════════════════════════════════════ */

// ─── Toast ───────────────────────────────────────────────────
function showToast(message, type) {
    type = type || 'success';
    var container = document.getElementById('gelToastContainer');
    if (!container) return;
    var icons = { success: 'check-circle', error: 'times-circle', warning: 'exclamation-triangle', info: 'info-circle' };
    var colors = { success: 'var(--gel-success)', error: 'var(--gel-danger)', warning: 'var(--gel-warning)', info: 'var(--gel-info)' };
    var toast = document.createElement('div');
    toast.className = 'gel-toast gel-toast-' + type;
    toast.style.borderLeftColor = colors[type] || colors.info;
    toast.innerHTML = '<i class="fas fa-' + (icons[type] || 'info-circle') + '" style="color:' + (colors[type] || colors.info) + '"></i> ' + message;
    container.appendChild(toast);
    setTimeout(function () {
        toast.style.transition = 'opacity 0.3s ease';
        toast.style.opacity = '0';
        setTimeout(function () { toast.remove(); }, 300);
    }, 5000);
}

// ─── Auto-affichage du flash session ──────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var flash = document.getElementById('gel-flash');
    if (flash) showToast(flash.dataset.msg, flash.dataset.type);
});

// ─── Dropdown ────────────────────────────────────────────────
function toggleDropdown(id) {
    var el = document.getElementById(id);
    if (!el) return;
    var isOpen = el.classList.contains('show');
    document.querySelectorAll('.gel-dropdown-menu.show').forEach(function (d) { d.classList.remove('show'); });
    if (!isOpen) el.classList.add('show');
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.gel-dropdown')) {
        document.querySelectorAll('.gel-dropdown-menu.show').forEach(function (d) { d.classList.remove('show'); });
    }
});

// ─── Slide Panel ──────────────────────────────────────────────
/**
 * Ouvre le panel coulissant.
 * @param {string} title   Titre dans l'entête
 * @param {string} body    HTML du corps
 * @param {string} footer  HTML des boutons de pied (optionnel)
 */
function openPanel(title, body, footer) {
    document.getElementById('gelPanelTitle').textContent = title || '';
    document.getElementById('gelPanelBody').innerHTML = body || '';
    document.getElementById('gelPanelFooter').innerHTML = footer || '';
    document.getElementById('gelPanel').classList.add('open');
    document.getElementById('gelPanelOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closePanel() {
    document.getElementById('gelPanel').classList.remove('open');
    document.getElementById('gelPanelOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

// ─── Modal ────────────────────────────────────────────────────
function openModal(id) {
    var m = document.getElementById(id);
    if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
    var m = document.getElementById(id);
    if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
}

// ─── Tabs ─────────────────────────────────────────────────────
function switchTab(group, tab) {
    // Support data-tab-group / data-tab convention
    document.querySelectorAll('[data-tab-group="' + group + '"]').forEach(function (e) { e.classList.remove('active'); });
    document.querySelectorAll('[data-tab-group="' + group + '"][data-tab="' + tab + '"]').forEach(function (e) { e.classList.add('active'); });
    document.querySelectorAll('[data-panel-group="' + group + '"]').forEach(function (e) { e.classList.remove('active'); });
    document.querySelectorAll('[data-panel-group="' + group + '"][data-panel="' + tab + '"]').forEach(function (e) { e.classList.add('active'); });
    // Support inline attribute convention (e.g. bqTab, factureTab...)
    document.querySelectorAll('[' + group + ']').forEach(function (e) {
        e.classList.remove('active');
        if (e.getAttribute(group) === tab) e.classList.add('active');
    });
    // Also toggle anchor tabs (a.gel-tab) that have an onclick matching the group
    document.querySelectorAll('a.gel-tab').forEach(function (e) {
        var oc = e.getAttribute('onclick') || '';
        if (oc.includes("'" + group + "'") || oc.includes('"' + group + '"')) {
            e.classList.remove('active');
            if (oc.includes("'" + tab + "'") || oc.includes('"' + tab + '"')) e.classList.add('active');
        }
    });
}

// ─── Sidebar mobile toggle ────────────────────────────────────
function toggleSidebar() {
    var sidebar = document.getElementById('gelSidebar');
    if (sidebar) sidebar.classList.toggle('mobile-open');
}

// ─── Format CFA ──────────────────────────────────────────────
function formatCFA(n) {
    return 'CFA ' + Number(n).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

// ─── Bank Connect Panel ───────────────────────────────────────
function openBankConnectPanel() {
    openPanel(
        'Connecter une banque',
        '<div class="gel-form-group"><label>Nom de la banque</label><select class="gel-form-control"><option value="">Sélectionnez votre banque...</option><option>Bank of Africa (BOA)</option><option>Ecobank Bénin</option><option>SGBE</option><option>UBA Bénin</option><option>Coris Bank</option><option>Autre</option></select></div>' +
        '<div class="gel-form-group"><label>Type de connexion</label><select class="gel-form-control"><option>Import manuel (relevé PDF/CSV)</option><option>API bancaire (Open Banking)</option></select></div>' +
        '<div class="gel-form-group"><label>Importer un relevé</label><input type="file" class="gel-form-control" accept=".pdf,.csv,.xls,.xlsx"></div>' +
        '<p style="font-size:12px;color:var(--gel-text-muted);margin-top:8px;"><i class="fas fa-lock"></i> Connexion sécurisée — vos données restent confidentielles.</p>',
        '<button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>' +
        '<button class="gel-btn gel-btn-primary" onclick="showToast(\'Banque connectée avec succès\', \'success\'); closePanel();">Connecter</button>'
    );
}

// ─── Sidebar sub-menus ────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.gel-sidebar-link.has-sub').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var sub = this.nextElementSibling;
            if (sub && sub.classList.contains('gel-sidebar-sub')) {
                sub.style.display = sub.style.display === 'none' ? 'block' : 'none';
                var arrow = this.querySelector('.gel-sidebar-toggle');
                if (arrow) arrow.classList.toggle('open');
            }
        });
    });
});
</script>
