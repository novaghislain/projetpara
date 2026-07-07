<script>
    function showToast(message, type) {
        type = type || 'success';
        var container = document.getElementById('gelToastContainer');
        if (!container) return;
        var toast = document.createElement('div');
        toast.className = 'gel-toast gel-toast-' + type;
        var icons = { success: 'check-circle', error: 'times-circle', warning: 'exclamation-triangle' };
        toast.innerHTML = '<i class="fas fa-' + (icons[type] || 'info-circle') + '"></i> ' + message;
        container.appendChild(toast);
        setTimeout(function() { toast.style.opacity = '0'; setTimeout(function() { toast.remove(); }, 300); }, 5000);
    }
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
    function openPanel(id) { var p=document.getElementById(id); var o=document.getElementById(id+'Overlay'); if(p) p.classList.add('open'); if(o) o.classList.add('open'); }
    function closePanel(id) { var p=document.getElementById(id); var o=document.getElementById(id+'Overlay'); if(p) p.classList.remove('open'); if(o) o.classList.remove('open'); }
    function openModal(id) { var m=document.getElementById(id); if(m) m.classList.add('open'); }
    function closeModal(id) { var m=document.getElementById(id); if(m) m.classList.remove('open'); }
    function switchTab(group, tab) { document.querySelectorAll('['+group+']').forEach(function(e){e.classList.remove('active');}); document.querySelectorAll('['+group+'="'+tab+'"]').forEach(function(e){e.classList.add('active');}); }
    function formatCFA(n) { return 'CFA '+Number(n).toLocaleString('fr-FR',{minimumFractionDigits:0,maximumFractionDigits:0}); }

    // Business-specific: expand sidebar submenus
    document.querySelectorAll('.gel-sidebar-link.has-sub').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var sub = this.nextElementSibling;
            if (sub && sub.classList.contains('gel-sidebar-sub')) {
                sub.style.display = sub.style.display === 'none' ? 'block' : 'none';
            }
        });
    });
</script>
