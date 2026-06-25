<?php
/**
 * StreamHub - Restablecer contraseña (?p=reset_password&token=...)
 *
 * El token solo se valida server-side aquí para decidir si mostrar el
 * formulario (UX); la validación real (existe, sin usar, sin expirar)
 * vuelve a correr en api/auth.php al enviar la nueva contraseña — esta
 * página nunca toca la base de datos.
 */
$_hasToken = isset($_GET['token']) && trim($_GET['token']) !== '';
?>

<!-- Botón de tema flotante -->
<div style="position:fixed; top:1rem; right:1rem; z-index:100;">
  <button class="btn-theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
    <i id="theme-icon" class="fas fa-sun"></i>
  </button>
</div>

<div class="auth-page">
  <div class="auth-card">

    <div class="auth-logo">Tele<span style="color:var(--accent);"> Deportes</span></div>
    <p class="auth-subtitle">Restablecer contraseña</p>

    <?php if (!$_hasToken): ?>
    <div class="alert-sh alert-error" style="display:block;">
      <i class="fas fa-exclamation-triangle me-1"></i>
      Este enlace no es válido. Solicita uno nuevo desde la pantalla de inicio de sesión.
    </div>
    <div style="text-align:center; margin-top:1.25rem;">
      <a href="<?= url('login') ?>" style="font-size:0.85rem; color:var(--accent); text-decoration:none;">
        <i class="fas fa-arrow-left me-1"></i> Volver a iniciar sesión
      </a>
    </div>
    <?php else: ?>
    <div id="reset-password-form">
      <div class="alert-sh" id="alert-reset"></div>
      <p style="font-size:0.82rem; color:var(--text-secondary); margin:0 0 1rem;">
        Ingresa tu nueva contraseña. El enlace es válido por 60 minutos y solo se puede usar una vez.
      </p>
      <div class="form-group">
        <label class="form-label" for="reset-password">
          <i class="fas fa-lock me-1"></i> Nueva contraseña
        </label>
        <input type="password" id="reset-password" class="form-control-sh" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
      </div>
      <div class="form-group">
        <label class="form-label" for="reset-password-confirm">
          <i class="fas fa-lock me-1"></i> Confirmar contraseña
        </label>
        <input type="password" id="reset-password-confirm" class="form-control-sh" placeholder="Repite tu contraseña" autocomplete="new-password">
      </div>
      <button class="btn-auth-submit" id="btn-reset-submit">
        <i class="fas fa-key me-2"></i> Guardar nueva contraseña
      </button>
    </div>
    <?php endif; ?>

    <div class="auth-divider">o</div>
    <div style="text-align:center;">
      <a href="<?= url('home') ?>" style="font-size:0.83rem; color:var(--text-secondary); text-decoration:none;">
        <i class="fas fa-arrow-left me-1"></i> Volver al inicio
      </a>
    </div>

  </div>
</div>
