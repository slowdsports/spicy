<?php
/**
 * StreamHub Admin - Login
 * Página independiente de login solo para administradores.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Si ya es admin, redirigir al dashboard
if (isLoggedIn() && isAdmin()) {
    header('Location: ' . BASE_URL . 'admin/');
    exit();
}

$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT id, nombre, email, password, rol, activo FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && $user['activo'] && $user['rol'] === 'admin' && password_verify($password, $user['password'])) {
                // Login exitoso
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_rol']   = $user['rol'];
                header('Location: ' . BASE_URL . 'admin/');
                exit();
            } else {
                $error = 'Credenciales incorrectas o sin permisos de administrador.';
            }
        } catch (Exception $e) {
            $error = 'Error de conexión con la base de datos.';
        }
    } else {
        $error = 'Completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login · Tele Deportes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<script src="../assets/js/theme.js"></script>

<div class="auth-page">
  <div class="auth-card" style="max-width:380px;">

    <!-- Logo + badge admin -->
    <div style="text-align:center; margin-bottom:1.5rem;">
      <div class="auth-logo">Tele<span style="color:var(--accent);">Gratuita</span></div>
      <span style="font-size:0.72rem; font-weight:700; background:var(--accent-soft); color:var(--accent); border:1px solid var(--border-accent); padding:3px 10px; border-radius:100px; font-family:'Space Mono',monospace;">
        <i class="fas fa-shield-alt me-1"></i> Panel de Administración
      </span>
    </div>

    <?php if ($error): ?>
      <div class="alert-sh alert-error" style="display:block; margin-bottom:1rem;">
        <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label"><i class="fas fa-envelope me-1"></i> Correo electrónico</label>
        <input type="email" name="email" class="form-control-sh" placeholder="admin@telegratuita.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label"><i class="fas fa-lock me-1"></i> Contraseña</label>
        <input type="password" name="password" class="form-control-sh" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-auth-submit">
        <i class="fas fa-sign-in-alt me-2"></i> Acceder al panel
      </button>
    </form>

    <div style="text-align:center; margin-top:1.5rem;">
      <a href="<?= BASE_URL ?>?p=home"
         style="font-size:0.8rem; color:var(--text-muted); text-decoration:none;">
        <i class="fas fa-arrow-left me-1"></i> Volver al sitio
      </a>
    </div>

  </div>
</div>
</body>
</html>
