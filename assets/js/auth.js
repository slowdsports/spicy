/**
 * StreamHub - JS de autenticación (?p=login)
 */

function switchTab(tab) {
  document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
  document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
  document.getElementById('form-login').style.display    = tab === 'login'    ? 'block' : 'none';
  document.getElementById('form-register').style.display = tab === 'register' ? 'block' : 'none';
  clearAlerts();
}

function validateLogin() {
  const email = document.getElementById('login-email').value.trim();
  const pass  = document.getElementById('login-password').value;
  if (!email || !pass) { showAlert('login', 'Completa todos los campos.', 'error'); return false; }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showAlert('login', 'Email no válido.', 'error'); return false; }
  return true;
}

function validateRegister() {
  const name  = document.getElementById('reg-name').value.trim();
  const email = document.getElementById('reg-email').value.trim();
  const pass  = document.getElementById('reg-password').value;
  const conf  = document.getElementById('reg-confirm').value;
  if (!name || !email || !pass || !conf) { showAlert('register', 'Completa todos los campos.', 'error'); return false; }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showAlert('register', 'Email no válido.', 'error'); return false; }
  if (pass.length < 6) { showAlert('register', 'Mínimo 6 caracteres.', 'error'); return false; }
  if (pass !== conf) { showAlert('register', 'Las contraseñas no coinciden.', 'error'); return false; }
  return true;
}

async function submitLogin() {
  if (!validateLogin()) return;
  const btn = document.getElementById('btn-login-submit');
  btn.textContent = 'Iniciando...'; btn.disabled = true;
  try {
    const res = await fetch('api/auth.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'login', email: document.getElementById('login-email').value.trim(), password: document.getElementById('login-password').value }) });
    const result = await res.json();
    if (result.success) {
      showAlert('login', '¡Bienvenido!', 'success');
      setTimeout(() => { window.location.href = '?p=home'; }, 1000);
    } else {
      showAlert('login', result.message || 'Credenciales incorrectas.', 'error');
      btn.textContent = 'Iniciar sesión'; btn.disabled = false;
    }
  } catch {
    showAlert('login', 'Modo demo: backend PHP requerido.', 'error');
    btn.textContent = 'Iniciar sesión'; btn.disabled = false;
  }
}

async function submitRegister() {
  if (!validateRegister()) return;
  const btn = document.getElementById('btn-register-submit');
  btn.textContent = 'Creando...'; btn.disabled = true;
  try {
    const res = await fetch('api/auth.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'register', name: document.getElementById('reg-name').value.trim(), email: document.getElementById('reg-email').value.trim(), password: document.getElementById('reg-password').value }) });
    const result = await res.json();
    if (result.success) {
      showAlert('register', '¡Cuenta creada!', 'success');
      setTimeout(() => { window.location.href = '?p=home'; }, 1000);
    } else {
      showAlert('register', result.message || 'Error al crear cuenta.', 'error');
      btn.textContent = 'Crear cuenta'; btn.disabled = false;
    }
  } catch {
    showAlert('register', 'Modo demo: backend PHP requerido.', 'error');
    btn.textContent = 'Crear cuenta'; btn.disabled = false;
  }
}

function showAlert(form, message, type) {
  const el = document.getElementById(`alert-${form}`);
  if (!el) return;
  el.textContent = message;
  el.className = `alert-sh alert-${type}`;
  el.style.display = 'block';
}

function clearAlerts() {
  document.querySelectorAll('.alert-sh').forEach(a => a.style.display = 'none');
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.auth-tab').forEach(tab => {
    tab.addEventListener('click', () => switchTab(tab.dataset.tab));
  });
  const params = new URLSearchParams(window.location.search);
  if (params.get('tab') === 'register') switchTab('register');
});
