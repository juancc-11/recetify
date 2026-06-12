/**
 * mis_recetas.js — CORREGIDO
 * Fixes:
 *  1. Imágenes adicionales: se envían las URLs de Cloudinary al servidor en el submit
 *  2. Modal editar: se agrega console.log para depuración y se corrige carga de slots
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
    const tabMap = {
        'mis-recetas':  '#tab-mis-recetas',
        'estadisticas': '#tab-estadisticas',
        'nueva-receta': '#tab-nueva-receta'
    };
    $('.tab-btn').on('click', function () {
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.tab-panel').removeClass('active');
        $(tabMap[$(this).data('tab')]).addClass('active');
    });
    $('#btn-cancelar-nueva').on('click', function () {
        $('[data-tab="mis-recetas"]').trigger('click');
    });


    /* ══════════════════════════════════════════════
       2. TOGGLE PUBLICAR / DESPUBLICAR
    ══════════════════════════════════════════════ */
    $(document).on('click', '.btn-toggle-publish', function () {
        const $b  = $(this);
        const val = parseInt($b.data('published')) === 1 ? 0 : 1;
        togglePublish($b.data('recipe-id'), val, $b);
    });

    function togglePublish(id, val, $btn) {
        showLoading();
        $.post(
            BASE_URL + '/site/toggle-publish',
            { _csrf: CSRF_TOKEN, recipe_id: id, published: val },
            function (r) {
                if (r.success) {
                    $btn.data('published', val)
                        .text(val ? 'Despublicar' : 'Publicar')
                        .toggleClass('despublicar', val === 1);
                } else {
                    alert('No se pudo actualizar: ' + (r.message || 'Error desconocido'));
                }
            },
            'json'
        ).fail(function (xhr) {
            console.error('[TOGGLE-PUBLISH] Error:', xhr.status, xhr.responseText);
            alert('Error de conexión. (Código: ' + xhr.status + ')');
        }).always(hideLoading);
    }


    /* ══════════════════════════════════════════════
       3. BORRAR RECETA
    ══════════════════════════════════════════════ */
    let recipeIdToDelete = null;

    $(document).on('click', '.btn-borrar-receta', function () {
        recipeIdToDelete = $(this).data('recipe-id');
        $('#confirm-recipe-titulo').text($(this).data('titulo'));
        $('#modal-confirmar-borrar').addClass('open').attr('aria-hidden', 'false');
        $('body').css('overflow', 'hidden');
    });

    function cerrarModalBorrar() {
        $('#modal-confirmar-borrar').removeClass('open').attr('aria-hidden', 'true');
        $('body').css('overflow', '');
        $('#btn-confirmar-si').prop('disabled', false).text('Sí, eliminar');
        recipeIdToDelete = null;
    }

    $('#btn-confirmar-no').on('click', cerrarModalBorrar);
    $('#modal-confirmar-borrar').on('click', function (e) {
        if ($(e.target).is(this)) cerrarModalBorrar();
    });

    $('#btn-confirmar-si').on('click', function () {
        if (!recipeIdToDelete) return;
        const $btn = $(this).prop('disabled', true).text('Eliminando…');
        showLoading();
        $.post(
            BASE_URL + '/site/borrar-receta',
            { _csrf: CSRF_TOKEN, recipe_id: recipeIdToDelete },
            function (r) {
                if (r.success) {
                    $('[data-id="' + recipeIdToDelete + '"]').fadeOut(300, function () {
                        $(this).remove();
                        if ($('.receta-card').length === 0) {
                            $('#recetas-lista').html(
                                '<div class="empty-state"><p>Aún no has publicado ninguna receta.</p></div>'
                            );
                        }
                    });
                    cerrarModalBorrar();
                } else {
                    alert(r.message || 'No se pudo eliminar.');
                    $btn.prop('disabled', false).text('Sí, eliminar');
                }
            },
            'json'
        ).fail(function (xhr) {
            console.error('[BORRAR-RECETA] Error:', xhr.status, xhr.responseText);
            alert('Error de conexión. (Código: ' + xhr.status + ')');
            $btn.prop('disabled', false).text('Sí, eliminar');
        }).always(hideLoading);
    });


    /* ══════════════════════════════════════════════
       4. ESTADÍSTICAS
    ══════════════════════════════════════════════ */
    let chartV = null, chartD = null;

    $('#stats-recipe-select').on('change', function () {
        const id = $(this).val();
        if (!id) {
            $('#stats-content').html('<div class="stats-placeholder"><p>Selecciona una receta.</p></div>');
            return;
        }
        showLoading();
        $('#stats-content').html('<div class="stats-placeholder"><p>Cargando…</p></div>');
        $.get(BASE_URL + '/site/recipe-stats', { recipe_id: id }, function (d) {
            if (!d || d.error) {
                $('#stats-content').html('<div class="stats-placeholder"><p>Sin datos.</p></div>');
                return;
            }
            $('#stats-content').empty().append(
                document.getElementById('stats-template').content.cloneNode(true)
            );
            if (chartV) chartV.destroy();
            chartV = new Chart(document.getElementById('chart-visitas'), {
                type: 'line',
                data: {
                    labels: d.visitas.map(v => v.fecha),
                    datasets: [{
                        data: d.visitas.map(v => v.total),
                        borderColor: '#F26522',
                        backgroundColor: 'rgba(242,101,34,.1)',
                        pointBackgroundColor: '#F26522',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
            if (chartD) chartD.destroy();
            chartD = new Chart(document.getElementById('chart-datos'), {
                type: 'bar',
                data: {
                    labels: ['Visitas', 'Comentarios', 'Guardados', 'Favoritos', 'Historial'],
                    datasets: [{
                        data: [
                            d.totales.visitas,
                            d.totales.comentarios,
                            d.totales.guardados,
                            d.totales.favoritos,
                            d.totales.historial
                        ],
                        backgroundColor: '#29B6D6',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });
            const pos = d.ranking.posicion, tot = d.ranking.total;
            $('#stat-rating-pos').text('#' + pos);
            $('#stat-total-recipes').text(tot);
            setTimeout(() => {
                $('#stat-rating-bar').css('width',
                    (tot > 0 ? Math.max(4, Math.round((1 - (pos - 1) / tot) * 100)) : 0) + '%'
                );
            }, 100);
        }).fail(function (xhr) {
            console.error('[RECIPE-STATS] Error:', xhr.status, xhr.responseText);
            $('#stats-content').html('<div class="stats-placeholder"><p>Error al cargar. (Código: ' + xhr.status + ')</p></div>');
        }).always(hideLoading);
    });


    /* ══════════════════════════════════════════════
       5. SLOTS DE IMÁGENES EXTRA
          Sube a Cloudinary inmediatamente al seleccionar
          → guarda URL real en data-cloudinary-url
    ══════════════════════════════════════════════ */
    $(document).on('change', 'input[type=file][data-slot]', function () {
        const file   = this.files[0];
        if (!file) return;
        const $input = $(this);
        const $slot  = $input.closest('.multi-thumb');
        const isEdit = $slot.closest('#edit-multi-upload').length > 0;

        // Preview local inmediato mientras sube
        const blobUrl = URL.createObjectURL(file);
        $slot.find('.slot-preview').attr('src', blobUrl).css('opacity', '0.5');
        $slot.find('.slot-preview-wrap').show();
        $slot.addClass('has-image');
        $slot.data('uploading', true);

        // Subir a Cloudinary inmediatamente
        const fd = new FormData();
        fd.append('imagen', file);
        fd.append('_csrf', CSRF_TOKEN);

        $.ajax({
            url:         BASE_URL + '/site/subir-imagen-temp',
            type:        'POST',
            data:        fd,
            xhrFields: { withCredentials: true },
            processData: false,
            contentType: false,
            success: function (r) {
                if (r.success) {
                    // Guardar URL real de Cloudinary y actualizar preview
                    $slot.data('cloudinary-url', r.url);
                    $slot.find('.slot-preview').attr('src', r.url).css('opacity', '1');
                    $slot.data('uploading', false);
                    $slot.find('.delete-img-flag').prop('disabled', true).val('');
                    refreshToolbar(isEdit ? 'editar' : 'nueva');
                } else {
                    alert('Error al subir imagen: ' + (r.message || 'Error desconocido'));
                    $slot.find('.slot-preview').attr('src', '').css('opacity', '1');
                    $slot.find('.slot-preview-wrap').hide();
                    $slot.removeClass('has-image');
                    $slot.removeData('cloudinary-url');
                    $input.val('');
                    $slot.data('uploading', false);
                }
            },
            error: function (xhr, status, error) {
                console.error('[SUBIR-IMAGEN-TEMP] Error:', xhr.status, xhr.responseText);
                alert('Error de conexión al subir la imagen. (Código: ' + xhr.status + ')');
                $slot.find('.slot-preview').attr('src', '').css('opacity', '1');
                $slot.find('.slot-preview-wrap').hide();
                $slot.removeClass('has-image');
                $slot.removeData('cloudinary-url');
                $input.val('');
                $slot.data('uploading', false);
            }
        });
    });

    $(document).on('click', '.slot-remove', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const $slot  = $(this).closest('.multi-thumb');
        const isEdit = $slot.closest('#edit-multi-upload').length > 0;
        const imgId  = $slot.data('img-id');
        const $flag  = $slot.find('.delete-img-flag');

        if (imgId && $flag.length) $flag.prop('disabled', false).val(imgId);

        $slot.find('input[type=file]').val('');
        $slot.find('.slot-preview').attr('src', '').css('opacity', '1');
        $slot.find('.slot-preview-wrap').hide();
        $slot.removeClass('has-image')
             .removeData('cloudinary-url')
             .removeData('img-id')
             .removeData('uploading');

        refreshToolbar(isEdit ? 'editar' : 'nueva');
    });


    /* ══════════════════════════════════════════════
       6. PREVIEW PORTADA
    ══════════════════════════════════════════════ */
    $('#portada-input').on('change', function () {
        if (!this.files[0]) return;
        $('#portada-preview').html(
            '<img src="' + URL.createObjectURL(this.files[0]) + '" alt="Portada">'
        );
        refreshToolbar('nueva');
    });
    $('#edit-portada-input').on('change', function () {
        if (!this.files[0]) return;
        $('#edit-portada-preview').html(
            '<img src="' + URL.createObjectURL(this.files[0]) + '" alt="Portada">'
        );
        refreshToolbar('editar');
    });


    /* ══════════════════════════════════════════════
       7. EDITOR VISUAL
    ══════════════════════════════════════════════ */

    function getEditorEls(ctx) {
        const isEdit = ctx === 'editar';
        return {
            $editor:  isEdit ? $('#edit-receta-editor') : $('#nueva-receta-editor'),
            $toolbar: isEdit ? $('#edit-img-toolbar')    : $('#nueva-img-toolbar'),
            $hidden:  isEdit ? $('#edit-receta-texto')   : $('#nueva-receta-texto'),
        };
    }

    // Toolbar de insertar imagen — usa siempre URL de Cloudinary
    function refreshToolbar(ctx) {
    const { $toolbar } = getEditorEls(ctx);
    const isEdit = ctx === 'editar';
    const imgs   = [];

    const portSrc = (isEdit ? $('#edit-portada-preview') : $('#portada-preview'))
        .find('img').attr('src');

    if (portSrc) imgs.push({ label: 'Portada', src: portSrc });

    for (let n = 1; n <= 3; n++) {
        const $slot = isEdit
            ? $('#edit-extra-slot-' + n)
            : $('#extra-slot-' + n);

        if (!$slot.length) continue;

        const cloudUrl = $slot.data('cloudinary-url');
        const preview  = $slot.find('.slot-preview').attr('src');

        const src = cloudUrl || (preview && preview.indexOf('blob:') === -1 ? preview : null);

        if (src) {
            imgs.push({ label: 'Imagen ' + n, src: src });
        }
    }

    $toolbar.empty();

    if (imgs.length === 0) {
        $toolbar.hide();
        return;
    }

    $toolbar.show();

    imgs.forEach(function (img) {
        const $btn = $('<button type="button" class="insert-img-btn"></button>')
            .html('<img class="btn-img-thumb" src="' + img.src + '"> ' + img.label);

        $btn.on('click', function () {
            const { $editor } = getEditorEls(ctx);
            insertImgInEditor($editor, img.src);
        });

        $toolbar.append($btn);
    });
}

    function insertImgInEditor($editor, src) {
        const $wrap = buildImgWrap(src, 220);
        const sel   = window.getSelection();
        if (sel && sel.rangeCount &&
            $editor[0].contains(sel.getRangeAt(0).commonAncestorContainer)) {
            const range = sel.getRangeAt(0);
            range.deleteContents();
            range.insertNode($wrap[0]);
            range.setStartAfter($wrap[0]);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        } else {
            $editor.append($wrap);
        }
        selectImgWrap($wrap);
        syncHidden($editor);
    }

    function buildImgWrap(src, width) {
        const $wrap = $('<span class="editor-img-wrap" contenteditable="false"></span>');
        const $img  = $('<img>').attr('src', src).css('width', width + 'px');
        const $ft   = $('<span class="img-float-toolbar"></span>');

        const aligns = [
            { l: '←', a: 'align-left',   title: 'Izquierda' },
            { l: '↔', a: 'align-center', title: 'Centro'    },
            { l: '→', a: 'align-right',  title: 'Derecha'   },
        ];
        aligns.forEach(function (al) {
            $('<button type="button"></button>')
                .text(al.l)
                .attr({ title: al.title, 'data-align': al.a })
                .on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $wrap.removeClass('align-left align-center align-right');
                    $wrap.addClass(al.a);
                    $ft.find('[data-align]').removeClass('active-align');
                    $(this).addClass('active-align');
                    syncHidden($wrap.closest('.receta-editor'));
                })
                .appendTo($ft);
        });

        $('<span class="tb-divider"></span>').appendTo($ft);

        const sizes = [{ l: 'S', w: 120 }, { l: 'M', w: 220 }, { l: 'L', w: 360 }, { l: 'XL', w: 500 }];
        sizes.forEach(function (s) {
            $('<button type="button"></button>').text(s.l).on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $img.css('width', s.w + 'px');
                syncHidden($wrap.closest('.receta-editor'));
            }).appendTo($ft);
        });

        $('<span class="tb-divider"></span>').appendTo($ft);

        $('<button type="button" title="Quitar imagen">🗑</button>').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const $ed = $wrap.closest('.receta-editor');
            $wrap.remove();
            syncHidden($ed);
        }).appendTo($ft);

        ['nw', 'ne', 'sw', 'se'].forEach(function (pos) {
            const $h = $('<span class="resize-handle ' + pos + '"></span>');
            makeResizable($wrap, $img, $h, pos);
            $wrap.append($h);
        });

        $wrap.append($ft).append($img);

        $wrap.on('mousedown', function (e) {
            if ($(e.target).hasClass('resize-handle') ||
                $(e.target).closest('.img-float-toolbar').length) return;
            e.preventDefault();
            selectImgWrap($wrap);
        });

        makeDraggable($wrap, $img);
        return $wrap;
    }

    function selectImgWrap($wrap) {
        $('.editor-img-wrap').removeClass('selected');
        $wrap.addClass('selected');
    }

    $(document).on('mousedown', function (e) {
        if (!$(e.target).closest('.editor-img-wrap').length &&
            !$(e.target).closest('.img-insert-toolbar').length) {
            $('.editor-img-wrap').removeClass('selected');
        }
    });

    function makeResizable($wrap, $img, $handle, corner) {
        $handle.on('mousedown', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const startX = e.clientX;
            const startW = $img.width();
            const sign   = (corner === 'se' || corner === 'ne') ? 1 : -1;

            $(document).on('mousemove.resize', function (e) {
                const newW = Math.max(60, startW + sign * (e.clientX - startX));
                $img.css('width', newW + 'px');
            });
            $(document).on('mouseup.resize', function () {
                $(document).off('mousemove.resize mouseup.resize');
                syncHidden($wrap.closest('.receta-editor'));
            });
        });
    }

    function makeDraggable($wrap, $img) {
        let startX, startY, startLeft, startTop;

        $img.on('mousedown', function (e) {
            if ($(e.target).hasClass('resize-handle')) return;
            e.preventDefault();
            selectImgWrap($wrap);

            startX    = e.clientX;
            startY    = e.clientY;
            startLeft = parseInt($wrap.css('left'))  || 0;
            startTop  = parseInt($wrap.css('top'))   || 0;

            $(document).on('mousemove.drag', function (e) {
                $wrap.css({
                    position: 'relative',
                    left: (startLeft + e.clientX - startX) + 'px',
                    top:  (startTop  + e.clientY - startY) + 'px',
                });
            });
            $(document).on('mouseup.drag', function () {
                $(document).off('mousemove.drag mouseup.drag');
                syncHidden($wrap.closest('.receta-editor'));
            });
        });
    }

    function syncHidden($editor) {
        const $hidden = $editor.is('#nueva-receta-editor')
            ? $('#nueva-receta-texto')
            : $('#edit-receta-texto');
        $hidden.val($editor.html());
    }

    $(document).on('input', '.receta-editor', function () {
        syncHidden($(this));
    });

    function loadEditorContent($editor, content) {
        if (!content) { $editor.empty(); return; }

        if (content.indexOf('editor-img-wrap') !== -1) {
            $editor.html(content);
        } else {
            const html = '<p>' + content
                .replace(/&/g,  '&amp;')
                .replace(/</g,  '&lt;')
                .replace(/>/g,  '&gt;')
                .replace(/\n\n+/g, '</p><p>')
                .replace(/\n/g, '<br>') + '</p>';
            $editor.html(html);
        }

        $editor.find('.editor-img-wrap').each(function () {
            const $wrap = $(this);
            const $img  = $wrap.find('img');

            $wrap.off('mousedown').on('mousedown', function (e) {
                if ($(e.target).hasClass('resize-handle') ||
                    $(e.target).closest('.img-float-toolbar').length) return;
                e.preventDefault();
                selectImgWrap($wrap);
            });

            makeDraggable($wrap, $img);

            $wrap.find('.resize-handle').each(function () {
                const corner = $(this).attr('class').split(' ').pop();
                makeResizable($wrap, $img, $(this), corner);
            });

            $wrap.find('.img-float-toolbar [data-align]').each(function () {
                const al = $(this).attr('data-align');
                $(this).off('click').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $wrap.removeClass('align-left align-center align-right');
                    $wrap.addClass(al);
                    $wrap.find('[data-align]').removeClass('active-align');
                    $(this).addClass('active-align');
                    syncHidden($editor);
                });
                if ($wrap.hasClass(al)) $(this).addClass('active-align');
            });

            const sizePx = [120, 220, 360, 500];
            $wrap.find('.img-float-toolbar button:not([data-align])').each(function (i) {
                $(this).off('click');
                if (i < sizePx.length) {
                    const w = sizePx[i];
                    $(this).on('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $img.css('width', w + 'px');
                        syncHidden($editor);
                    });
                } else {
                    $(this).on('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $wrap.remove();
                        syncHidden($editor);
                    });
                }
            });
        });
    }


    /* ══════════════════════════════════════════════
       8. LIGHTBOX
    ══════════════════════════════════════════════ */
    const $lb = $('#lightbox');

    $(document).on('click', '.slot-preview', function (e) {
        e.stopPropagation();
        const src = $(this).attr('src');
        if (!src) return;
        $('#lightbox-img').attr('src', src);
        $lb.css('display', 'flex');
        $('body').css('overflow', 'hidden');
    });

    function closeLightbox() { $lb.hide(); $('body').css('overflow', ''); }
    $('#lightbox-close').on('click', closeLightbox);
    $lb.on('click', function (e) {
        if (!$(e.target).is('#lightbox-img')) closeLightbox();
    });


    /* ══════════════════════════════════════════════
       9. TAGS
    ══════════════════════════════════════════════ */
    const tags = [];

    function updateTagsHidden() { $('#tags-hidden').val(JSON.stringify(tags)); }

    function addTag(name) {
        name = name.trim();
        if (!name || tags.includes(name)) return;
        tags.push(name);
        const $chip = $('<span class="tag-chip"></span>').text(name).append(
            $('<button type="button">×</button>').on('click', function () {
                tags.splice(tags.indexOf(name), 1);
                $chip.remove();
                updateTagsHidden();
            })
        );
        $('#tags-chips').append($chip);
        updateTagsHidden();
    }

    $('#tag-input')
        .on('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTag($(this).val());
                $(this).val('');
            }
        })
        .on('blur', function () {
            if ($(this).val().trim()) { addTag($(this).val()); $(this).val(''); }
        });

    $('#tags-input-wrap').on('click', function () { $('#tag-input').focus(); });


    /* ══════════════════════════════════════════════
       10. MODAL EDITAR
    ══════════════════════════════════════════════ */
    const $modal = $('#modal-editar');

    function openModal() {
        $modal.addClass('open').attr('aria-hidden', 'false');
        $('body').css('overflow', 'hidden');
    }

    function closeModal() {
        $modal.removeClass('open').attr('aria-hidden', 'true');
        $('body').css('overflow', '');
        $('#edit-recipe-id,#edit-titulo,#edit-utensilios').val('');
        $('#edit-descripcion,#edit-receta-texto').val('');
        $('#edit-portada-preview').html('<div class="upload-placeholder"><span>📷</span></div>');
        $('#edit-portada-input').val('');
        $('#edit-img-toolbar').hide().empty();
        $('#edit-receta-editor').empty();
        resetEditSlots();
    }

    function resetEditSlots() {
        [1, 2, 3].forEach(function (n) {
            const $s = $('#edit-extra-slot-' + n);
            $s.removeData('img-id')
              .removeData('cloudinary-url')
              .removeData('uploading');
            $s.find('input[type=file]').val('');
            $s.find('.slot-preview').attr('src', '').css('opacity', '1');
            $s.find('.slot-preview-wrap').hide();
            $s.removeClass('has-image');
            $s.find('.delete-img-flag').prop('disabled', true).val('');
        });
    }

    /* ── MODAL EDITAR — CARGA RECETA ── */
