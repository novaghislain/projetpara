<script>
    // ─── NESTED DROPDOWN MENU ───
    var nestedTimeout = null;
    var activeNested = null;

    function openNested(id, triggerEl) {
        if (nestedTimeout) { clearTimeout(nestedTimeout); nestedTimeout = null; }
        var menu = document.getElementById(id);
        if (!menu) return;

        // Close other open nested menus at the same level
        if (activeNested && activeNested !== id) {
            var prev = document.getElementById(activeNested);
            if (prev) prev.classList.remove('visible');
        }

        // Position the dropdown relative to the trigger element
        if (triggerEl) {
            var rect = triggerEl.getBoundingClientRect();
            menu.style.left = (rect.right) + 'px';
            menu.style.top = (rect.top - 8) + 'px';
        }

        menu.classList.add('visible');
        activeNested = id;
    }

    function closeNested(id) {
        var menu = document.getElementById(id);
        if (menu) {
            menu.classList.remove('visible');
            if (activeNested === id) activeNested = null;
        }
    }

    function closeNestedDelayed(id) {
        nestedTimeout = setTimeout(function() { closeNested(id); }, 200);
    }

    function toggleNested(id, triggerEl) {
        var menu = document.getElementById(id);
        if (!menu) return;
        if (menu.classList.contains('visible')) {
            closeNested(id);
        } else {
            openNested(id, triggerEl);
        }
    }

    function closeAllNested() {
        if (nestedTimeout) { clearTimeout(nestedTimeout); nestedTimeout = null; }
        document.querySelectorAll('.gel-nested-dropdown.visible').forEach(function(el) {
            el.classList.remove('visible');
        });
        activeNested = null;
    }

    // Close nested on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAllNested();
    });

    // Close nested on click outside sidebar
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.gel-sidebar')) {
            closeAllNested();
        }
    });

    // Prevent nested menus from closing when hovering them
    document.querySelectorAll('.gel-nested-dropdown').forEach(function(el) {
        el.addEventListener('mouseenter', function() {
            if (nestedTimeout) { clearTimeout(nestedTimeout); nestedTimeout = null; }
        });
    });

    // ─── TOAST ───
    function showToast(message, type) {
        type = type || 'success';
        var container = document.getElementById('gelToastContainer');
        if (!container) return;
        var toast = document.createElement('div');
        toast.className = 'gel-toast gel-toast-' + type;
        var icons = { success: 'check-circle', error: 'times-circle', warning: 'exclamation-triangle', info: 'info-circle' };
        toast.innerHTML = '<i class="fas fa-' + (icons[type] || 'info-circle') + '"></i> ' + message;
        container.appendChild(toast);
        setTimeout(function() {
            toast.style.opacity = '0';
            setTimeout(function() { toast.remove(); }, 300);
        }, 5000);
    }

    // ─── DROPDOWN TOGGLE (Topbar) ───
    function toggleDropdown(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var isOpen = el.classList.contains('show');
        document.querySelectorAll('.gel-dropdown-menu.show').forEach(function(d) { d.classList.remove('show'); });
        if (!isOpen) el.classList.add('show');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.gel-dropdown')) {
            document.querySelectorAll('.gel-dropdown-menu.show').forEach(function(d) { d.classList.remove('show'); });
        }
    });

    // ─── SEARCH DROPDOWN ───
    function openSearchDropdown() {
        var dd = document.getElementById('gelSearchDropdown');
        if (dd) dd.classList.add('show');
    }
    function closeSearchDropdown() {
        var dd = document.getElementById('gelSearchDropdown');
        if (dd) dd.classList.remove('show');
    }

    // ─── PANEL ───
    function openPanel(id) {
        var p = document.getElementById(id);
        var o = document.getElementById(id + 'Overlay');
        if (p) p.classList.add('open');
        if (o) o.classList.add('open');
    }
    function closePanel(id) {
        var p = document.getElementById(id);
        var o = document.getElementById(id + 'Overlay');
        if (p) p.classList.remove('open');
        if (o) o.classList.remove('open');
    }

    // ─── MODAL ───
    function openModal(id) { var m = document.getElementById(id); if(m) m.classList.add('open'); }
    function closeModal(id) { var m = document.getElementById(id); if(m) m.classList.remove('open'); }

    // ─── SIDEBAR ───
    function toggleSidebar() { document.getElementById('gelSidebar')?.classList.toggle('mobile-open'); }

    // ─── TABS ───
    function switchTab(group, tab) {
        document.querySelectorAll('[' + group + ']').forEach(function(el) { el.classList.remove('active'); });
        document.querySelectorAll('[' + group + '="' + tab + '"]').forEach(function(el) { el.classList.add('active'); });
    }

    // ─── CTRL+K SEARCH ───
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            var inp = document.getElementById('gelSearchInput');
            if (inp) { inp.focus(); inp.select(); }
        }
    });

    // ─── FORMAT CFA ───
    function formatCFA(n) {
        return 'CFA ' + Number(n).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    // ─── AJAX FILTER (for tables) ───
    function applyFilters(formId, tableId) {
        var form = document.getElementById(formId);
        if (!form) return;
        var params = new URLSearchParams(new FormData(form));
        var url = window.location.pathname + '?' + params.toString();
        window.location.href = url;
    }

    // ─── AUTO-CLOSE TOASTS from session ───
    document.addEventListener('DOMContentLoaded', function() {
        // If there's a Laravel session flash message
        var flash = document.getElementById('gel-flash-message');
        if (flash) {
            showToast(flash.dataset.message, flash.dataset.type || 'success');
        }
    });
</script>
