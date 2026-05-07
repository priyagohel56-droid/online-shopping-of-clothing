// ============================================================
// script.js - StyleHub Frontend Scripts
// ============================================================

// --- Toast Notification ---
function showToast(message) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = '✅ ' + message;
    toast.classList.remove('hidden');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);
}

// --- Register form client-side validation ---
function validateRegister() {
    const password = document.getElementById('password');
    const confirm  = document.getElementById('confirm_password');
    if (!password || !confirm) return true;

    if (password.value.length < 6) {
        alert('Password must be at least 6 characters.');
        return false;
    }
    if (password.value !== confirm.value) {
        alert('Passwords do not match!');
        return false;
    }
    return true;
}

// --- Auto-hide alerts after 4 seconds ---
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function () { alert.style.display = 'none'; }, 500);
        }, 4000);
    });
});
