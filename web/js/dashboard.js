document.addEventListener("click", function (e) {
    // 1. Manejo de Toggle Ban (Fetch / AJAX)
    if (e.target.classList.contains("btn-ban-toggle")) {
        const btn = e.target;
        const id = btn.dataset.id;
        const action = btn.dataset.action; // Supongo que aquí viene 'ban' o 'unban'
        const card = btn.closest(".card");

        // Personalizamos el mensaje según la acción
        const titulo = action === 'ban' ? '¿Bloquear usuario?' : '¿Desbloquear usuario?';
        const color = action === 'ban' ? '#d33' : '#2ecc71';

        Swal.fire({
            title: titulo,
            text: `¿Estás seguro de que deseas realizar esta acción?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: color,
            cancelButtonColor: '#7f8c8d',
            confirmButtonText: 'Sí, proceder',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, ejecutamos el fetch
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
                        // 🎬 Animación y feedback
                        Swal.fire({
                            title: '¡Listo!',
                            text: 'El estado del usuario ha sido actualizado.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        card.style.transition = "0.3s";
                        card.style.opacity = "0";
                        card.style.transform = "scale(0.8)";
                        setTimeout(() => card.remove(), 300);
                    } else {
                        Swal.fire('Error', 'No se pudo procesar la solicitud.', 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error de red', 'Hubo un problema con la conexión.', 'error');
                });
            }
        });
    }

    // 2. Manejo de Botones de Confirmación (Formularios Normales)
    const botonConfirm = e.target.closest('.btn-confirm');
    if (botonConfirm) {
        e.preventDefault();
        const mensaje = botonConfirm.getAttribute('data-mensaje') || '¿Estás seguro?';
        const tipoIcono = botonConfirm.getAttribute('data-tipo') || 'question';

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
            if (result.isConfirmed) {
                botonConfirm.closest('form').submit();
            }
        });
    }
});

const input = document.getElementById("searchInput");
const filter = document.getElementById("filter");

let timeout = null;

function fetchData() {

    if (!input || !filter) return;

    let search = input.value.trim();
    let tab = new URLSearchParams(window.location.search).get("tab") || "users";
    let order = filter.value;

    let url = SEARCH_URL;

    // ✅ Detectar si ya tiene ?
    if (url.includes('?')) {
        url += `&tab=${tab}&search=${search}&order=${order}`;
    } else {
        url += `?tab=${tab}&search=${search}&order=${order}`;
    }

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.querySelector(".grid").innerHTML = data.html;
            }
        })
        .catch(() => {
            console.error("Error en búsqueda AJAX");
        });
}

input.addEventListener("input", () => {
    clearTimeout(timeout);
    timeout = setTimeout(fetchData, 300);
});

filter.addEventListener("change", fetchData);

document.querySelector(".btn-search").addEventListener("click", function (e) {
    e.preventDefault();
});
