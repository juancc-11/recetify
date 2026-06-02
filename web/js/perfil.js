document.addEventListener("DOMContentLoaded", () => {

    // ── TOGGLE VER CONTRASEÑA (ojo en texto) ──
    document.querySelectorAll('.pf-eye-toggle').forEach(toggle => {
        toggle.addEventListener('click', () => {
            const targetId = toggle.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            toggle.textContent = input.type === 'password' ? '👁' : '🙈';
        });
    });

    // ── MODAL ELIMINAR CUENTA ──
    const deleteBtn = document.getElementById('pf-delete-btn');
    const modalDelete = document.getElementById('pf-modal-delete');
    const modalCancelBtn = document.getElementById('pf-modal-cancel');

    if (deleteBtn && modalDelete) {
        deleteBtn.addEventListener('click', () => {
            modalDelete.classList.remove('pf-modal-hidden');
        });
        modalCancelBtn.addEventListener('click', () => {
            modalDelete.classList.add('pf-modal-hidden');
        });
        modalDelete.addEventListener('click', (e) => {
            if (e.target === modalDelete) modalDelete.classList.add('pf-modal-hidden');
        });
    }

    // ── MODAL CAMBIAR FOTO ──
    const avatarWrap = document.getElementById('pf-avatar-wrap');
    const avatarInput = document.getElementById('pf-avatar-input');
    const preview = document.getElementById('pf-preview');
    const modalPhoto = document.getElementById('pf-modal-photo');
    const photoYes = document.getElementById('pf-photo-yes');
    const photoNo = document.getElementById('pf-photo-no');

    if (avatarWrap && modalPhoto) {
        avatarWrap.addEventListener('click', () => {
            modalPhoto.classList.remove('pf-modal-hidden');
        });
        photoNo.addEventListener('click', () => {
            modalPhoto.classList.add('pf-modal-hidden');
        });
        photoYes.addEventListener('click', () => {
            modalPhoto.classList.add('pf-modal-hidden');
            avatarInput.click();
        });
        modalPhoto.addEventListener('click', (e) => {
            if (e.target === modalPhoto) modalPhoto.classList.add('pf-modal-hidden');
        });

        // ✅ Preview inmediato al seleccionar imagen
        avatarInput.addEventListener('change', () => {
            const file = avatarInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => { preview.src = e.target.result; };
                reader.readAsDataURL(file);
            }
        });
    }
});