$(document).on('click', '.btn-editar', function () {
    const recipeId = $(this).data('recipe-id');

    openModal();
    showLoading();

    $.get(BASE_URL + '/site/get-recipe', { id: recipeId })
        .done(function (res) {

            if (typeof res === 'string') {
                try {
                    res = JSON.parse(res);
                } catch (e) {
                    console.error('Respuesta no JSON:', res);
                    alert('Error de servidor');
                    closeModal();
                    return;
                }
            }

            if (!res || !res.success || res.error) {
                alert('No se pudo cargar la receta');
                closeModal();
                return;
            }

            $('#edit-recipe-id').val(res.id);
            $('#edit-titulo').val(res.titulo);
            $('#edit-descripcion').val(res.descripcion);
            $('#edit-utensilios').val(res.utensilios || '');
            $('#edit-receta-texto').val(res.receta_texto || '');

            if (res.imagen_portada_url) {
                $('#edit-portada-preview').html(
                    '<img src="' + res.imagen_portada_url + '">'
                );
            }

            resetEditSlots();

            if (res.images && res.images.length) {
                res.images.forEach(function (img) {
                    const $slot = $('#edit-extra-slot-' + img.posicion);
                    if (!$slot.length) return;

                    $slot.data('img-id', img.id);
                    $slot.data('cloudinary-url', img.image_url);

                    $slot.find('.slot-preview').attr('src', img.image_url);
                    $slot.find('.slot-preview-wrap').show();
                    $slot.addClass('has-image');
                });
            }

            loadEditorContent($('#edit-receta-editor'), res.receta_texto);
            setTimeout(() => refreshToolbar('editar'), 100);
        })
        .fail(function (xhr) {
            console.error('[GET-RECIPE] Error:', xhr.status, xhr.responseText);
            alert('Error cargando receta: ' + xhr.status);
            closeModal();
        })
        .always(hideLoading);
});

    $('#modal-close-btn, #modal-close-btn-2').on('click', closeModal);
    $modal.on('click', function (e) { if ($(e.target).is($modal)) closeModal(); });
    $(document).on('keydown', function (e) {
        if (e.key !== 'Escape') return;
        closeModal();
        closeLightbox();
        cerrarModalBorrar();
    });


    /* ══════════════════════════════════════════════
       11. SUBMIT NUEVA RECETA
       FIX: inyectar URLs de Cloudinary de slots extra
    ══════════════════════════════════════════════ */
    $('#nueva-receta-form').on('submit', function (e) {
        // Verificar que no haya imágenes subiendo
        let uploading = false;
        $('[data-slot]').closest('.multi-thumb').each(function () {
            if ($(this).data('uploading')) uploading = true;
        });
        if (uploading) {
            e.preventDefault();
            alert('Espera a que terminen de subirse las imágenes.');
            return;
        }

        // ✅ FIX: limpiar campos previos e inyectar URLs de Cloudinary
        $(this).find('.cloudinary-url-hidden').remove();
        [1, 2, 3].forEach(function (n) {
            const $slot = $('#extra-slot-' + n);
            const url   = $slot.data('cloudinary-url');
            if (url) {
                $('<input type="hidden">')
                    .addClass('cloudinary-url-hidden')
                    .attr('name', 'Recipe[cloudinary_urls][' + n + ']')
                    .val(url)
                    .appendTo('#nueva-receta-form');
            }
        });

        syncHidden($('#nueva-receta-editor'));
        showLoading();
    });


    /* ══════════════════════════════════════════════
       12. SUBMIT EDITAR
       FIX: enviar URLs de Cloudinary de slots nuevos
    ══════════════════════════════════════════════ */
    $(document).on('submit', '#edit-recipe-form', function (e) {
        e.preventDefault();

        // Verificar que no haya imágenes subiendo
        let uploading = false;
        $('#edit-multi-upload .multi-thumb').each(function () {
            if ($(this).data('uploading')) uploading = true;
        });
        if (uploading) {
            alert('Espera a que terminen de subirse las imágenes.');
            return;
        }

        syncHidden($('#edit-receta-editor'));
        const fd   = new FormData(this);

        // ✅ FIX: incluir en FormData las URLs de Cloudinary de slots NUEVOS
        // (los que tienen cloudinary-url pero NO tienen img-id, es decir, recién subidos)
        [1, 2, 3].forEach(function (n) {
            const $slot = $('#edit-extra-slot-' + n);
            const url   = $slot.data('cloudinary-url');
            const imgId = $slot.data('img-id');
            // Solo agregar si es una imagen nueva (sin registro previo en BD)
            if (url && !imgId) {
                fd.append('Recipe[cloudinary_urls][' + n + ']', url);
            }
        });

        const $btn = $(this).find('[type=submit]').prop('disabled', true).text('Guardando…');
        showLoading();

        $.ajax({
            url:         BASE_URL + '/site/actualizar-receta',
            type:        'POST',
            data:        fd,
            xhrFields: { withCredentials: true },
            processData: false,
            contentType: false,
            headers:     { 'X-CSRF-Token': CSRF_TOKEN },
            success: function (r) {
                if (r.success) {
                    closeModal();
                    location.reload();
                } else {
                    alert(r.message || 'Error.');
                    $btn.prop('disabled', false).text('Guardar cambios');
                }
            },
            error: function (xhr) {
                console.error('[ACTUALIZAR-RECETA] Error:', xhr.status, xhr.responseText);
                alert('Error de conexión. Código: ' + xhr.status);
                $btn.prop('disabled', false).text('Guardar cambios');
            }
        }).always(hideLoading);
    });

});