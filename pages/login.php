<?php
/**
 * StreamHub - Página de login
 */

// ── Detección de Smart TV / Smart Box ───────────────────────────────────────
$_tvUa = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$_tvPatterns = [
    'smart-tv', 'smarttv', 'netcast', 'webos', 'tizen', 'hbbtv',
    'firetv', 'fire tv', 'appletv', 'apple tv', 'roku',
    'androidtv', 'android tv', 'bravia', 'viera',
    'googletv', 'google tv', 'aftb', 'aftt', 'aftm', 'afts', 'aftr', 'aftn',
    'crkey', 'nettv', 'netrange', 'philips tv',
];
$_isTvDevice = false;
foreach ($_tvPatterns as $_p) {
    if (strpos($_tvUa, $_p) !== false) { $_isTvDevice = true; break; }
}

$_isTvMode     = $_isTvDevice || isset($_GET['tvlogin']);
$_tvToken      = preg_replace('/[^a-f0-9]/', '', $_GET['tv'] ?? '');
$_isMobileAuth = strlen($_tvToken) === 32;

$_tvScanBase = $BASE_FULL . '?p=login&tv=';
$_tvApiBase  = $BASE_FULL . 'api/tvlogin.php';
?>

<!-- Botón de tema flotante -->
<div style="position:fixed; top:1rem; right:1rem; z-index:100;">
  <button class="btn-theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
    <i id="theme-icon" class="fas fa-sun"></i>
  </button>
</div>

