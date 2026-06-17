/* search.js — Recetify Lab */

(function () {

    /* ── CSRF token ── */
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    /* ── URLs inyectadas desde la vista via registerJs ── */
    /* toggleCollectionUrl, loginUrl, reportUrl vienen de $this->registerJs(...) */

    /* ── Toast ── */
    var toastEl    = document.getElementById('search-toast');
    var toastTimer = null;

    /* Crear el toast si no existe en el DOM */
    if (!toastEl) {
        toastEl = document.createElement('div');
        toastEl.id = 'search-toast';
        toastEl.style.cssText = [
            'position:fixed',
            'bottom:24px',
            'left:50%',
            'transform:translateX(-50%) translateY(10px)',
            'background:#2a2a2a',
            'color:#fff',
            'padding:9px 20px',
            'border-radius:24px',
            'font-size:13px',
            'font-weight:500',
            'opacity:0',
            'pointer-events:none',
            'transition:opacity .2s,transform .2s',
            'z-index:999',
            'white-space:nowrap',
        ].join(';');
        document.body.appendChild(toastEl);
    }

    function showToast(msg) {
        toastEl.textContent = msg;
        toastEl.style.opacity = '1';
        toastEl.style.transform = 'translateX(-50%) translateY(0)';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toastEl.style.opacity = '0';
            toastEl.style.transform = 'translateX(-50%) translateY(10px)';
        }, 2200);
    }

    /* ── SVG del bookmark ── */
    function bookmarkSVG(filled) {
        return (
            '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"' +
            ' fill="' + (filled ? 'currentColor' : 'none') + '"' +
            ' stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>'
        );
    }

    /* ── Delegación de eventos en el contenedor de resultados (GUARDAR) ── */
    document.addEventListener('click', function (e) {

        var btn = e.target.closest('.btn-save');
        if (!btn) return;

        var recipeId = btn.dataset.recipeId;
        var tipo     = btn.dataset.tipo     || 'guardado';
        var isSaved  = btn.dataset.saved    === '1';
        var action   = isSaved ? 'remove' : 'add';

        btn.disabled = true;

        fetch(toggleCollectionUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrfToken,
            },
            body: [
                '_csrf='     + encodeURIComponent(csrfToken),
                'recipe_id=' + encodeURIComponent(recipeId),
                'tipo='      + encodeURIComponent(tipo),
                'action='    + encodeURIComponent(action),
            ].join('&'),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {

            if (data.success) {

                if (action === 'add') {
                    btn.dataset.saved = '1';
                    btn.classList.add('btn-save--saved');
                    btn.innerHTML = bookmarkSVG(true) + '<span>Guardado</span>';
                    showToast('✓ Receta guardada');
                } else {
                    btn.dataset.saved = '0';
                    btn.classList.remove('btn-save--saved');
                    btn.innerHTML = bookmarkSVG(false) + '<span>Guardar</span>';
                    showToast('Receta eliminada de guardados');
                }

            } else {
                /* Si no está autenticado, redirigir al login */
                if (data.message && data.message.indexOf('sesión') !== -1) {
                    showToast('Debes iniciar sesión para guardar recetas');
                    setTimeout(function () {
                        window.location.href = loginUrl || '/index.php?r=site/login';
                    }, 1500);
                } else {
                    showToast(data.message || 'Error al guardar. Intenta de nuevo.');
                }
            }
        })
        .catch(function () {
            showToast('Error de conexión. Intenta de nuevo.');
        })
        .finally(function () {
            btn.disabled = false;
        });
    });


    /* ══════════════════════════════════════════════════════
       MENÚ DE OPCIONES (tres puntos): abrir / cerrar
       ══════════════════════════════════════════════════════ */
    document.addEventListener('click', function (e) {

        var optionsBtn = e.target.closest('.btn-options');

        /* Cerrar cualquier dropdown abierto que no sea el que se clickeó */
        document.querySelectorAll('.recipe-options.is-open').forEach(function (el) {
            if (!optionsBtn || el !== optionsBtn.closest('.recipe-options')) {
                el.classList.remove('is-open');
                var b = el.querySelector('.btn-options');
                if (b) b.setAttribute('aria-expanded', 'false');
            }
        });

        if (optionsBtn) {
            var wrapper = optionsBtn.closest('.recipe-options');
            var isOpen  = wrapper.classList.toggle('is-open');
            optionsBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
    });

    /* Cerrar dropdown con tecla Escape */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.recipe-options.is-open').forEach(function (el) {
                el.classList.remove('is-open');
                var b = el.querySelector('.btn-options');
                if (b) b.setAttribute('aria-expanded', 'false');
            });
        }
    });


    /* ══════════════════════════════════════════════════════
       MODAL DE REPORTE
       ══════════════════════════════════════════════════════ */
    var reportOverlay     = document.getElementById('report-modal-overlay');
    var reportForm        = document.getElementById('report-form');
    var reportRecipeInput = document.getElementById('report-recipe-id');
    var reportUserInput   = document.getElementById('report-user-id');
    var reportTargetLabel = document.getElementById('report-modal-target');
    var reportTitleEl     = document.getElementById('report-modal-title');
    var reportDescInput   = document.getElementById('report-descripcion');
    var submitBtn         = reportForm ? reportForm.querySelector('.btn-report-submit') : null;

    function openReportModal(type, payload) {
        if (!reportOverlay) return;

        /* Reset de inputs */
        reportRecipeInput.value = '';
        reportUserInput.value   = '';
        reportDescInput.value   = '';
        reportForm.querySelectorAll('input[name="motivo"]').forEach(function (r) {
            r.checked = false;
        });

        if (type === 'recipe') {
            reportRecipeInput.value = payload.recipeId;
            reportTitleEl.textContent = 'Reportar receta';
            reportTargetLabel.textContent = '"' + payload.title + '" — cuéntanos qué está mal';
            reportForm.classList.remove('report-form--user');
            reportForm.classList.add('report-form--recipe');
        } else {
            reportUserInput.value = payload.userId;
            reportTitleEl.textContent = 'Reportar usuario';
            reportTargetLabel.textContent = '@' + payload.username + ' — cuéntanos qué está pasando';
            reportForm.classList.remove('report-form--recipe');
            reportForm.classList.add('report-form--user');
        }

        reportOverlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeReportModal() {
        if (!reportOverlay) return;
        reportOverlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    /* Abrir modal: reportar receta */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-report-recipe');
        if (!btn) return;

        var wrapper = btn.closest('.recipe-options');
        if (wrapper) wrapper.classList.remove('is-open');

        if (!csrfToken) {
            showToast('Debes iniciar sesión para reportar');
            return;
        }

        openReportModal('recipe', {
            recipeId: btn.dataset.recipeId,
            title:    btn.dataset.recipeTitle || 'esta receta',
        });
    });

    /* Abrir modal: reportar usuario */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-report-user');
        if (!btn) return;

        var wrapper = btn.closest('.recipe-options');
        if (wrapper) wrapper.classList.remove('is-open');

        if (!csrfToken) {
            showToast('Debes iniciar sesión para reportar');
            return;
        }

        openReportModal('user', {
            userId:   btn.dataset.userId,
            username: btn.dataset.username || 'usuario',
        });
    });

    /* Cerrar modal: botón X, botón cancelar, click fuera */
    document.addEventListener('click', function (e) {
        if (e.target.closest('.report-modal-close') || e.target.closest('.btn-report-cancel')) {
            closeReportModal();
            return;
        }
        if (e.target === reportOverlay) {
            closeReportModal();
        }
    });

    /* Cerrar modal con Escape */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && reportOverlay && reportOverlay.classList.contains('is-open')) {
            closeReportModal();
        }
    });

    /* Envío del formulario de reporte */
    if (reportForm) {
        reportForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var motivoInput = reportForm.querySelector('input[name="motivo"]:checked');
            if (!motivoInput) {
                showToast('Selecciona un motivo para continuar');
                return;
            }

            var body = [
                '_csrf='       + encodeURIComponent(csrfToken),
                'recipe_id='   + encodeURIComponent(reportRecipeInput.value || ''),
                'user_id='     + encodeURIComponent(reportUserInput.value || ''),
                'motivo='      + encodeURIComponent(motivoInput.value),
                'descripcion=' + encodeURIComponent(reportDescInput.value || ''),
            ].join('&');

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Enviando...';
            }

            fetch(reportUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': csrfToken,
                },
                body: body,
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    showToast('✓ Reporte enviado. Gracias por avisarnos');
                    closeReportModal();
                } else {
                    if (data.message && data.message.indexOf('autenticado') !== -1) {
                        showToast('Debes iniciar sesión para reportar');
                        setTimeout(function () {
                            window.location.href = loginUrl || '/index.php?r=site/login';
                        }, 1500);
                    } else {
                        showToast(data.message || 'No se pudo enviar el reporte');
                    }
                }
            })
            .catch(function () {
                showToast('Error de conexión. Intenta de nuevo.');
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enviar reporte';
                }
            });
        });
    }

})();