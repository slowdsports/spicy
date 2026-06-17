/**
 * StreamHub - JS de autenticación (?p=login)
 */

// ── Login / Register normal ───────────────────────────────────────────────

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

async function apiPost(payload) {
  let res = await fetch('api/auth.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(payload),
  });

  if (res.status === 403) {
    const form = new URLSearchParams();
    Object.entries(payload).forEach(([k, v]) => form.append(k, v));
    res = await fetch('api/auth.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    form.toString(),
    });
  }

  const text = await res.text();
  try {
    return JSON.parse(text);
  } catch {
    console.error('Respuesta no-JSON de auth.php:', text.slice(0, 300));
    return { success: false, message: 'Error del servidor (' + res.status + ')' };
  }
}

async function submitLogin() {
  if (!validateLogin()) return;
  const btn = document.getElementById('btn-login-submit');
  btn.textContent = 'Iniciando...'; btn.disabled = true;
  try {
    const result = await apiPost({
      action:   'login',
      email:    document.getElementById('login-email').value.trim(),
      password: document.getElementById('login-password').value,
    });
    if (result.success) {
      // Si hay un token de TV pendiente, aprobarlo antes de redirigir
      if (window.IS_MOBILE_AUTH && window.TV_TOKEN) {
        showAlert('login', 'Sesión iniciada. Autorizando TV…', 'success');
        await approveTvLogin(true);
      } else {
        showAlert('login', '¡Bienvenido!', 'success');
        setTimeout(() => { window.location.href = getRedirectDest(); }, 1000);
      }
    } else {
      showAlert('login', result.message || 'Credenciales incorrectas.', 'error');
      btn.textContent = window.IS_MOBILE_AUTH ? 'Iniciar sesión y autorizar TV' : 'Iniciar sesión';
      btn.disabled = false;
    }
  } catch (e) {
    showAlert('login', 'Error de conexión. Verifica que el servidor está activo.', 'error');
    btn.textContent = window.IS_MOBILE_AUTH ? 'Iniciar sesión y autorizar TV' : 'Iniciar sesión';
    btn.disabled = false;
  }
}

