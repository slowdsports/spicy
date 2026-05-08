<?php
/**
 * Test de login standalone — sin dependencias del proyecto.
 * Credenciales hardcodeadas: usuario "admin" / contraseña "test1234"
 *
 * Pasos que verifica:
 *   1. PHP funciona
 *   2. session_start() funciona
 *   3. password_verify() funciona
 *   4. Redirección con header() funciona
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ── Credenciales de prueba (sin BD) ─────────────────────────────────────────
define('TEST_USER', 'admin');
define('TEST_HASH', password_hash('test1234', PASSWORD_DEFAULT));

// ── Sesión ───────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error   = '';
$success = false;
$info    = [];

// ── Recoger diagnóstico del entorno ─────────────────────────────────────────
$info['PHP']        = PHP_VERSION;
$info['Sesiones']   = ini_get('session.save_handler') . ' → ' . session_save_path();
$info['session_id'] = session_id() ?: '(vacío)';
$info['SAPI']       = PHP_SAPI;
$info['password_*'] = function_exists('password_hash') ? 'disponible' : 'NO disponible';

// ── Logout ───────────────────────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

// ── Ya logueado ──────────────────────────────────────────────────────────────
if (!empty($_SESSION['test_logged'])) {
    $success = true;
}

// ── Procesar formulario ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$success) {
    $user = trim($_POST['usuario'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($user === '' || $pass === '') {
        $error = 'Completa todos los campos.';
    } elseif ($user !== TEST_USER || !password_verify($pass, TEST_HASH)) {
        $error = 'Usuario o contraseña incorrectos.';
    } else {
        $_SESSION['test_logged'] = true;
        $_SESSION['test_user']   = $user;
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Test Login</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      gap: 24px;
      background: #0f0f17;
      color: #e0e0f0;
      font-family: system-ui, -apple-system, sans-serif;
      padding: 24px;
    }

    .card {
      background: #1a1a28;
      border: 1px solid #2e2e4a;
      border-radius: 14px;
      padding: 36px 40px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 8px 32px rgba(0,0,0,.5);
    }

    h1 {
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 6px;
      color: #fff;
    }

    .subtitle {
      font-size: 0.82rem;
      color: #888;
      margin-bottom: 28px;
    }

    label {
      display: block;
      font-size: 0.82rem;
      font-weight: 600;
      color: #aaa;
      margin-bottom: 6px;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 11px 14px;
      background: #0f0f1a;
      border: 1px solid #2e2e4a;
      border-radius: 8px;
      color: #fff;
      font-size: 0.95rem;
      margin-bottom: 18px;
      outline: none;
      transition: border-color .2s;
    }

    input:focus { border-color: #8b5cf6; }

    button[type="submit"] {
      width: 100%;
      padding: 12px;
      background: #8b5cf6;
      color: #fff;
      font-size: 0.95rem;
      font-weight: 700;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background .2s, transform .1s;
    }

    button[type="submit"]:hover { background: #7c3aed; transform: translateY(-1px); }

    .alert {
      padding: 11px 14px;
      border-radius: 8px;
      font-size: 0.88rem;
      margin-bottom: 18px;
    }

    .alert-error   { background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.4); color: #fca5a5; }
    .alert-success { background: rgba(34,197,94,.15);  border: 1px solid rgba(34,197,94,.4);  color: #86efac; }

    .hint {
      font-size: 0.78rem;
      color: #555;
      text-align: center;
      margin-top: 16px;
    }

    .hint code {
      background: #0f0f1a;
      padding: 1px 5px;
      border-radius: 4px;
      color: #8b5cf6;
    }

    /* Tabla de diagnóstico */
    .diag {
      width: 100%;
      max-width: 560px;
      background: #1a1a28;
      border: 1px solid #2e2e4a;
      border-radius: 14px;
      overflow: hidden;
    }

    .diag-title {
      padding: 14px 20px;
      font-size: 0.82rem;
      font-weight: 700;
      color: #8b5cf6;
      border-bottom: 1px solid #2e2e4a;
      letter-spacing: .5px;
      text-transform: uppercase;
    }

    table { width: 100%; border-collapse: collapse; }

    td {
      padding: 9px 20px;
      font-size: 0.8rem;
      border-bottom: 1px solid #22223a;
    }

    td:first-child { color: #888; width: 38%; }
    td:last-child  { color: #c0c0e0; font-family: monospace; word-break: break-all; }

    tr:last-child td { border-bottom: none; }

    .logout {
      display: inline-block;
      margin-top: 16px;
      padding: 8px 22px;
      background: transparent;
      border: 1px solid #8b5cf6;
      color: #8b5cf6;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      transition: background .2s, color .2s;
    }

    .logout:hover { background: #8b5cf6; color: #fff; }
  </style>
</head>
<body>

  <div class="card">
    <h1>🔐 Test de Login</h1>
    <p class="subtitle">Sin base de datos — credenciales locales hardcodeadas</p>

    <?php if ($success): ?>
      <div class="alert alert-success">
        ✅ Login exitoso. Sesión activa como <strong><?= htmlspecialchars($_SESSION['test_user']) ?></strong>.
      </div>
      <p style="font-size:.85rem;color:#aaa;margin-bottom:16px;">
        Las sesiones PHP funcionan correctamente en este servidor.
      </p>
      <a href="index.php?logout=1" class="logout">Cerrar sesión</a>

    <?php else: ?>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario"
               value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
               placeholder="admin" autocomplete="username" required>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password"
               placeholder="••••••••" autocomplete="current-password" required>

        <button type="submit">Iniciar sesión</button>
      </form>

      <p class="hint">Credenciales de prueba: <code>admin</code> / <code>test1234</code></p>
    <?php endif; ?>
  </div>

  <!-- Diagnóstico del entorno -->
  <div class="diag">
    <div class="diag-title">🛠 Diagnóstico del entorno</div>
    <table>
      <?php foreach ($info as $k => $v): ?>
      <tr>
        <td><?= htmlspecialchars($k) ?></td>
        <td><?= htmlspecialchars($v) ?></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td>Sesión activa</td>
        <td><?= !empty($_SESSION['test_logged']) ? '✅ Sí' : '❌ No' ?></td>
      </tr>
      <tr>
        <td>headers_sent()</td>
        <td><?= headers_sent($hFile, $hLine) ? "⚠️ Sí — $hFile : $hLine" : '✅ No' ?></td>
      </tr>
    </table>
  </div>

</body>
</html>
