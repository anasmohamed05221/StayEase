const params = new URLSearchParams(window.location.search);
const error = params.get('error');
const msg = document.getElementById('error-msg');

if (error === 'mismatch') msg.textContent = 'Passwords do not match.';
if (error === 'email_taken') msg.textContent = 'Email already registered.';