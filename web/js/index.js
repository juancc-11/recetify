document.addEventListener("DOMContentLoaded", () => {

    // ===== POPUP MENU (avatar) =====
    const btnMenu   = document.getElementById('rl-menu-toggle');
    const popupMenu = document.getElementById('rl-popup-menu');

    if (btnMenu && popupMenu) {
        btnMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            popupMenu.classList.toggle('rl-hidden');
        });

        document.addEventListener('click', (e) => {
            if (!popupMenu.contains(e.target) && e.target !== btnMenu) {
                popupMenu.classList.add('rl-hidden');
            }
        });
    }

    // ===== SIDEBAR LATERAL =====
    const sidebarToggle = document.getElementById('rl-sidebar-toggle');
    const sidebar       = document.getElementById('rl-sidebar');
    const sidebarClose  = document.getElementById('rl-sidebar-close');
    const overlay       = document.getElementById('rl-sidebar-overlay');

    function openSidebar() {
        sidebar.classList.remove('rl-sidebar-hidden');
        sidebar.classList.add('rl-sidebar-visible');
        overlay.classList.remove('rl-hidden');
    }

    function closeSidebar() {
        sidebar.classList.remove('rl-sidebar-visible');
        sidebar.classList.add('rl-sidebar-hidden');
        overlay.classList.add('rl-hidden');
    }

    if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebar);
    if (sidebarClose)  sidebarClose.addEventListener('click', closeSidebar);
    if (overlay)       overlay.addEventListener('click', closeSidebar);

});