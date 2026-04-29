const params = new URLSearchParams(window.location.search);
const error = params.get('error');
const success = params.get('success');
const msg = document.getElementById('msg');

if (error === 'invalid') {
    msg.style.color = 'var(--danger)'
    msg.textContent = 'Wrong email or password!';
}

if (success === 'registered') {
    msg.style.color = 'var(--success)';
    msg.textContent = 'Account created! Please log in.';
}

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