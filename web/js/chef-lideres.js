/**
 * chef-lideres.js
 * Lógica del carrusel para la página Chef Líderes.
 * Soporta:
 *  - Botones prev/next
 *  - Click en dots
 *  - Swipe táctil (móvil)
 * Cada carrusel es independiente (uno por sección).
 */

(function () {
    'use strict';

    /** Estado de cada carrusel: id -> { currentIndex, total } */
    var carousels = {};

    /**
     * Inicializa todos los carruseles presentes en la página.
     */
    function init() {
        document.querySelectorAll('.cl-carousel').forEach(function (carousel) {
            var id    = carousel.id;
            var track = carousel.querySelector('.cl-carousel-track');
            var cards = track ? track.querySelectorAll('.cl-card') : [];

            if (!id || cards.length <= 1) return; // nada que hacer

            carousels[id] = { currentIndex: 0, total: cards.length };

            // Prev button
            var btnPrev = carousel.querySelector('.cl-carousel-prev');
            if (btnPrev) {
                btnPrev.addEventListener('click', function () {
                    move(id, -1);
                });
            }

            // Next button
            var btnNext = carousel.querySelector('.cl-carousel-next');
            if (btnNext) {
                btnNext.addEventListener('click', function () {
                    move(id, 1);
                });
            }

            // Dots
            carousel.querySelectorAll('.cl-dot').forEach(function (dot) {
                dot.addEventListener('click', function () {
                    var idx = parseInt(dot.getAttribute('data-index'), 10);
                    goTo(id, idx);
                });
            });

            // Swipe táctil
            attachSwipe(carousel, id);
        });
    }

    /**
     * Avanza o retrocede el carrusel.
     * @param {string} id
     * @param {number} delta  +1 o -1
     */
    function move(id, delta) {
        var state = carousels[id];
        if (!state) return;
        var next = (state.currentIndex + delta + state.total) % state.total;
        goTo(id, next);
    }

    /**
     * Va directamente a un índice.
     */
    function goTo(id, index) {
        var state = carousels[id];
        if (!state) return;

        state.currentIndex = index;

        // Mover el track
        var carousel = document.getElementById(id);
        if (!carousel) return;

        var track = carousel.querySelector('.cl-carousel-track');
        if (track) {
            track.style.transform = 'translateX(-' + (index * 100) + '%)';
        }

        // Actualizar dots
        carousel.querySelectorAll('.cl-dot').forEach(function (dot) {
            var i = parseInt(dot.getAttribute('data-index'), 10);
            dot.classList.toggle('cl-dot--active', i === index);
        });
    }

    /**
     * Agrega detección de swipe táctil.
     */
    function attachSwipe(element, id) {
        var startX = null;
        var threshold = 40; // px mínimos para considerar swipe

        element.addEventListener('touchstart', function (e) {
            startX = e.touches[0].clientX;
        }, { passive: true });

        element.addEventListener('touchend', function (e) {
            if (startX === null) return;
            var diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) >= threshold) {
                move(id, diff > 0 ? 1 : -1);
            }
            startX = null;
        }, { passive: true });
    }

    // ── Bootstrap ────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init(); // ya cargado (ej. script defer)
    }

})();
