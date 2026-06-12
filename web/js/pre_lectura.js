/**
 * pre_lectura.js
 */
$(function () {

    /* ══════════════════════════════════════════════
       LOADING
    ══════════════════════════════════════════════ */
    function showLoading() { $('#rl-loading').fadeIn(150); }
    function hideLoading() { $('#rl-loading').fadeOut(200); }


    /* ══════════════════════════════════════════════
       1. TABS
    ══════════════════════════════════════════════ */
    $('.pl-tab').on('click', function () {
        var tab = $(this).data('tab');
        $('.pl-tab').removeClass('active');
        $(this).addClass('active');
        $('.pl-tab-panel').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });


    /* ══════════════════════════════════════════════
       2. DROPDOWN AÑADIR (Guardar / Favorito)
    ══════════════════════════════════════════════ */
    var $addBtn      = $('#pl-btn-add');
    var $dropdown    = $('#pl-add-dropdown');
    var $chevron     = $('#pl-chevron');
    var $addLabel    = $('#pl-add-label');
    var $addIcon     = $('#pl-add-icon');

    // Estado inicial desde servidor
    var savedGuardado = COLLECTION && COLLECTION.guardado ? true : false;
    var savedFavorito = COLLECTION && COLLECTION.favorito ? true : false;

    // Inicializar estado visual
    updateAddBtn();

    function updateAddBtn() {
        if (savedGuardado && savedFavorito) {
            $addBtn.addClass('active');
            $addLabel.text('Guardado y favorito');
            $addIcon.removeClass().addClass('fa-solid fa-check');
        } else if (savedGuardado) {
            $addBtn.addClass('active');
            $addLabel.text('Guardado');
            $addIcon.removeClass().addClass('fa-solid fa-bookmark');
        } else if (savedFavorito) {
            $addBtn.addClass('active');
            $addLabel.text('Favorito');
            $addIcon.removeClass().addClass('fa-solid fa-heart');
        } else {
            $addBtn.removeClass('active');
            $addLabel.text('Guardar');
            $addIcon.removeClass().addClass('fa-solid fa-bookmark');
        }

        // Estado visual de los items del dropdown
        $('#btn-guardar').toggleClass('active-item', savedGuardado);
        $('#btn-favorito').toggleClass('active-item', savedFavorito);
    }

    // Abrir / cerrar dropdown
    $addBtn.on('click', function (e) {
        e.stopPropagation();
        if (IS_GUEST) {
            window.location.href = BASE_URL + '/index.php?r=site/login';
            return;
        }
        $dropdown.toggleClass('open');
        $chevron.toggleClass('open');
    });

    // Cerrar al hacer clic fuera
    $(document).on('click', function (e) {
        if (!$('#pl-add-wrap').length) return;
        if (!$('#pl-add-wrap')[0].contains(e.target)) {
            $dropdown.removeClass('open');
            $chevron.removeClass('open');
        }
    });

    // Clic en Guardar / Favorito
    $('.pl-dropdown-item').on('click', function () {
        var tipo = $(this).data('tipo');
        if (IS_GUEST) return;

        var isActive = tipo === 'guardado' ? savedGuardado : savedFavorito;
        var action   = isActive ? 'remove' : 'add';

        showLoading();

        $.post(BASE_URL + '/index.php?r=site/toggle-collection', {
            _csrf:     CSRF_TOKEN,
            recipe_id: RECIPE_ID,
            tipo:      tipo,
            action:    action,
        }, function (r) {
            if (r.success) {
                if (tipo === 'guardado') {
                    savedGuardado = !savedGuardado;
                } else {
                    savedFavorito = !savedFavorito;
                }
                updateAddBtn();
                $dropdown.removeClass('open');
                $chevron.removeClass('open');
            } else {
                alert(r.message || 'No se pudo actualizar.');
            }
        }).fail(function () {
            alert('Error de conexión.');
        }).always(hideLoading);
    });


    /* ══════════════════════════════════════════════
       3. STAR PICKER (calificación)
    ══════════════════════════════════════════════ */
    var selectedStars = 0;

    // Si ya tiene reseña previa, cargarla
    if (USER_REVIEW) {
        selectedStars = USER_REVIEW.score || 0;
        if (USER_REVIEW.comment) {
            $('#review-text').val(USER_REVIEW.comment);
        }
        renderStars(selectedStars);
        $('#btn-submit-review').prop('disabled', false)
            .text('Actualizar reseña');
    }

    // Hover
    $('#star-picker .pl-star-btn').on('mouseenter', function () {
        var val = parseInt($(this).data('val'));
        highlightStars(val);
    }).on('mouseleave', function () {
        renderStars(selectedStars);
    });

    // Click
    $('#star-picker .pl-star-btn').on('click', function () {
        selectedStars = parseInt($(this).data('val'));
        renderStars(selectedStars);
        $('#btn-submit-review').prop('disabled', false);
    });

    function highlightStars(n) {
        $('#star-picker .pl-star-btn').each(function (i) {
            var $icon = $(this).find('i');
            if (i < n) {
                $icon.removeClass('fa-regular').addClass('fa-solid');
                $(this).addClass('hover');
            } else {
                $icon.removeClass('fa-solid').addClass('fa-regular');
                $(this).removeClass('hover');
            }
        });
    }

    function renderStars(n) {
        $('#star-picker .pl-star-btn').each(function (i) {
            var $icon = $(this).find('i');
            if (i < n) {
                $icon.removeClass('fa-regular').addClass('fa-solid');
                $(this).addClass('selected');
            } else {
                $icon.removeClass('fa-solid').addClass('fa-regular');
                $(this).removeClass('selected hover');
            }
        });
    }


    /* ══════════════════════════════════════════════
       4. SUBMIT RESEÑA
    ══════════════════════════════════════════════ */
    $('#btn-submit-review').on('click', function () {
        if (!selectedStars) return;

        var $btn = $(this).prop('disabled', true).text('Publicando…');
        showLoading();

        $.post(BASE_URL + '/index.php?r=site/submit-review', {
            _csrf:     CSRF_TOKEN,
            recipe_id: RECIPE_ID,
            score:     selectedStars,
            comment:   $('#review-text').val().trim(),
        }, function (r) {
            if (r.success) {
                // Actualizar stats en pantalla
                $('#avg-value').text(r.avg.toFixed(1));
                $('#review-count').text('(' + r.total + ' reseñas)');
                $('#big-score').text(r.avg.toFixed(1));
                $('#total-votes').text(r.total + ' votos');

                // Actualizar barras
                if (r.distribution) {
                    for (var s = 1; s <= 5; s++) {
                        var pct = r.distribution[s] || 0;
                        $('[data-star="' + s + '"]').css('width', pct + '%');
                        $('#star-count-' + s).text(r.counts[s] || 0);
                    }
                }

                // Actualizar stars del resumen
                renderSummaryStars(r.avg);

                // Agregar reseña a la lista si tiene comentario
                if (r.comment) {
                    addReviewToList(r);
                }

                $btn.text('Reseña publicada ✓').prop('disabled', true);
            } else {
                alert(r.message || 'No se pudo publicar.');
                $btn.prop('disabled', false).text('Publicar reseña');
            }
        }).fail(function () {
            alert('Error de conexión.');
            $btn.prop('disabled', false).text('Publicar reseña');
        }).always(hideLoading);
    });

    function renderSummaryStars(avg) {
        var full = Math.floor(avg);
        var half = (avg - full) >= 0.5;
        var html = '';
        for (var i = 1; i <= 5; i++) {
            if (i <= full) {
                html += '<i class="fa-solid fa-star pl-star-filled"></i>';
            } else if (half && i === full + 1) {
                html += '<i class="fa-solid fa-star-half-stroke pl-star-filled"></i>';
            } else {
                html += '<i class="fa-regular fa-star pl-star-empty"></i>';
            }
        }
        $('#avg-stars-display').html(html);
        $('#big-stars-display').html(html);
    }

    function addReviewToList(r) {
        var starsHtml = '';
        for (var i = 1; i <= 5; i++) {
            starsHtml += i <= r.score
                ? '<i class="fa-solid fa-star pl-star-filled"></i>'
                : '<i class="fa-regular fa-star pl-star-filled"></i>';
        }

        var avatarHtml = r.avatar_url
            ? '<img src="' + r.avatar_url + '" class="pl-review-avatar" alt="Avatar">'
            : '<div class="pl-review-avatar-default">' + r.username.charAt(0).toUpperCase() + '</div>';

        var commentHtml = r.comment
            ? '<p class="pl-review-text">' + $('<div>').text(r.comment).html() + '</p>'
            : '';

        var $item = $(
            '<div class="pl-review-item">' +
                '<div class="pl-review-header">' +
                    avatarHtml +
                    '<div class="pl-review-meta">' +
                        '<span class="pl-review-user">' + $('<div>').text(r.username).html() + '</span>' +
                        '<div class="pl-review-stars">' + starsHtml + '</div>' +
                    '</div>' +
                '</div>' +
                commentHtml +
            '</div>'
        );

        $('.pl-no-reviews').remove();
        $('#reviews-list').prepend($item);
    }

});