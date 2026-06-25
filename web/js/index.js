document.addEventListener("DOMContentLoaded", () => {

    /* ══════════════════════════════════════════════
       DECLARAR TODAS LAS VARIABLES PRIMERO
    ══════════════════════════════════════════════ */
    const btnMenu      = document.getElementById('rl-menu-toggle');
    const popupMenu    = document.getElementById('rl-popup-menu');
    const notifToggle  = document.getElementById('rl-notif-toggle');
    const notifMenu    = document.getElementById('rl-notif-menu');
    const notifList    = document.getElementById('rl-notif-list');
    const notifModal   = document.getElementById('rl-notif-modal');
    const configToggle = document.getElementById('rl-config-toggle');
    const configMenu   = document.getElementById('rl-config-menu');
    const sidebarToggle = document.getElementById('rl-sidebar-toggle');
    const sidebar       = document.getElementById('rl-sidebar');
    const sidebarClose  = document.getElementById('rl-sidebar-close');
    const overlay       = document.getElementById('rl-sidebar-overlay');

    /* ══════════════════════════════════════════════
       SIDEBAR LATERAL
    ══════════════════════════════════════════════ */
    function openSidebar() {
        sidebar?.classList.remove('rl-sidebar-hidden');
        sidebar?.classList.add('rl-sidebar-visible');
        overlay?.classList.remove('rl-hidden');
    }

    function closeSidebar() {
        sidebar?.classList.remove('rl-sidebar-visible');
        sidebar?.classList.add('rl-sidebar-hidden');
        overlay?.classList.add('rl-hidden');
    }

    sidebarToggle?.addEventListener('click', openSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    /* ══════════════════════════════════════════════
       POPUP MENU (avatar)
    ══════════════════════════════════════════════ */
    btnMenu?.addEventListener('click', (e) => {
        e.stopPropagation();
        popupMenu?.classList.toggle('rl-hidden');
        notifMenu?.classList.add('rl-hidden');
        configMenu?.classList.add('rl-hidden');
    });

    /* ══════════════════════════════════════════════
       POPUP NOTIFICACIONES
    ══════════════════════════════════════════════ */
    notifToggle?.addEventListener('click', (e) => {
        e.stopPropagation();
        notifMenu?.classList.toggle('rl-hidden');
        popupMenu?.classList.add('rl-hidden');
        configMenu?.classList.add('rl-hidden');
    });

    // Marcar todas como leídas
    const markAllBtn = document.getElementById('rl-mark-all-read');
    markAllBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const baseUrl   = document.querySelector('meta[name="base-url"]')?.content ?? '';

        fetch(baseUrl + '/site/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrfToken,
            },
            body: '_csrf=' + encodeURIComponent(csrfToken),
            credentials: 'same-origin',
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.querySelector('.rl-notif-badge')?.remove();
                document.querySelectorAll('.rl-notif-item.unread').forEach(el => {
                    el.classList.remove('unread');
                    el.querySelector('.rl-notif-dot')?.remove();
                });
                markAllBtn.remove();
            }
        });
    });

    // Modal notificación completa
    const notifModalClose  = document.getElementById('rl-notif-modal-close');
    const notifModalTipo   = document.getElementById('rl-notif-modal-tipo');
    const notifModalAsunto = document.getElementById('rl-notif-modal-asunto');
    const notifModalFecha  = document.getElementById('rl-notif-modal-fecha');
    const notifModalCuerpo = document.getElementById('rl-notif-modal-cuerpo');

    function openNotifModal(item) {
        const id     = item.dataset.id;
        const asunto = item.dataset.asunto;
        const cuerpo = item.dataset.cuerpo;
        const tipo   = item.dataset.tipo;
        const fecha  = item.dataset.fecha;

        const tipoLabels = {
            notificacion: '<i class="fa-solid fa-circle-info"></i> Notificación',
            advertencia:  '<i class="fa-solid fa-triangle-exclamation"></i> Advertencia',
            kick:         '<i class="fa-solid fa-ban"></i> Suspensión',
        };

        if (notifModalTipo)   { notifModalTipo.innerHTML = tipoLabels[tipo] || tipo; notifModalTipo.className = 'rl-notif-modal-tipo ' + tipo; }
        if (notifModalAsunto) notifModalAsunto.textContent = asunto;
        if (notifModalFecha)  notifModalFecha.textContent  = fecha;
        if (notifModalCuerpo) notifModalCuerpo.textContent = cuerpo;

        notifModal?.classList.remove('rl-hidden');
        document.body.style.overflow = 'hidden';
        notifMenu?.classList.add('rl-hidden');

        if (item.classList.contains('unread')) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const baseUrl   = document.querySelector('meta[name="base-url"]')?.content ?? '';

            fetch(baseUrl + '/site/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': csrfToken,
                },
                body: '_csrf=' + encodeURIComponent(csrfToken) + '&message_id=' + id,
                credentials: 'same-origin',
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    item.classList.remove('unread');
                    item.querySelector('.rl-notif-dot')?.remove();
                    const badge = document.querySelector('.rl-notif-badge');
                    if (badge) {
                        const count = parseInt(badge.textContent) - 1;
                        count <= 0 ? badge.remove() : (badge.textContent = count > 99 ? '99+' : count);
                    }
                }
            });
        }
    }

    function closeNotifModal() {
        notifModal?.classList.add('rl-hidden');
        document.body.style.overflow = '';
    }

    notifList?.addEventListener('click', (e) => {
        const item = e.target.closest('.rl-notif-item');
        if (item) openNotifModal(item);
    });

    notifModalClose?.addEventListener('click', closeNotifModal);
    notifModal?.addEventListener('click', (e) => {
        if (e.target === notifModal) closeNotifModal();
    });

    /* ══════════════════════════════════════════════
       POPUP CONFIGURACIÓN
    ══════════════════════════════════════════════ */
    configToggle?.addEventListener('click', (e) => {
        e.stopPropagation();
        configMenu?.classList.toggle('rl-hidden');
        popupMenu?.classList.add('rl-hidden');
        notifMenu?.classList.add('rl-hidden');
    });

    // Tema oscuro
    const darkToggle = document.getElementById('rl-dark-toggle');
    if (darkToggle) {
        if (localStorage.getItem('rl-dark') === '1') {
            document.body.classList.add('rl-dark');
            darkToggle.checked = true;
        }
        darkToggle.addEventListener('change', () => {
            if (darkToggle.checked) {
                document.body.classList.add('rl-dark');
                localStorage.setItem('rl-dark', '1');
            } else {
                document.body.classList.remove('rl-dark');
                localStorage.setItem('rl-dark', '0');
            }
        });
    }

    /* ══════════════════════════════════════════════
       CERRAR AL HACER CLIC FUERA
    ══════════════════════════════════════════════ */
    document.addEventListener('click', (e) => {
        if (popupMenu && !popupMenu.contains(e.target) && e.target !== btnMenu) {
            popupMenu.classList.add('rl-hidden');
        }
        if (notifMenu && !notifMenu.contains(e.target) &&
            !document.getElementById('rl-notif-wrap')?.contains(e.target)) {
            notifMenu.classList.add('rl-hidden');
        }
        if (configMenu && !configMenu.contains(e.target) &&
            !document.getElementById('rl-config-wrap')?.contains(e.target)) {
            configMenu.classList.add('rl-hidden');
        }
    });

    /* ══════════════════════════════════════════════
       ESC cierra todo
    ══════════════════════════════════════════════ */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            popupMenu?.classList.add('rl-hidden');
            notifMenu?.classList.add('rl-hidden');
            configMenu?.classList.add('rl-hidden');
            closeNotifModal();
            closeSidebar();
        }
    });

});