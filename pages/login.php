<?php
/**
 * StreamHub - Página de login (login.php)
 */
?>
<!-- Botón de tema flotante -->
<div style="position:fixed; top:1rem; right:1rem; z-index:100;">
  <button class="btn-theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
    <i id="theme-icon" class="fas fa-sun"></i>
  </button>
</div>

<div class="auth-page">
  <div class="auth-card">

    <div class="auth-logo">Stream<span style="color:var(--accent);">Hub</span></div>
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
