const params = new URLSearchParams(window.location.search);
const error = params.get('error');
const msg = document.getElementById('msg');

if (error === 'mismatch') msg.textContent = 'Passwords do not match.';
if (error === 'email_taken') msg.textContent = 'Email already registered.';

document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});