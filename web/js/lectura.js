/**
 * lectura.js
 */
$(function () {

    /* ══════════════════════════════════════════════
       LOADING
    ══════════════════════════════════════════════ */
    function showLoading() { $('#rl-loading').fadeIn(150); }
    function hideLoading() { $('#rl-loading').fadeOut(200); }


    /* ══════════════════════════════════════════════
       ESTADO PERSISTENTE (memoria de sesión, no localStorage)
    ══════════════════════════════════════════════ */
    var state = {
        font:       'nunito',
        fontSize:   16,
        lineHeight: 1.75,
        theme:      'light',
        ttsEnabled: false,
        ttsVoice:   null,
        ttsSpeed:   1,
        ttsHighlight: true,
    };

    var lineHeightSteps = [1.4, 1.55, 1.75, 2.0, 2.3];
    var lineHeightLabels = ['Compacto', 'Reducido', 'Normal', 'Amplio', 'Extra'];
    var lineHeightIdx = 2;

    var fontFamilies = {
        nunito: "'Nunito Sans', sans-serif",
        roboto: "'Roboto', sans-serif",
        lora:   "'Lora', serif",
    };

    var $page = $('#lc-page');

    function applyState() {
        $page.attr('data-theme', state.theme);
        $page.attr('data-font', state.font);
        document.documentElement.style.setProperty('--lc-font-size', state.fontSize + 'px');
        document.documentElement.style.setProperty('--lc-line-height', state.lineHeight);
        document.documentElement.style.setProperty('--lc-font-family', fontFamilies[state.font]);
    }

    applyState();


    /* ══════════════════════════════════════════════
       1. ABRIR / CERRAR MENÚ
    ══════════════════════════════════════════════ */
    var $overlay   = $('#lc-overlay');
    var $menuTop   = $('#lc-menu-top');
    var $menuBottom = $('#lc-menu-bottom');
    var $closeBtn  = $('#lc-close-btn');
    var menuOpen   = false;

    function openMenu() {
        menuOpen = true;
        $overlay.addClass('open');
        $menuTop.addClass('open');
        $menuBottom.addClass('open');
        $closeBtn.addClass('visible open');
    }

    function closeMenu() {
        menuOpen = false;
        $overlay.removeClass('open');
        $menuTop.removeClass('open');
        $menuBottom.removeClass('open');
        $closeBtn.removeClass('open');
        setTimeout(function () {
            if (!menuOpen) $closeBtn.removeClass('visible');
        }, 250);
    }

    function toggleMenu() {
        if (menuOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    // Clic en el contenido de la receta abre/cierra
    $('.lc-content').on('click', function (e) {
        // Evitar que clics en imágenes interactivas interfieran
        toggleMenu();
    });

    // Clic en el overlay cierra
    $overlay.on('click', function () {
        closeMenu();
    });

    // Botón flotante de cierre
    $closeBtn.on('click', function (e) {
        e.stopPropagation();
        toggleMenu();
    });

    // Evitar que clics dentro del menú inferior cierren el menú
    $menuBottom.on('click', function (e) {
        e.stopPropagation();
    });
    $menuTop.on('click', function (e) {
        e.stopPropagation();
    });


    /* ══════════════════════════════════════════════
       2. TABS DEL MENÚ INFERIOR
    ══════════════════════════════════════════════ */
    $('.lc-tab').on('click', function () {
        var panel = $(this).data('panel');
        $('.lc-tab').removeClass('active');
        $(this).addClass('active');
        $('.lc-panel').removeClass('active');
        $('#lc-panel-' + panel).addClass('active');
    });


    /* ══════════════════════════════════════════════
       3. PANEL READ — Guardar / Favorito
    ══════════════════════════════════════════════ */
    var savedGuardado = COLLECTION && COLLECTION.guardado ? true : false;
    var savedFavorito = COLLECTION && COLLECTION.favorito ? true : false;

    function updateCollectionBtns() {
        $('#lc-btn-guardado').toggleClass('active-state', savedGuardado);
        $('#lc-guardado-label').text(savedGuardado ? 'Guardado' : 'Guardar');
        $('#lc-btn-favorito').toggleClass('active-state', savedFavorito);
        $('#lc-favorito-label').text(savedFavorito ? 'En favoritos' : 'Favorito');
    }

    function toggleCollection(tipo) {
        if (IS_GUEST) {
            window.location.href = BASE_URL + '/site/login';
            return;
        }

        var isActive = tipo === 'guardado' ? savedGuardado : savedFavorito;
        var action   = isActive ? 'remove' : 'add';

        showLoading();

        $.ajax({
            url:       BASE_URL + '/site/toggle-collection',
            type:      'POST',
            xhrFields: { withCredentials: true },
            data: {
                _csrf:     CSRF_TOKEN,
                recipe_id: RECIPE_ID,
                tipo:      tipo,
                action:    action,
            },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    if (tipo === 'guardado') {
                        savedGuardado = !savedGuardado;
                    } else {
                        savedFavorito = !savedFavorito;
                    }
                    updateCollectionBtns();
                } else {
                    alert(r.message || 'No se pudo actualizar.');
                }
            },
            error: function (xhr) {
                console.error('[TOGGLE-COLLECTION] Error:', xhr.status, xhr.responseText);
                alert('Error de conexión. (Código: ' + xhr.status + ')');
            }
        }).always(hideLoading);
    }

    $('#lc-btn-guardado').on('click', function () { toggleCollection('guardado'); });
    $('#lc-btn-favorito').on('click', function () { toggleCollection('favorito'); });
    updateCollectionBtns();


    /* ══════════════════════════════════════════════
       4. PANEL DISPLAY — Fuente, tamaño, interlineado
    ══════════════════════════════════════════════ */

    // Fuente
    $('[data-font]').on('click', function () {
        var font = $(this).data('font');
        state.font = font;
        $('[data-font]').removeClass('active');
        $(this).addClass('active');
        applyState();
    });

    // Tamaño de fuente
    $('#lc-font-dec').on('click', function () {
        state.fontSize = Math.max(12, state.fontSize - 1);
        $('#lc-font-size-display').text(state.fontSize);
        applyState();
    });
    $('#lc-font-inc').on('click', function () {
        state.fontSize = Math.min(28, state.fontSize + 1);
        $('#lc-font-size-display').text(state.fontSize);
        applyState();
    });

    // Interlineado
    $('#lc-line-dec').on('click', function () {
        lineHeightIdx = Math.max(0, lineHeightIdx - 1);
        state.lineHeight = lineHeightSteps[lineHeightIdx];
        $('#lc-line-display').text(lineHeightLabels[lineHeightIdx]);
        applyState();
    });
    $('#lc-line-inc').on('click', function () {
        lineHeightIdx = Math.min(lineHeightSteps.length - 1, lineHeightIdx + 1);
        state.lineHeight = lineHeightSteps[lineHeightIdx];
        $('#lc-line-display').text(lineHeightLabels[lineHeightIdx]);
        applyState();
    });


    /* ══════════════════════════════════════════════
       5. PANEL SETTINGS — Tema de lectura
    ══════════════════════════════════════════════ */
    $('.lc-theme-swatch').on('click', function () {
        var theme = $(this).data('theme');
        state.theme = theme;
        $('.lc-theme-swatch').removeClass('active');
        $(this).addClass('active');
        applyState();
    });

    // Auto-bloqueo de pantalla (Wake Lock API)
    var wakeLock = null;
    $('#lc-autolock').on('change', function () {
        var enabled = $(this).is(':checked');
        if (enabled) {
            if ('wakeLock' in navigator) {
                navigator.wakeLock.request('screen').then(function (lock) {
                    wakeLock = lock;
                }).catch(function () {
                    // silencioso si el navegador no lo soporta
                });
            }
        } else {
            if (wakeLock) {
                wakeLock.release();
                wakeLock = null;
            }
        }
    });


    /* ══════════════════════════════════════════════
       6. PANEL SPEECH — Text-to-Speech
    ══════════════════════════════════════════════ */
    var synth = window.speechSynthesis;
    var utterance = null;
    var ttsParagraphs = [];
    var ttsIndex = 0;
    var ttsPlaying = false;

    // Cargar voces disponibles
    function loadVoices() {
        var voices = synth.getVoices();
        var $select = $('#lc-tts-voice');
        $select.empty();

        // Priorizar voces en español
        var spanishVoices = voices.filter(function (v) { return v.lang.startsWith('es'); });
        var otherVoices   = voices.filter(function (v) { return !v.lang.startsWith('es'); });
        var ordered = spanishVoices.concat(otherVoices);

        ordered.forEach(function (v, i) {
            $select.append(
                $('<option></option>')
                    .val(i)
                    .text(v.name + ' (' + v.lang + ')')
            );
        });

        window._lcVoices = ordered;
    }

    loadVoices();
    if (synth.onvoiceschanged !== undefined) {
        synth.onvoiceschanged = loadVoices;
    }

    // Preparar párrafos del cuerpo de la receta
    function prepareParagraphs() {
        ttsParagraphs = [];
        $('#lc-body').children().each(function () {
            var $el = $(this);
            // Saltar elementos que sean o contengan imágenes
            if ($el.is('img') || $el.find('img').length > 0 || $el.hasClass('editor-img-wrap')) {
                return;
            }

            var text = $el.text().trim();
            if (text) {
                ttsParagraphs.push({ el: $el, text: text });
            }
        });
    }
    prepareParagraphs();

    function clearHighlights() {
        $('.lc-tts-highlight').removeClass('lc-tts-highlight');
    }

    function speakFrom(index) {
        if (!ttsParagraphs.length) return;
        if (index >= ttsParagraphs.length) {
            ttsPlaying = false;
            clearHighlights();
            return;
        }

        ttsIndex = index;
        var para = ttsParagraphs[ttsIndex];

        utterance = new SpeechSynthesisUtterance(para.text);

        var voiceIdx = $('#lc-tts-voice').val();
        if (window._lcVoices && window._lcVoices[voiceIdx]) {
            utterance.voice = window._lcVoices[voiceIdx];
            utterance.lang  = window._lcVoices[voiceIdx].lang;
        }
        utterance.rate = parseFloat($('#lc-tts-speed').val()) || 1;

        if ($('#lc-tts-highlight').is(':checked')) {
            clearHighlights();
            para.el.addClass('lc-tts-highlight');
        }

        utterance.onend = function () {
            if (ttsPlaying) {
                speakFrom(ttsIndex + 1);
            }
        };

        synth.speak(utterance);
    }

    $('#lc-tts-play').on('click', function () {
        if (!ttsParagraphs.length) prepareParagraphs();
        if (synth.paused && ttsPlaying === false && synth.speaking) {
            synth.resume();
            ttsPlaying = true;
            return;
        }
        synth.cancel();
        ttsPlaying = true;
        speakFrom(0);
    });

    $('#lc-tts-stop').on('click', function () {
        ttsPlaying = false;
        synth.cancel();
        clearHighlights();
    });

    $('#lc-tts-enable').on('change', function () {
        state.ttsEnabled = $(this).is(':checked');
        if (!state.ttsEnabled) {
            ttsPlaying = false;
            synth.cancel();
            clearHighlights();
        }
    });

    $('#lc-tts-highlight').on('change', function () {
        if (!$(this).is(':checked')) clearHighlights();
    });

    // Detener TTS si se cierra el menú o se navega
    $(window).on('beforeunload', function () {
        synth.cancel();
    });


    /* ══════════════════════════════════════════════
       7. PANEL MORE — Reportar receta
    ══════════════════════════════════════════════ */
    var $reportModal = $('#lc-report-modal');

    $('#lc-btn-report').on('click', function () {
        if (IS_GUEST) {
            window.location.href = BASE_URL + '/site/login';
            return;
        }
        $reportModal.addClass('open').attr('aria-hidden', 'false');
        $('body').css('overflow', 'hidden');
    });

    function closeReportModal() {
        $reportModal.removeClass('open').attr('aria-hidden', 'true');
        $('body').css('overflow', '');
    }

    $('#lc-report-close, #lc-report-cancel').on('click', closeReportModal);
    $reportModal.on('click', function (e) {
        if ($(e.target).is($reportModal)) closeReportModal();
    });

    $('#lc-report-submit').on('click', function () {
        var motivo = $('#lc-report-motivo').val();
        var desc   = $('#lc-report-desc').val().trim();
        var $btn   = $(this).prop('disabled', true).text('Enviando…');

        showLoading();

        $.ajax({
            url:       BASE_URL + '/site/reportar-receta',
            type:      'POST',
            xhrFields: { withCredentials: true },
            data: {
                _csrf:      CSRF_TOKEN,
                recipe_id:  RECIPE_ID,
                motivo:     motivo,
                descripcion: desc,
            },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    alert('Reporte enviado. Gracias por ayudarnos a mantener la comunidad segura.');
                    closeReportModal();
                    $('#lc-report-desc').val('');
                } else {
                    alert(r.message || 'No se pudo enviar el reporte.');
                }
            },
            error: function (xhr) {
                console.error('[REPORTAR-RECETA] Error:', xhr.status, xhr.responseText);
                alert('Error de conexión. (Código: ' + xhr.status + ')');
            }
        }).always(function () {
            hideLoading();
            $btn.prop('disabled', false).text('Enviar reporte');
        });
    });


    /* ══════════════════════════════════════════════
       8. ESC para cerrar
    ══════════════════════════════════════════════ */
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            closeMenu();
            closeReportModal();
        }
    });

});