document.addEventListener("DOMContentLoaded", () => {

    /* ══════════════════════════════════════════════
       VARIABLES
    ══════════════════════════════════════════════ */
    const btnMenu       = document.getElementById('rl-menu-toggle');
    const popupMenu     = document.getElementById('rl-popup-menu');
    const notifToggle   = document.getElementById('rl-notif-toggle');
    const notifMenu     = document.getElementById('rl-notif-menu');
    const notifList     = document.getElementById('rl-notif-list');
    const notifModal    = document.getElementById('rl-notif-modal');
    const configToggle  = document.getElementById('rl-config-toggle');
    const configMenu    = document.getElementById('rl-config-menu');
    const sidebarToggle = document.getElementById('rl-sidebar-toggle');
    const sidebar       = document.getElementById('rl-sidebar');
    const sidebarClose  = document.getElementById('rl-sidebar-close');
    const overlay       = document.getElementById('rl-sidebar-overlay');

    /* ══════════════════════════════════════════════
       SIDEBAR
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
       NOTIFICACIONES
    ══════════════════════════════════════════════ */
    notifToggle?.addEventListener('click', (e) => {
        e.stopPropagation();
        notifMenu?.classList.toggle('rl-hidden');
        popupMenu?.classList.add('rl-hidden');
        configMenu?.classList.add('rl-hidden');
    });

    const markAllBtn = document.getElementById('rl-mark-all-read');
    markAllBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const baseUrl   = document.querySelector('meta[name="base-url"]')?.content ?? '';
        fetch(baseUrl + '/site/mark-all-read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken },
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

    const notifModalClose  = document.getElementById('rl-notif-modal-close');
    const notifModalTipo   = document.getElementById('rl-notif-modal-tipo');
    const notifModalAsunto = document.getElementById('rl-notif-modal-asunto');
    const notifModalFecha  = document.getElementById('rl-notif-modal-fecha');
    const notifModalCuerpo = document.getElementById('rl-notif-modal-cuerpo');

    function openNotifModal(item) {
        const tipoLabels = {
            notificacion: '<i class="fa-solid fa-circle-info"></i> Notificación',
            advertencia:  '<i class="fa-solid fa-triangle-exclamation"></i> Advertencia',
            kick:         '<i class="fa-solid fa-ban"></i> Suspensión',
        };
        if (notifModalTipo)   { notifModalTipo.innerHTML = tipoLabels[item.dataset.tipo] || item.dataset.tipo; notifModalTipo.className = 'rl-notif-modal-tipo ' + item.dataset.tipo; }
        if (notifModalAsunto) notifModalAsunto.textContent = item.dataset.asunto;
        if (notifModalFecha)  notifModalFecha.textContent  = item.dataset.fecha;
        if (notifModalCuerpo) notifModalCuerpo.textContent = item.dataset.cuerpo;
        notifModal?.classList.remove('rl-hidden');
        document.body.style.overflow = 'hidden';
        notifMenu?.classList.add('rl-hidden');

        if (item.classList.contains('unread')) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const baseUrl   = document.querySelector('meta[name="base-url"]')?.content ?? '';
            fetch(baseUrl + '/site/mark-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken },
                body: '_csrf=' + encodeURIComponent(csrfToken) + '&message_id=' + item.dataset.id,
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
    notifModal?.addEventListener('click', (e) => { if (e.target === notifModal) closeNotifModal(); });

    /* ══════════════════════════════════════════════
       CONFIGURACIÓN
    ══════════════════════════════════════════════ */
    configToggle?.addEventListener('click', (e) => {
        e.stopPropagation();
        configMenu?.classList.toggle('rl-hidden');
        popupMenu?.classList.add('rl-hidden');
        notifMenu?.classList.add('rl-hidden');
    });

    const darkToggle = document.getElementById('rl-dark-toggle');
    if (darkToggle) {
        if (localStorage.getItem('rl-dark') === '1') { document.body.classList.add('rl-dark'); darkToggle.checked = true; }
        darkToggle.addEventListener('change', () => {
            document.body.classList.toggle('rl-dark', darkToggle.checked);
            localStorage.setItem('rl-dark', darkToggle.checked ? '1' : '0');
        });
    }

    /* ══════════════════════════════════════════════
       CERRAR AL CLIC FUERA / ESC
    ══════════════════════════════════════════════ */
    document.addEventListener('click', (e) => {
        if (popupMenu && !popupMenu.contains(e.target) && e.target !== btnMenu) popupMenu.classList.add('rl-hidden');
        if (notifMenu && !notifMenu.contains(e.target) && !document.getElementById('rl-notif-wrap')?.contains(e.target)) notifMenu.classList.add('rl-hidden');
        if (configMenu && !configMenu.contains(e.target) && !document.getElementById('rl-config-wrap')?.contains(e.target)) configMenu.classList.add('rl-hidden');
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            popupMenu?.classList.add('rl-hidden');
            notifMenu?.classList.add('rl-hidden');
            configMenu?.classList.add('rl-hidden');
            closeNotifModal();
            closeSidebar();
        }
    });

        /* ══════════════════════════════════════════════
       IDIOMA — Google Translate
       Ambos cambios usan reload para consistencia:
       - EN: escribe cookie googtrans y recarga (GT la lee al inicio)
       - ES: borra cookie googtrans y recarga (contenido original)
       Esto evita el problema de tener que recargar manualmente.
    ══════════════════════════════════════════════ */
    const LANG_KEY = 'rl_lang';
    const DOMAIN   = '.recetifylab.gzgroup.dev';
    const isLocal  = location.hostname === 'localhost' || location.hostname === '127.0.0.1';

    // Leer preferencia y marcar botón activo
    const savedLang = localStorage.getItem(LANG_KEY) || 'es';
    updateLangButtons(savedLang);

    // Si la página cargó con preferencia inglés pero sin cookie GT activa → poner cookie y recargar
    // Esto resuelve el caso de "vengo de recargar en español y quiero volver a inglés"
    if (savedLang === 'en' && !getGTCookie()) {
        setGTCookie('en');
        location.reload();
    }

    // Escuchar clics en los botones
    document.querySelectorAll('.rl-lang-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const lang    = btn.dataset.lang;
            const current = localStorage.getItem(LANG_KEY) || 'es';

            // No hacer nada si ya está en ese idioma
            if (lang === current) return;

            localStorage.setItem(LANG_KEY, lang);
            updateLangButtons(lang);

            if (lang === 'en') {
                // Poner cookie y recargar → GT traduce al arrancar
                setGTCookie('en');
                location.reload();
            } else {
                // Borrar cookie y recargar → página vuelve al español original
                deleteGTCookie();
                setTimeout(() => {
                    location.reload(true);
                }, 300);
            }
        });
    });

    function updateLangButtons(lang) {
        document.querySelectorAll('.rl-lang-btn').forEach(b => {
            b.classList.toggle('active', b.dataset.lang === lang);
        });
    }

    function getGTCookie() {
        const match = document.cookie.match('(^|;) ?googtrans=([^;]*)(;|$)');
        return match ? match[2] : null;
    }

    function setGTCookie(lang) {
        const value = '/es/' + lang;
        document.cookie = 'googtrans=' + value + '; path=/';
        if (!isLocal) {
            document.cookie = 'googtrans=' + value + '; path=/; domain=' + DOMAIN;
        }
    }

    function deleteGTCookie() {

    const domains = [
        '',
        location.hostname,
        '.' + location.hostname,
        '.recetifylab.gzgroup.dev',
        'recetifylab.gzgroup.dev'
    ];

    domains.forEach(domain => {

        const domainPart = domain ? '; domain=' + domain : '';

        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/' + domainPart;

        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Lax' + domainPart;

        document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; Secure' + domainPart;

    });

}

}); // fin DOMContentLoaded DOMContentLoaded// fin DOMContentLoaded
    
