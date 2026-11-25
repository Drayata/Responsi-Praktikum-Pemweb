// assets/js/script.js - BagiPangan Native JavaScript

function showToast(message, duration = 3200) {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('on');
    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(() => {
        toast.classList.remove('on');
    }, duration);
}

// Preview gambar sebelum diupload
function setupImagePreview(inputId, previewImgId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewImgId);
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

// Konfirmasi hapus data
function confirmAction(message, url) {
    if (confirm(message || 'Apakah kamu yakin ingin melanjutkan?')) {
        window.location.href = url;
    }
}

// Toggle menu mobile
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('navToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    if (toggleBtn && mobileMenu) {
        toggleBtn.addEventListener('click', function () {
            const isOpen = mobileMenu.classList.toggle('open');
            toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }
});
