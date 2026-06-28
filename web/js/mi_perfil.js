/**
 * mi_perfil.js
 */
$(function () {

    /* ══════════════════════════════════════════════
       TABS
    ══════════════════════════════════════════════ */
    $('.pf-tab').on('click', function () {
        var tab = $(this).data('tab');
        $('.pf-tab').removeClass('active');
        $(this).addClass('active');
        $('.pf-tab-panel').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });

    /* ══════════════════════════════════════════════
       BIO — EDITAR DESCRIPCIÓN
    ══════════════════════════════════════════════ */
    if (!IS_OWNER) return;

    var $bioDisplay  = $('#pf-bio-display');
    var $bioEditor   = $('#pf-bio-editor');
    var $bioText     = $('#pf-bio-text');
    var $bioTextarea = $('#pf-bio-textarea');
    var $bioCount    = $('#pf-bio-count');

    // Contador inicial
    $bioCount.text($bioTextarea.val().length);

    $bioTextarea.on('input', function () {
        $bioCount.text($(this).val().length);
    });

    // Abrir editor
    $('#pf-bio-edit-btn').on('click', function () {
        $bioDisplay.hide();
        $bioEditor.show();
        $bioTextarea.focus();
    });

    // Cancelar
    $('#pf-bio-cancel').on('click', function () {
        $bioEditor.hide();
        $bioDisplay.show();
        // Restaurar valor original
        $bioTextarea.val($bioText.text().trim() === 'Añade una descripción sobre ti...' ? '' : $bioText.text().trim());
        $bioCount.text($bioTextarea.val().length);
    });

    // Guardar
    $('#pf-bio-save').on('click', function () {
        var bio  = $bioTextarea.val().trim();
        var $btn = $(this).prop('disabled', true).text('Guardando…');

        $.ajax({
            url:       BASE_URL + '/site/guardar-bio',
            type:      'POST',
            xhrFields: { withCredentials: true },
            data: {
                _csrf: CSRF_TOKEN,
                bio:   bio,
            },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    if (r.bio) {
                        $bioText.html(r.bio.replace(/\n/g, '<br>'));
                    } else {
                        $bioText.html('<span class="pf-bio-empty">Añade una descripción sobre ti...</span>');
                    }
                    $bioEditor.hide();
                    $bioDisplay.show();
                } else {
                    alert('No se pudo guardar.');
                }
            },
            error: function (xhr) {
                console.error('[GUARDAR-BIO] Error:', xhr.status, xhr.responseText);
                alert('Error de conexión. (Código: ' + xhr.status + ')');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Guardar');
            }
        });
    });

});

// Abrir tab por ancla en la URL
var hash = window.location.hash;
if (hash === '#tab-recetas') {
    $('[data-tab="recetas"]').trigger('click');
}