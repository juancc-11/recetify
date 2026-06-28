document.addEventListener("click", function (e) {

    // ══════════════════════════════════════════════════════
    // 1. Toggle Ban (AJAX)
    // ══════════════════════════════════════════════════════
    if (e.target.classList.contains("btn-ban-toggle")) {
        const btn    = e.target;
        const id     = btn.dataset.id;
        const action = btn.dataset.action;
        const card   = btn.closest(".card");

        const titulo = action === 'ban' ? '¿Bloquear usuario?' : '¿Desbloquear usuario?';
        const color  = action === 'ban' ? '#d33' : '#2ecc71';

        Swal.fire({
            title: titulo,
            text: '¿Estás seguro de que deseas realizar esta acción?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: color,
            cancelButtonColor: '#7f8c8d',
            confirmButtonText: 'Sí, proceder',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            // Animación de carga
            Swal.fire({
                title: 'Procesando...',
                text: action === 'ban' ? 'Baneando usuario...' : 'Desbaneando usuario...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(TOGGLE_BAN_URL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-CSRF-Token": yii.getCsrfToken()
                },
                body: `id=${id}&action=${action}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Listo!',
                        text: 'El estado del usuario ha sido actualizado.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    card.style.transition  = "0.3s";
                    card.style.opacity     = "0";
                    card.style.transform   = "scale(0.8)";
                    setTimeout(() => card.remove(), 300);
                } else {
                    Swal.fire('Error', 'No se pudo procesar la solicitud.', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error de red', 'Hubo un problema con la conexión.', 'error');
            });
        });

        return;
    }

    // ══════════════════════════════════════════════════════
    // 2. Botones de confirmación (formularios normales)
    //    kick / ban / delete-recipe / dismiss-report
    // ══════════════════════════════════════════════════════
    const botonConfirm = e.target.closest('.btn-confirm');
    if (botonConfirm) {
        e.preventDefault();

        const mensaje   = botonConfirm.getAttribute('data-mensaje') || '¿Estás seguro?';
        const tipoIcono = botonConfirm.getAttribute('data-tipo')    || 'question';
        const form      = botonConfirm.closest('form');

        // Detectar qué acción es para personalizar el mensaje de carga
        const action = form ? form.action : '';
        let loadingText = 'Procesando...';
        if (action.includes('kick'))           loadingText = 'Enviando advertencia...';
        else if (action.includes('ban'))       loadingText = 'Baneando usuario...';
        else if (action.includes('delete'))    loadingText = 'Eliminando receta...';
        else if (action.includes('dismiss'))   loadingText = 'Descartando reporte...';

        Swal.fire({
            title: 'Confirmar acción',
            text: mensaje,
            icon: tipoIcono,
            showCancelButton: true,
            confirmButtonColor: tipoIcono === 'error' ? '#d33' : '#f39c12',
            cancelButtonColor: '#7f8c8d',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            // Animación de carga antes de enviar el form
            Swal.fire({
                title: loadingText,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            // Pequeño delay para que se vea el loading antes del submit
            setTimeout(() => {
                if (form) form.submit();
            }, 600);
        });

        return;
    }
});

// ══════════════════════════════════════════════════════
// 3. Búsqueda AJAX con animación de carga en el grid
// ══════════════════════════════════════════════════════
const input  = document.getElementById("searchInput");
const filter = document.getElementById("filter");

let timeout = null;

function showGridLoading() {
    const grid = document.querySelector(".grid");
    if (!grid) return;
    grid.style.opacity    = "0.4";
    grid.style.pointerEvents = "none";
}

function hideGridLoading() {
    const grid = document.querySelector(".grid");
    if (!grid) return;
    grid.style.opacity    = "1";
    grid.style.pointerEvents = "auto";
}

function fetchData() {
    if (!input || !filter) return;

    const search = input.value.trim();
    const tab    = new URLSearchParams(window.location.search).get("tab") || "users";
    const order  = filter.value;

    let url = SEARCH_URL;
    url += url.includes('?')
        ? `&tab=${tab}&search=${encodeURIComponent(search)}&order=${order}`
        : `?tab=${tab}&search=${encodeURIComponent(search)}&order=${order}`;

    showGridLoading();

    fetch(url)
        .then(res => res.json())
        .then(data => {
            hideGridLoading();
            if (data.success) {
                const grid = document.querySelector(".grid");
                if (grid) {
                    grid.style.transition = "opacity 0.2s";
                    grid.innerHTML        = data.html;
                }
            }
        })
        .catch(() => {
            hideGridLoading();
            console.error("Error en búsqueda AJAX");
        });
}

if (input) {
    input.addEventListener("input", () => {
        clearTimeout(timeout);
        timeout = setTimeout(fetchData, 300);
    });
}

if (filter) {
    filter.addEventListener("change", fetchData);
}

const btnSearch = document.querySelector(".btn-search");
if (btnSearch) {
    btnSearch.addEventListener("click", function (e) {
        e.preventDefault();
        fetchData();
    });
}
