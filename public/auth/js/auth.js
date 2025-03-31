// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const eyeIcon = document.querySelector(".eye-icon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.style.opacity = "1";
    } else {
        passwordInput.type = "password";
        eyeIcon.style.opacity = "0.5";
    }
}

// Auto-hide messages after 5 seconds
document.addEventListener("DOMContentLoaded", function () {
    const messages = document.querySelectorAll(
        ".success-message, .error-message"
    );
    messages.forEach((message) => {
        setTimeout(() => {
            message.style.opacity = "0";
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
});

// Phone number formatting
document.addEventListener("DOMContentLoaded", function () {
    const teleponInput = document.getElementById("telepon");
    if (teleponInput) {
        teleponInput.addEventListener("input", function (e) {
            let x = e.target.value
                .replace(/\D/g, "")
                .match(/(\d{0,5})(\d{0,4})(\d{0,5})/);
            e.target.value = !x[2]
                ? x[1]
                : `${x[1]}-${x[2]}` + (x[3] ? `-${x[3]}` : ``);
        });
    }

    document.querySelector("form").addEventListener("submit", function (event) {
        let value = teleponInput.value.replace(/\D/g, ""); // Hapus "-" dan spasi

        if (!(value.startsWith("08") || value.startsWith("628"))) {
            event.preventDefault();
            alert("Masukan Nomor Telepon yang Benar");
            return;
        }

        // Ubah awalan 0 menjadi 62
        if (value.startsWith("08")) {
            value = "62" + value.substring(1);
        }

        teleponInput.value = value; // Set nilai input yang sudah diformat
    });
});