<?php if ($_isTvMode): ?>
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- MODO TV: pantalla QR para autenticar desde el celular                    -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<style>
.tv-login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-body, #0f0f13);
  padding: 2rem;
}
.tv-login-card {
  background: var(--bg-card, #1a1a24);
  border: 1px solid var(--border, #2a2a3a);
  border-radius: 24px;
  padding: 2.5rem 3rem;
  text-align: center;
  max-width: 520px;
  width: 100%;
}
.tv-login-logo { font-size: 2rem; font-weight: 800; margin-bottom: 0.2rem; letter-spacing: -0.5px; }
.tv-login-sub  { color: var(--text-secondary, #a0a0b0); font-size: 0.95rem; margin-bottom: 1.75rem; }
.tv-qr-box {
  background: #fff;
  border-radius: 16px;
  padding: 1rem;
  display: inline-block;
  margin: 0 auto 1.5rem;
  min-width: 258px;
  min-height: 258px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.tv-qr-loading {
  width: 256px;
  height: 256px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  color: #888;
}
.tv-steps {
  text-align: left;
  margin-bottom: 1.25rem;
  color: var(--text-secondary, #a0a0b0);
  font-size: 0.93rem;
}
.tv-steps li { margin-bottom: 0.35rem; }
.tv-steps strong { color: var(--accent, #8b5cf6); }
.tv-status-box {
  padding: 0.8rem 1.25rem;
  border-radius: 12px;
  font-size: 0.92rem;
  background: var(--accent-soft, rgba(139,92,246,.1));
  border: 1px solid var(--border-accent, rgba(139,92,246,.25));
  color: var(--text-primary, #e0e0f0);
  margin-bottom: 0.75rem;
  transition: background .3s, border-color .3s, color .3s;
}
.tv-status-box.status-ok      { background: rgba(34,197,94,.12); border-color: rgba(34,197,94,.35); color: #4ade80; }
.tv-status-box.status-expired { background: rgba(239,68,68,.12); border-color: rgba(239,68,68,.35); color: #f87171; }
.tv-countdown { font-size: 0.78rem; color: var(--text-muted, #6b6b80); }
</style>

<div class="tv-login-page">
  <div class="tv-login-card">
    <div class="tv-login-logo">Tele<span style="color:var(--accent,#8b5cf6);"> Deportes</span></div>
    <p class="tv-login-sub">Inicia sesión con tu celular</p>

    <div class="tv-qr-box" id="tv-qr-box">
      <div id="tv-qr-loading" class="tv-qr-loading">
        <i class="fas fa-circle-notch fa-spin" style="font-size:2.25rem; color:#8b5cf6;"></i>
        <span style="font-size:0.85rem;">Generando código...</span>
      </div>
      <div id="tv-qr" style="display:none;"></div>
    </div>

    <ol class="tv-steps">
      <li>Abre la <strong>cámara</strong> de tu celular</li>
      <li>Apunta al <strong>código QR</strong> de arriba</li>
      <li>Inicia sesión en la página que se abre</li>
    </ol>

    <div id="tv-status-box" class="tv-status-box">
      <i class="fas fa-clock me-2"></i><span id="tv-status-text">Esperando autorización…</span>
    </div>
    <p class="tv-countdown">Código expira en <span id="tv-countdown">10:00</span></p>

    <?php if (isset($_GET['tv_error'])): ?>
    <p style="color:#f87171; font-size:0.82rem; margin-top:0.75rem;">
      <i class="fas fa-exclamation-circle me-1"></i>El código anterior no era válido o ya fue usado. Se generó uno nuevo.
    </p>
    <?php endif; ?>
  </div>
</div>

<!-- QRCode.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<?php elseif ($_isMobileAuth): ?>
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- MODO MÓVIL AUTH: el usuario escaneó el QR y debe autorizar la TV         -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<style>
.tv-badge {
  background: var(--accent-soft, rgba(139,92,246,.1));
  border: 1px solid var(--border-accent, rgba(139,92,246,.25));
  border-radius: 14px;
  padding: 1rem 1.25rem;
  margin-bottom: 1.5rem;
  text-align: center;
  font-size: 0.88rem;
  color: var(--text-secondary, #a0a0b0);
  line-height: 1.5;
}
.tv-badge .tv-icon { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
.btn-tv-approve {
  width: 100%;
  padding: 0.9rem;
  background: var(--accent, #8b5cf6);
  color: #fff;
  border: none;
  border-radius: 14px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity .2s;
  margin-bottom: 0.75rem;
}
.btn-tv-approve:hover    { opacity: .88; }
.btn-tv-approve:disabled { opacity: .45; cursor: default; }
</style>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">Tele<span style="color:var(--accent);"> Deportes</span></div>

    <?php if (isLoggedIn()): ?>
    <!-- ── Usuario logueado: solo necesita aprobar ───────── -->
    <div class="tv-badge">
      <span class="tv-icon">📺</span>
      <strong style="color:var(--text-primary,#e0e0f0);">Solicitud de inicio de sesión en TV</strong><br>
      Conectado como <strong><?= htmlspecialchars(userName()) ?></strong>.<br>
      ¿Autorizar el acceso desde tu Smart TV?
    </div>
    <div id="mobile-auth-alert" class="alert-sh"></div>
    <button class="btn-tv-approve" id="btn-tv-approve" onclick="approveTvLogin()">
      <i class="fas fa-tv me-2"></i>Autorizar mi TV
    </button>
    <div style="text-align:center;">
      <a href="<?= url('home') ?>" style="font-size:0.82rem; color:var(--text-secondary); text-decoration:none;">
        <i class="fas fa-times me-1"></i>Cancelar
      </a>
    </div>

    <?php else: ?>
    <!-- ── Usuario no logueado: iniciar sesión y auto-aprobar ── -->
    <div class="tv-badge">
      <span class="tv-icon">📺</span>
      <strong style="color:var(--text-primary,#e0e0f0);">Inicio de sesión desde TV</strong><br>
      Inicia sesión para autorizar el acceso en tu Smart TV.
    </div>
    <div class="alert-sh" id="alert-login"></div>
    <div class="form-group">
      <label class="form-label" for="login-email">
        <i class="fas fa-envelope me-1"></i>Correo electrónico
      </label>
      <input type="email" id="login-email" class="form-control-sh" placeholder="tu@correo.com" autocomplete="email">
    </div>
    <div class="form-group">
      <label class="form-label" for="login-password">
        <i class="fas fa-lock me-1"></i>Contraseña
      </label>
      <input type="password" id="login-password" class="form-control-sh" placeholder="••••••••" autocomplete="current-password">
    </div>
    <button class="btn-auth-submit" id="btn-login-submit" onclick="submitLogin()">
      <i class="fas fa-tv me-2"></i>Iniciar sesión y autorizar TV
    </button>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- LOGIN NORMAL                                                              -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="auth-page">
  <div class="auth-card">

    <div class="auth-logo">Tele<span style="color:var(--accent);"> Deportes</span></div>
    <p class="auth-subtitle">Tu plataforma de streaming favorita</p>

    <div class="auth-tabs">
      <button class="auth-tab active" data-tab="login">Iniciar sesión</button>
      <button class="auth-tab" data-tab="register">Registrarse</button>
    </div>

    <!-- FORMULARIO LOGIN -->
    <div id="form-login">
      <div class="alert-sh" id="alert-login"></div>
      <div class="form-group">
        <label class="form-label" for="login-email">
          <i class="fas fa-envelope me-1"></i> Correo electrónico
        </label>
        <input type="email" id="login-email" class="form-control-sh" placeholder="tu@correo.com" autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label" for="login-password">
          <i class="fas fa-lock me-1"></i> Contraseña
        </label>
        <input type="password" id="login-password" class="form-control-sh" placeholder="••••••••" autocomplete="current-password">
      </div>
      <div style="text-align:right; margin-bottom:1rem;">
        <a href="#" style="font-size:0.78rem; color:var(--accent); text-decoration:none;">¿Olvidaste tu contraseña?</a>
      </div>
      <button class="btn-auth-submit" id="btn-login-submit" onclick="submitLogin()">
        <i class="fas fa-sign-in-alt me-2"></i> Iniciar sesión
      </button>
      <div style="display:none;margin-top:1.25rem; padding:0.75rem 1rem; background:var(--accent-soft); border:1px solid var(--border-accent); border-radius:10px;">
        <p style="font-size:0.75rem; color:var(--text-secondary); margin:0 0 0.25rem;">
          <i class="fas fa-info-circle me-1" style="color:var(--accent);"></i>
          <strong>Usuario de prueba:</strong>
        </p>
        <p style="font-size:0.75rem; color:var(--text-muted); margin:0; font-family:'Space Mono',monospace;">
          admin@telegratuita.com<br>admin123
        </p>
      </div>
    </div>

    <!-- FORMULARIO REGISTRO -->
    <div id="form-register" style="display:none;">
      <div class="alert-sh" id="alert-register"></div>
      <div class="form-group">
        <label class="form-label" for="reg-name"><i class="fas fa-user me-1"></i> Nombre completo</label>
        <input type="text" id="reg-name" class="form-control-sh" placeholder="Tu nombre" autocomplete="name">
      </div>
      <div class="form-group">
        <label class="form-label" for="reg-email"><i class="fas fa-envelope me-1"></i> Correo electrónico</label>
        <input type="email" id="reg-email" class="form-control-sh" placeholder="tu@correo.com" autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label" for="reg-password"><i class="fas fa-lock me-1"></i> Contraseña</label>
        <input type="password" id="reg-password" class="form-control-sh" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
      </div>
      <div class="form-group">
        <label class="form-label" for="reg-confirm"><i class="fas fa-lock me-1"></i> Confirmar contraseña</label>
        <input type="password" id="reg-confirm" class="form-control-sh" placeholder="Repite tu contraseña" autocomplete="new-password">
      </div>
      <button class="btn-auth-submit" id="btn-register-submit" onclick="submitRegister()">
        <i class="fas fa-user-plus me-2"></i> Crear cuenta
      </button>
      <p style="font-size:0.72rem; color:var(--text-muted); text-align:center; margin-top:0.75rem;">
        Al registrarte aceptas nuestros <a href="#" style="color:var(--accent);">términos de servicio</a>
      </p>
    </div>

    <div class="auth-divider">o</div>
    <div style="text-align:center;">
      <a href="<?= url('home') ?>" style="font-size:0.83rem; color:var(--text-secondary); text-decoration:none;">
        <i class="fas fa-arrow-left me-1"></i> Volver al inicio
      </a>
    </div>

  </div>
</div>
<?php endif; ?>

<!-- Configuración para auth.js -->
<script>
window.TV_MODE        = <?= json_encode($_isTvMode) ?>;
window.TV_TOKEN       = <?= json_encode($_tvToken) ?>;
window.IS_MOBILE_AUTH = <?= json_encode($_isMobileAuth) ?>;
window.IS_LOGGED_IN   = <?= json_encode(isLoggedIn()) ?>;
window.TV_SCAN_BASE   = <?= json_encode($_tvScanBase) ?>;
window.TV_API_BASE    = <?= json_encode($_tvApiBase) ?>;
</script>
