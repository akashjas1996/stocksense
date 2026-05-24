// Auto-dismiss flash messages after 4 seconds
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert.alert-success').forEach(el => {
        setTimeout(() => el.classList.remove('show'), 4000);
    });
});