async function submitRegister() {
  if (!validateRegister()) return;
  const btn = document.getElementById('btn-register-submit');
  btn.textContent = 'Creando...'; btn.disabled = true;
  try {
    const result = await apiPost({
      action:   'register',
      name:     document.getElementById('reg-name').value.trim(),
      email:    document.getElementById('reg-email').value.trim(),
      password: document.getElementById('reg-password').value,
    });
    if (result.success) {
      showAlert('register', '¡Cuenta creada!', 'success');
      setTimeout(() => { window.location.href = getRedirectDest(); }, 1000);
    } else {
      showAlert('register', result.message || 'Error al crear cuenta.', 'error');
      btn.textContent = 'Crear cuenta'; btn.disabled = false;
    }
  } catch (e) {
    showAlert('register', 'Error de conexión. Verifica que el servidor está activo.', 'error');
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

function getRedirectDest() {
  const redirect = new URLSearchParams(window.location.search).get('redirect') || '';
  return /^[?/]/.test(redirect) ? redirect : '?p=home';
}

// ── Modo TV: QR Login ─────────────────────────────────────────────────────

async function initTvMode() {
  try {
    const res  = await fetch(window.TV_API_BASE + '?action=create');
    const data = await res.json();
    if (!data.success) { tvSetStatus('error', 'No se pudo generar el código. Recarga la página.'); return; }

    const token      = data.data.token;
    const expiresIn  = data.data.expires_in || 600;
    const qrUrl      = window.TV_SCAN_BASE + token;

    // Generar QR
    const qrEl = document.getElementById('tv-qr');
    new QRCode(qrEl, {
      text:         qrUrl,
      width:        256,
      height:       256,
      colorDark:    '#000000',
      colorLight:   '#ffffff',
      correctLevel: QRCode.CorrectLevel.M,
    });

    document.getElementById('tv-qr-loading').style.display = 'none';
    qrEl.style.display = 'block';

    // Temporizador de cuenta regresiva
    let remaining = expiresIn;
    const countdownEl = document.getElementById('tv-countdown');

    const countdownId = setInterval(() => {
      remaining--;
      const m = Math.floor(remaining / 60);
      const s = remaining % 60;
      if (countdownEl) countdownEl.textContent = `${m}:${String(s).padStart(2, '0')}`;
      if (remaining <= 0) {
        clearInterval(countdownId);
        clearInterval(pollId);
        tvSetStatus('expired', 'Código expirado. Recargando…');
        setTimeout(() => location.reload(), 3000);
      }
    }, 1000);

    // Polling cada 2.5 s
    const pollId = setInterval(async () => {
      try {
        const pr   = await fetch(`${window.TV_API_BASE}?action=poll&token=${token}`);
        const pd   = await pr.json();
        const st   = pd.data?.status;

        if (st === 'approved') {
          clearInterval(pollId);
          clearInterval(countdownId);
          tvSetStatus('ok', '¡Autorizado! Iniciando sesión…');
          setTimeout(() => {
            window.location.href = `${window.TV_API_BASE}?action=auth&token=${token}`;
          }, 1200);
        } else if (st === 'expired' || st === 'not_found') {
          clearInterval(pollId);
          clearInterval(countdownId);
          tvSetStatus('expired', 'Código expirado. Recargando…');
          setTimeout(() => location.reload(), 3000);
        }
      } catch (_) { /* error de red — reintentar en el próximo ciclo */ }
    }, 2500);

  } catch (e) {
    tvSetStatus('error', 'Error de conexión. Recarga la página.');
  }
}

function tvSetStatus(type, text) {
  const box = document.getElementById('tv-status-box');
  if (!box) return;
  box.className = 'tv-status-box' + (type === 'ok' ? ' status-ok' : type === 'expired' || type === 'error' ? ' status-expired' : '');
  const icons = { ok: 'fa-check-circle', expired: 'fa-times-circle', error: 'fa-exclamation-circle', pending: 'fa-clock' };
  box.innerHTML = `<i class="fas ${icons[type] || icons.pending} me-2"></i><span>${text}</span>`;
}

// ── Modo TV: alternar entre QR y formulario de credenciales ──────────────

function tvToggleCredentials() {
  const form       = document.getElementById('tv-cred-form');
  const toggle     = document.getElementById('tv-cred-toggle');
  const qrBox      = document.getElementById('tv-qr-box');
  const steps      = document.querySelector('.tv-steps');
  const statusBox  = document.getElementById('tv-status-box');
  const cdRow      = document.getElementById('tv-countdown-row');
  const sub        = document.getElementById('tv-login-sub');

  const showForm = form.style.display === 'none';

  form.style.display      = showForm ? 'block' : 'none';
  if (qrBox)    qrBox.style.display    = showForm ? 'none' : '';
  if (steps)    steps.style.display    = showForm ? 'none' : '';
  if (statusBox) statusBox.style.display = showForm ? 'none' : '';
  if (cdRow)    cdRow.style.display    = showForm ? 'none' : '';
  if (sub)      sub.textContent        = showForm ? 'Inicia sesión con tu cuenta' : 'Inicia sesión con tu celular';

  toggle.innerHTML = showForm
    ? '<i class="fas fa-qrcode me-2"></i>Usar código QR'
    : '<i class="fas fa-keyboard me-2"></i>Iniciar sesión con contraseña';

  if (showForm) {
    const emailInput = document.getElementById('login-email');
    if (emailInput) emailInput.focus();
  }
}

// ── Modo Móvil Auth: autorizar TV desde el celular ────────────────────────

async function approveTvLogin(afterLogin = false) {
  const btn = document.getElementById('btn-tv-approve');
  if (btn) { btn.disabled = true; btn.textContent = 'Autorizando…'; }

  // Usar cualquier div de alerta disponible en la página actual
  const alertEl = document.getElementById('mobile-auth-alert')
               || document.getElementById('alert-login');

  function showApproveAlert(msg, type) {
    if (!alertEl) return;
    alertEl.textContent  = msg;
    alertEl.className    = 'alert-sh alert-' + type;
    alertEl.style.display = 'block';
  }

  try {
    const res  = await fetch(`${window.TV_API_BASE}?action=approve&token=${window.TV_TOKEN}`, {
      method: 'POST',
      credentials: 'same-origin',
    });
    const data = await res.json();

    if (data.success) {
      showApproveAlert('✅ ¡TV autorizada! Ya puedes usar tu Smart TV.', 'success');
      if (btn) {
        btn.textContent = '¡TV autorizada!';
        const cancelLink = document.querySelector('a[href*="p=home"]');
        if (cancelLink) cancelLink.style.display = 'none';
      }
      if (afterLogin) {
        setTimeout(() => { window.location.href = '?p=home'; }, 1500);
      }
    } else {
      showApproveAlert(data.message || 'No se pudo autorizar. El código puede haber expirado.', 'error');
      if (btn) { btn.disabled = false; btn.textContent = 'Autorizar mi TV'; }
      if (afterLogin) {
        // Login fue exitoso, igual redirigir aunque el approve falló
        setTimeout(() => { window.location.href = '?p=home'; }, 2500);
      }
    }
  } catch (e) {
    showApproveAlert('Error de conexión. Inténtalo de nuevo.', 'error');
    if (btn) { btn.disabled = false; btn.textContent = 'Autorizar mi TV'; }
  }
}

// ── Init ──────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
  if (window.TV_MODE) {
    initTvMode();
    return;
  }

  if (window.IS_MOBILE_AUTH && window.IS_LOGGED_IN) {
    // Ya logueado: el botón de aprobar se muestra en el PHP, nada más que hacer
    return;
  }

  // Login/Register normal
  document.querySelectorAll('.auth-tab').forEach(tab => {
    tab.addEventListener('click', () => switchTab(tab.dataset.tab));
  });
  const params = new URLSearchParams(window.location.search);
  if (params.get('tab') === 'register') switchTab('register');
});
