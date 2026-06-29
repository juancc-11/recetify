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


    /* ══════════════════════════════════════════════
       2. DROPDOWN AÑADIR (Guardar / Favorito)
    ══════════════════════════════════════════════ */
    var $addBtn   = $('#pl-btn-add');
    var $dropdown = $('#pl-add-dropdown');
    var $chevron  = $('#pl-chevron');
    var $addLabel = $('#pl-add-label');
    var $addIcon  = $('#pl-add-icon');

    var savedGuardado = COLLECTION && COLLECTION.guardado ? true : false;
    var savedFavorito = COLLECTION && COLLECTION.favorito ? true : false;

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
        $('#btn-guardar').toggleClass('active-item', savedGuardado);
        $('#btn-favorito').toggleClass('active-item', savedFavorito);
    }

    $addBtn.on('click', function (e) {
        e.stopPropagation();
        if (IS_GUEST) {
            showToast('Debes iniciar sesión para utilizar esta función');
            return;
        }
        $dropdown.toggleClass('open');
        $chevron.toggleClass('open');
    });

    $(document).on('click', function (e) {
        if (!$('#pl-add-wrap').length) return;
        if (!$('#pl-add-wrap')[0].contains(e.target)) {
            $dropdown.removeClass('open');
            $chevron.removeClass('open');
        }
    });

    $('.pl-dropdown-item').on('click', function () {
        var tipo     = $(this).data('tipo');
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
                    updateAddBtn();
                    $dropdown.removeClass('open');
                    $chevron.removeClass('open');
                } else {
                    alert(r.message || 'No se pudo actualizar.');
                }
            },
            error: function (xhr) {
                console.error('[TOGGLE-COLLECTION] Error:', xhr.status, xhr.responseText);
                alert('Error de conexión. (Código: ' + xhr.status + ')');
            }
        }).always(hideLoading);
    });


    /* ══════════════════════════════════════════════
       3. STAR PICKER (calificación)
    ══════════════════════════════════════════════ */
    var selectedStars = 0;

    if (USER_REVIEW) {
        selectedStars = USER_REVIEW.score || 0;
        if (USER_REVIEW.comment) {
            $('#review-text').val(USER_REVIEW.comment);
        }
        renderStars(selectedStars);
        $('#btn-submit-review')
            .prop('disabled', false)
            .text('Actualizar reseña');
    }

    $('#star-picker .pl-star-btn').on('mouseenter', function () {
        highlightStars(parseInt($(this).data('val')));
    }).on('mouseleave', function () {
        renderStars(selectedStars);
    });

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

        $.ajax({
            url:       BASE_URL + '/site/subir-comment',
            type:      'POST',
            xhrFields: { withCredentials: true },
            data: {
                _csrf:     CSRF_TOKEN,
                recipe_id: RECIPE_ID,
                score:     selectedStars,
                comment:   $('#review-text').val().trim(),
            },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    $('#avg-value').text(r.avg.toFixed(1));
                    $('#review-count').text('(' + r.total + ' reseñas)');
                    $('#big-score').text(r.avg.toFixed(1));
                    $('#total-votes').text(r.total + ' votos');

                    if (r.distribution) {
                        for (var s = 1; s <= 5; s++) {
                            var pct = r.distribution[s] || 0;
                            $('[data-star="' + s + '"]').css('width', pct + '%');
                            $('#star-count-' + s).text(r.counts[s] || 0);
                        }
                    }

                    renderSummaryStars(r.avg);

                    addReviewToList(r);

                    $btn.text('Reseña publicada ✓').prop('disabled', true);
                } else {
                    var msg = r.message || 'No se pudo publicar.';
                    if (r.errors) {
                        msg += '\nErrores: ' + JSON.stringify(r.errors);
                    }
                    alert(msg);
                    $btn.prop('disabled', false).text('Publicar reseña');
                }
            },
            error: function (xhr) {
                console.error('[SUBIR-COMMENT] Error:', xhr.status, xhr.responseText);
                alert('Error de conexión. (Código: ' + xhr.status + ')');
                $btn.prop('disabled', false).text('Publicar reseña');
            }
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
                : '<i class="fa-regular fa-star pl-star-empty"></i>';
        }

        var avatarHtml = r.avatar_url
            ? '<img src="' + r.avatar_url + '" class="pl-review-avatar" alt="Avatar">'
            : '<div class="pl-review-avatar-default">' +
                $('<div>').text(r.username.charAt(0).toUpperCase()).html() +
              '</div>';

        var commentHtml = r.comment
            ? '<p class="pl-review-text">' + $('<div>').text(r.comment).html() + '</p>'
            : '';

        var $item = $(
            '<div class="pl-review-item">' +
                '<div class="pl-review-header">' +
                    avatarHtml +
                    '<div class="pl-review-meta">' +
                        '<span class="pl-review-user">' +
                            $('<div>').text(r.username).html() +
                        '</span>' +
                        '<div class="pl-review-stars">' + starsHtml + '</div>' +
                    '</div>' +
                '</div>' +
                commentHtml +
            '</div>'
        );

        $('.pl-no-reviews').remove();
        $('#reviews-list').prepend($item.hide().fadeIn(300));
    }

});