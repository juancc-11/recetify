// ================= PASSWORD STRENGTH =================
const password = document.getElementById('password');
const strength = document.getElementById('strength');

if (password) {
    password.addEventListener('input', () => {
        const val = password.value;

        let level = "Débil";
        let color = "red";

        const hasUpper = /[A-Z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        const hasSymbol = /[^A-Za-z0-9]/.test(val);

        if (val.length >= 10 && hasUpper && hasNumber && hasSymbol) {
            level = "Fuerte";
            color = "green";
        } else if (val.length >= 6 && (hasUpper || hasNumber)) {
            level = "Media";
            color = "orange";
        }

        strength.innerHTML = "Seguridad: " + level;
        strength.style.color = color;
    });
}

// ================= PREVIEW IMAGEN =================
const input = document.getElementById('avatarInput');
const preview = document.getElementById('preview');

if (input) {
    input.addEventListener('change', function () {
        const file = this.files[0];

        if (file) {
            const reader = new FileReader();

            reader.addEventListener('load', function () {
                preview.setAttribute('src', this.result);
            });

            reader.readAsDataURL(file);
        }
    });
}