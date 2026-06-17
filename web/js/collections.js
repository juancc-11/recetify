/* collections.js — Recetify Lab */

(function () {

    /* ── CSRF token desde el meta tag de Yii2 ── */
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    /* ── URL del endpoint — compatible con pretty URLs y sin ellos ── */
    var toggleUrl = (function () {
        var base = document.querySelector('meta[name="base-url"]');
        if (base) {
            return base.content.replace(/\/$/, '') + '/recetas/guardar';
        }
        // Fallback sin pretty URLs
        return 'index.php?r=recipe%2Ftoggle-collection';
    })();

    /* ── Toggle de categorías ── */
    var toggleCheckbox = document.getElementById('toggle-categories');
    var categoriesPanel = document.getElementById('categories-panel');

    if (toggleCheckbox && categoriesPanel) {
        toggleCheckbox.addEventListener('change', function () {
            categoriesPanel.style.display = this.checked ? 'flex' : 'none';
        });
    }

    /* ── Chips de categorías ── */
    document.querySelectorAll('.category-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.category-chip').forEach(function (c) {
                c.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    /* ── Toast helper ── */
    var toastTimer = null;

    function showToast(msg) {
        var toast = document.getElementById('save-toast');
        if (!toast) return;
        toast.textContent = msg;
        toast.classList.add('visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toast.classList.remove('visible');
        }, 2200);
    }

    /* ── Botones de guardar ── */
    document.querySelectorAll('.btn-guardar').forEach(function (btn) {
        btn.addEventListener('click', function () {

            var self     = this;
            var recipeId = self.dataset.recipeId;
            var isSaved  = self.dataset.saved === '1';
            var tipo     = self.dataset.tipo || 'guardado';
            var action   = isSaved ? 'remove' : 'add';

            self.disabled = true;

            fetch(toggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type':  'application/x-www-form-urlencoded',
                    'X-CSRF-Token':  csrfToken,
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
                        setSavedState(self, true);
                        showToast('✓ Receta guardada');
                    } else {
                        setSavedState(self, false);
                        showToast('Receta eliminada de guardados');

                        /* Animar y quitar la card si estamos en pestaña Guardados */
                        var activeTab = document.querySelector('.tab-active');
                        if (activeTab && activeTab.textContent.trim() === 'Guardados') {
                            var card = self.closest('.recipe-card');
                            if (card) {
                                card.style.transition = 'opacity 0.3s, transform 0.3s';
                                card.style.opacity    = '0';
                                card.style.transform  = 'scale(0.95)';
                                setTimeout(function () {
                                    card.remove();
                                    checkEmpty();
                                }, 320);
                            }
                        }
                    }
                } else {
                    showToast(data.message || 'Error al guardar. Intenta de nuevo.');
                }
            })
            .catch(function () {
                showToast('Error de conexión. Intenta de nuevo.');
            })
            .finally(function () {
                self.disabled = false;
            });
        });
    });

    /* ── Helpers ── */

    /* El botón es solo un icono (38x38px, definido en CSS).
       Por eso aquí solo se actualiza el SVG y los atributos,
       nunca se inyecta texto dentro del botón. */
    function setSavedState(btn, saved) {
        btn.dataset.saved = saved ? '1' : '0';
        btn.classList.toggle('saved', saved);

        var label = saved ? 'Quitar de guardados' : 'Guardar receta';
        btn.setAttribute('title', label);
        btn.setAttribute('aria-label', label);

        btn.innerHTML = bookmarkHTML(saved);
    }

    function bookmarkHTML(saved) {
        var fill = saved ? 'currentColor' : 'none';
        return (
            '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"' +
            ' fill="' + fill + '" stroke="currentColor" stroke-width="2"' +
            ' stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>'
        );
    }

    function checkEmpty() {
        var grid = document.querySelector('.recipes-grid');
        if (grid && grid.children.length === 0) {
            location.reload();
        }
    }

})();