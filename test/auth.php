<?php
/**
 * Test del endpoint api/auth.php — muestra la respuesta cruda.
 * Permite diagnosticar qué devuelve exactamente auth.php.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password'] ?? '';
$action   = $_POST['action']   ?? 'login';
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$rawResponse = '';
$httpCode    = 0;
$parsed      = null;
$parseError  = '';
$isJson      = false;

if ($submitted && $email) {
    // Llamar a api/auth.php directamente via cURL interno
    $payload = json_encode(['action' => $action, 'email' => $email, 'password' => $password]);

    // Construir URL absoluta al api/auth.php real
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'];
    // Subir dos niveles desde /test/ → raíz, luego /api/auth.php
    $script   = dirname(dirname($_SERVER['SCRIPT_NAME']));
    $apiUrl   = $protocol . '://' . $host . rtrim($script, '/') . '/api/auth.php';

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $rawResponse = curl_exec($ch);
    $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError   = curl_error($ch);
    curl_close($ch);

    if ($rawResponse !== false && $rawResponse !== '') {
        $parsed = json_decode($rawResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $parseError = json_last_error_msg();
        } else {
            $isJson = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Test Auth</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh; background: #0f0f17; color: #e0e0f0;
      font-family: system-ui, -apple-system, sans-serif;
      padding: 28px 16px; display: flex; flex-direction: column; align-items: center; gap: 20px;
    }
    .card {
      background: #1a1a28; border: 1px solid #2e2e4a; border-radius: 14px;
      padding: 24px 28px; width: 100%; max-width: 600px;
    }
    .card-title {
      font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .5px; color: #8b5cf6; margin-bottom: 16px;
    }
    label { display: block; font-size: 0.78rem; font-weight: 600; color: #999; margin-bottom: 5px; margin-top: 14px; }
    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%; padding: 9px 12px; background: #0f0f1a; border: 1px solid #2e2e4a;
      border-radius: 8px; color: #fff; font-size: 0.88rem; outline: none;
    }
    input:focus { border-color: #8b5cf6; }
    button {
      margin-top: 18px; width: 100%; padding: 11px;
      background: #8b5cf6; color: #fff; font-size: 0.9rem; font-weight: 700;
      border: none; border-radius: 8px; cursor: pointer;
    }
    button:hover { background: #7c3aed; }
    .row { display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; border-bottom: 1px solid #22223a; font-size: 0.83rem; }
    .row:last-child { border-bottom: none; }
    .rl { color: #888; min-width: 160px; flex-shrink: 0; }
    .rv { color: #ddd; word-break: break-all; }
    .code-box {
      background: #0a0a12; border: 1px solid #2e2e4a; border-radius: 8px;
      padding: 14px; font-family: monospace; font-size: 0.78rem; color: #a78bfa;
      white-space: pre-wrap; word-break: break-all; margin-top: 14px; max-height: 280px; overflow-y: auto;
    }
    .badge { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
    .badge-ok    { background: rgba(34,197,94,.2);  color: #86efac; }
    .badge-error { background: rgba(239,68,68,.2);  color: #fca5a5; }
    .badge-warn  { background: rgba(245,158,11,.2); color: #fcd34d; }
    .alert { padding: 11px 14px; border-radius: 9px; font-size: 0.83rem; margin-top: 14px; }
    .alert-ok    { background: rgba(34,197,94,.1);  border: 1px solid rgba(34,197,94,.3);  color: #86efac; }
    .alert-error { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.3);  color: #fca5a5; }
    .nav { display: flex; gap: 10px; }
    a.btn-link {
      padding: 8px 18px; background: rgba(139,92,246,.15); border: 1px solid #8b5cf6;
      color: #8b5cf6; border-radius: 8px; text-decoration: none; font-size: 0.83rem; font-weight: 600;
    }
    a.btn-link:hover { background: #8b5cf6; color: #fff; }
  </style>
</head>
<body>

<div class="nav">
  <a href="index.php" class="btn-link">← Login test</a>
  <a href="db.php"    class="btn-link">DB test</a>
</div>

<div class="card">
  <div class="card-title">🔑 Test de api/auth.php</div>
  <p style="font-size:.8rem;color:#666;margin-bottom:2px;">
    Llama directamente al endpoint de autenticación y muestra la respuesta cruda.
  </p>

  <form method="POST">
    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="tu@correo.com" required>

    <label>Contraseña</label>
    <input type="password" name="password" value="<?= htmlspecialchars($password) ?>">

    <label>Acción</label>
    <input type="text" name="action" value="<?= htmlspecialchars($action) ?>" placeholder="login">

    <button type="submit">Llamar a auth.php</button>
  </form>
</div>

<?php if ($submitted): ?>
<div class="card">
  <div class="card-title">📡 Resultado</div>

  <div class="row">
    <span class="rl">URL llamada</span>
    <span class="rv"><?= htmlspecialchars($apiUrl ?? 'N/A') ?></span>
  </div>
  <div class="row">
    <span class="rl">HTTP status</span>
    <span class="rv">
      <?php
        $cls = ($httpCode >= 200 && $httpCode < 300) ? 'ok' : (($httpCode >= 500) ? 'error' : 'warn');
      ?>
      <span class="badge badge-<?= $cls ?>"><?= $httpCode ?: 'Sin respuesta' ?></span>
    </span>
  </div>
  <?php if (!empty($curlError)): ?>
  <div class="row">
    <span class="rl">Error cURL</span>
    <span class="rv" style="color:#fca5a5;"><?= htmlspecialchars($curlError) ?></span>
  </div>
  <?php endif; ?>
  <div class="row">
    <span class="rl">¿JSON válido?</span>
    <span class="rv">
      <?php if ($isJson): ?>
        <span class="badge badge-ok">✅ Sí</span>
      <?php else: ?>
        <span class="badge badge-error">❌ No — <?= htmlspecialchars($parseError) ?></span>
      <?php endif; ?>
    </span>
  </div>

  <?php if ($isJson && $parsed): ?>
  <div class="row">
    <span class="rl">success</span>
    <span class="rv">
      <span class="badge <?= $parsed['success'] ? 'badge-ok' : 'badge-error' ?>">
        <?= $parsed['success'] ? 'true' : 'false' ?>
      </span>
    </span>
  </div>
  <div class="row">
    <span class="rl">message</span>
    <span class="rv"><?= htmlspecialchars($parsed['message'] ?? '(sin mensaje)') ?></span>
  </div>
  <?php endif; ?>

  <div style="margin-top:12px; font-size:0.75rem; color:#666;">Respuesta cruda de auth.php:</div>
  <div class="code-box"><?= htmlspecialchars($rawResponse ?: '(sin respuesta)') ?></div>

  <?php if (!$isJson && $rawResponse): ?>
  <div class="alert alert-error" style="margin-top:14px;">
    ⚠️ <strong>auth.php devuelve HTML/texto en lugar de JSON.</strong><br>
    Esto causa "Error del servidor (500)" en el frontend. Probable causa: un error PHP se imprime antes del JSON.
    Revisa la respuesta cruda de arriba para ver el error exacto.
  </div>
  <?php elseif ($isJson && isset($parsed['success']) && !$parsed['success'] && $httpCode === 500): ?>
  <div class="alert alert-error" style="margin-top:14px;">
    ❌ <strong>Error del servidor:</strong> <?= htmlspecialchars($parsed['message']) ?><br>
    Causa probable: credenciales de BD incorrectas. Verifica <code>includes/.env.php</code> en el servidor.
  </div>
  <?php elseif ($isJson && isset($parsed['success']) && $parsed['success']): ?>
  <div class="alert alert-ok" style="margin-top:14px;">
    ✅ Login exitoso. El sistema de autenticación funciona correctamente.
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

</body>
</html>
