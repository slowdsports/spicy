<?php
/**
 * StreamHub - Página de acceso europeo pendiente / denegado
 */

// Si no está logueado, ir al login
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '?p=login');
    exit();
}

$userId = userId();
$euStatus = 'pendiente';

try {
    $conn = getDBConnection();
    _euEnsureTable();

    $s = $conn->prepare("
        SELECT estado FROM eu_access
        WHERE user_id = ?
        ORDER BY FIELD(estado, 'aprobado', 'pendiente', 'denegado'), updated_at DESC
        LIMIT 1
    ");
    $s->bind_param('i', $userId);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();

    if (!$row) {
        // Sin registro en BD: el usuario llegó directamente a esta URL.
        // Si no tiene país EU en sesión, simplemente volverlo al inicio
        // (no hacer redirect a home si hay país EU para evitar bucle middleware).
        $countryCheck = $_SESSION['_eu_country'] ?? '';
        if (!$countryCheck || !_euIsEuCountry($countryCheck)) {
            header('Location: ' . BASE_URL . '?p=home');
            exit();
        }
        // Crear el registro pendiente aquí mismo en vez de rebotar a home
        $ip      = _euGetIp();
        $insPage = $conn->prepare(
            "INSERT INTO eu_access (user_id, ip, pais, estado) VALUES (?,?,?,'pendiente')"
        );
        $insPage->bind_param('iss', $userId, $ip, $countryCheck);
        $insPage->execute();
        $insPage->close();
        $euStatus = 'pendiente';
    } elseif ($row['estado'] === 'aprobado') {
        // Ya aprobado: limpiar la sesión y dejarle pasar
        unset($_SESSION['_eu_status']);
        header('Location: ' . BASE_URL . '?p=home');
        exit();
    } else {
        $euStatus = $row['estado'];
    }

} catch (Throwable $e) {
    $euStatus = $_SESSION['_eu_status'] ?? 'pendiente';
}
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

    <?php if ($euStatus === 'denegado'): ?>

      <!-- ── ACCESO DENEGADO ── -->
      <div style="text-align:center; margin: 1.5rem 0 1rem;">
        <div style="
          width:64px; height:64px; border-radius:50%;
          background:rgba(239,68,68,.12); border:2px solid rgba(239,68,68,.3);
          display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;
        ">
          <i class="fas fa-ban" style="font-size:1.6rem; color:#ef4444;"></i>
        </div>
        <h2 style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-bottom:.5rem;">
          Acceso denegado
        </h2>
        <p style="font-size:.85rem; color:var(--text-secondary); line-height:1.6; margin-bottom:1.5rem;">
          Tu solicitud de acceso no fue aprobada. Si crees que esto es un error,
          puedes contactarnos para solicitar una revisión.
        </p>
      </div>

      <div style="
        padding:.75rem 1rem;
        background:rgba(239,68,68,.07);
        border:1px solid rgba(239,68,68,.2);
        border-radius:10px;
        margin-bottom:1.5rem;
        font-size:.8rem;
        color:var(--text-muted);
        line-height:1.6;
      ">
        <i class="fas fa-info-circle me-1" style="color:#ef4444;"></i>
        El acceso desde tu región está sujeto a aprobación manual. En caso de denegación,
        el acceso a la plataforma no estará disponible desde tu ubicación actual.
      </div>

    <?php else: ?>

      <!-- ── ESPERANDO APROBACIÓN ── -->
      <div style="text-align:center; margin: 1.5rem 0 1rem;">
        <div style="
          width:64px; height:64px; border-radius:50%;
          background:var(--accent-soft); border:2px solid var(--border-accent);
          display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;
          animation: eu-pulse 2.5s ease-in-out infinite;
        ">
          <i class="fas fa-hourglass-half" style="font-size:1.6rem; color:var(--accent);"></i>
        </div>
        <h2 style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-bottom:.5rem;">
          Esperando aprobación
        </h2>
        <p style="font-size:.85rem; color:var(--text-secondary); line-height:1.6; margin-bottom:1.5rem;">
          Tu cuenta ha sido registrada. Un administrador revisará tu solicitud
          y te dará acceso en breve.
        </p>
      </div>

      <div style="
        padding:.75rem 1rem;
        background:var(--accent-soft);
        border:1px solid var(--border-accent);
        border-radius:10px;
        margin-bottom:1.5rem;
        font-size:.8rem;
        color:var(--text-muted);
        line-height:1.6;
      ">
        <i class="fas fa-shield-halved me-1" style="color:var(--accent);"></i>
        Por razones de cumplimiento normativo, el acceso desde tu región
        requiere verificación manual. Este proceso suele completarse en
        menos de 24 horas.
      </div>

      <button
        onclick="location.reload()"
        class="btn-auth-submit"
        style="margin-bottom:.75rem; background:var(--accent-soft); color:var(--accent); border:1px solid var(--border-accent);"
      >
        <i class="fas fa-rotate-right me-2"></i> Verificar estado
      </button>

    <?php endif; ?>

    <!-- Sesión del usuario -->
    <div style="
      padding:.6rem 1rem;
      background:var(--bg-secondary);
      border:1px solid var(--border);
      border-radius:10px;
      margin-bottom:1rem;
      display:flex;
      align-items:center;
      gap:.6rem;
    ">
      <i class="fas fa-user-circle" style="color:var(--accent); font-size:1.1rem;"></i>
      <div>
        <div style="font-size:.8rem; font-weight:600; color:var(--text-primary);">
          <?= htmlspecialchars(userName()) ?>
        </div>
        <div style="font-size:.7rem; color:var(--text-muted);">Sesión activa</div>
      </div>
      <a href="api/auth.php?action=logout_redirect"
         style="margin-left:auto; font-size:.72rem; color:#ef4444; text-decoration:none;"
         title="Cerrar sesión">
        <i class="fas fa-right-from-bracket"></i> Salir
      </a>
    </div>

  </div>
</div>

<style>
@keyframes eu-pulse {
  0%, 100% { box-shadow: 0 0 0 0 var(--accent-glow); }
  50%       { box-shadow: 0 0 0 10px transparent; }
}
</style>
