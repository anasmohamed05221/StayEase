const params = new URLSearchParams(window.location.search);
const error = params.get('error');
const success = params.get('success');
const msg = document.getElementById('error-msg');

if (error === 'invalid') msg.textContent = 'Wrong email or password.';

if (success === 'registered') {
    msg.style.color = 'var(--success)';
    msg.textContent = 'Account created! Please log in.';
}