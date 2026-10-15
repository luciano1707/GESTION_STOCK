// js/script.js

// ===== THEME CLAIR / SOMBRE =====
function initTheme() {
    const saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    updateThemeIcon(saved);
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    updateThemeIcon(next);
}

function updateThemeIcon(theme) {
    const btn = document.getElementById('themeToggle');
    if (!btn) return;
    btn.innerHTML = theme === 'dark'
        ? '<i class="fa-solid fa-sun"></i>'
        : '<i class="fa-solid fa-moon"></i>';
    btn.title = theme === 'dark' ? 'Passer au mode clair' : 'Passer au mode sombre';
}

// ===== SIDEBAR COLLAPSE =====
function initSidebar() {
    const sidebar   = document.getElementById('sidebar');
    const wrapper   = document.getElementById('mainWrapper');
    const toggleBtn = document.getElementById('sidebarToggle');
    if (!sidebar) return;

    const collapsed = localStorage.getItem('sidebar') === 'collapsed';
    if (collapsed) {
        sidebar.classList.add('collapsed');
        wrapper && wrapper.classList.add('sidebar-collapsed');
    }

    toggleBtn && toggleBtn.addEventListener('click', () => {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        wrapper && wrapper.classList.toggle('sidebar-collapsed', isCollapsed);
        localStorage.setItem('sidebar', isCollapsed ? 'collapsed' : 'open');
    });
}

// ===== MOBILE SIDEBAR =====
function openMobileSidebar() {
    document.getElementById('sidebar')?.classList.add('mobile-open');
    document.getElementById('mobileOverlay')?.classList.add('active');
}
function closeMobileSidebar() {
    document.getElementById('sidebar')?.classList.remove('mobile-open');
    document.getElementById('mobileOverlay')?.classList.remove('active');
}

// ===== CONFIRMATIONS =====
document.addEventListener('click', function(e) {
    const el = e.target.closest('.confirm-action');
    if (!el) return;
    const msg = el.dataset.confirm || 'Êtes-vous sûr ?';
    if (!confirm(msg)) e.preventDefault();
});

// ===== ALERTES AUTO =====
function initAlerts() {
    document.querySelectorAll('.alert-auto').forEach(a => {
        setTimeout(() => {
            a.style.transition = 'opacity 0.5s, max-height 0.5s';
            a.style.opacity = '0';
            a.style.maxHeight = '0';
            a.style.padding = '0';
            a.style.marginBottom = '0';
            setTimeout(() => a.remove(), 500);
        }, 3500);
    });
}

// ===== INIT =====
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initSidebar();
    initAlerts();

    const themeBtn = document.getElementById('themeToggle');
    themeBtn?.addEventListener('click', toggleTheme);
});
