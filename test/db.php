<?php
/**
 * Test de conexión a base de datos — standalone.
 * Prueba paso a paso qué falla en el servidor.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$result  = [];
$fatal   = false;

// ── Formulario con credenciales ──────────────────────────────────────────────
$host = trim($_POST['host'] ?? 'localhost');
$user = trim($_POST['user'] ?? '');
$pass = $_POST['pass'] ?? '';
$db   = trim($_POST['db']   ?? '');
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

// ── Tests de entorno ─────────────────────────────────────────────────────────
$checks = [];

// 1. Extensión mysqli
$checks['mysqli habilitado'] = extension_loaded('mysqli')
    ? ['ok', 'Sí'] : ['error', 'NO — habilitar en cPanel → PHP Selector → Extensions'];

// 2. PDO disponible (alternativa)
$checks['PDO MySQL disponible'] = extension_loaded('pdo_mysql')
    ? ['ok', 'Sí'] : ['warn', 'No (no es crítico si mysqli funciona)'];

// 3. Archivo config.php accesible
$configPath = __DIR__ . '/../includes/config.php';
$checks['includes/config.php existe'] = file_exists($configPath)
    ? ['ok', 'Sí'] : ['error', 'NO — ¿se subió el archivo?'];

// 4. Archivo db.php accesible
$dbPath = __DIR__ . '/../includes/db.php';
$checks['includes/db.php existe'] = file_exists($dbPath)
    ? ['ok', 'Sí'] : ['error', 'NO — ¿se subió el archivo?'];

// 5. .env.php existe
$envPath = __DIR__ . '/../includes/.env.php';
$checks['includes/.env.php existe'] = file_exists($envPath)
    ? ['ok', 'Sí'] : ['warn', 'NO — usando credenciales por defecto (root/streamhub). Crear este archivo en el servidor.'];

// 6. Cargar config si existe
if (file_exists($configPath) && file_exists($dbPath)) {
    try {
        require_once $configPath;
        require_once $dbPath;
        $checks['config.php carga sin errores'] = ['ok', 'Sí'];
    } catch (Throwable $e) {
        $checks['config.php carga sin errores'] = ['error', 'Error: ' . $e->getMessage()];
        $fatal = true;
    }
}

// ── Prueba de conexión (si se envió el formulario) ───────────────────────────
$connResult = null;
$tableResult = null;

if ($submitted && !empty($user) && !empty($db)) {
    // Test 1: Conexión raw
    try {
        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            $connResult = ['error', 'Conexión fallida: ' . $conn->connect_error];
        } else {
            $conn->set_charset('utf8mb4');
            $connResult = ['ok', "Conexión exitosa al servidor MySQL (charset: utf8mb4)"];

            // Test 2: ¿Existe tabla usuarios?
            $tables = [];
            $r = $conn->query("SHOW TABLES");
            while ($row = $r->fetch_row()) $tables[] = $row[0];

            if (in_array('usuarios', $tables)) {
                $tableResult = ['ok', 'Tabla "usuarios" encontrada. Tablas: ' . implode(', ', $tables)];

                // Test 3: ¿Puedo leer un usuario?
                $test = $conn->query("SELECT id, email, rol FROM usuarios LIMIT 3");
                $rows = [];
                while ($u = $test->fetch_assoc()) $rows[] = "{$u['id']} — {$u['email']} ({$u['rol']})";
                $tableResult[1] .= '<br><small>Primeros registros: ' . (empty($rows) ? '(tabla vacía)' : implode(', ', $rows)) . '</small>';
            } else {
                $tableResult = ['warn', 'Tabla "usuarios" NO encontrada. Tablas disponibles: ' . (empty($tables) ? '(ninguna)' : implode(', ', $tables))];
            }
            $conn->close();
        }
    } catch (Throwable $e) {
        $connResult = ['error', 'Excepción: ' . $e->getMessage()];
    }
}

// ── Colores por estado ───────────────────────────────────────────────────────
function badge(string $status): string {
    return match($status) {
        'ok'    => '#22c55e',
        'warn'  => '#f59e0b',
        'error' => '#ef4444',
        default => '#888',
    };
}
function icon(string $status): string {
    return match($status) {
        'ok'    => '✅',
        'warn'  => '⚠️',
        'error' => '❌',
        default => '•',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Test DB</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh; background: #0f0f17; color: #e0e0f0;
      font-family: system-ui, -apple-system, sans-serif;
      padding: 28px 16px; display: flex; flex-direction: column; align-items: center; gap: 20px;
    }
    h2 { font-size: 1.1rem; font-weight: 700; color: #fff; }
    .card {
      background: #1a1a28; border: 1px solid #2e2e4a; border-radius: 14px;
      padding: 24px 28px; width: 100%; max-width: 580px;
    }
    .card-title {
      font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .5px; color: #8b5cf6; margin-bottom: 16px;
    }
    .check-row {
      display: flex; align-items: flex-start; gap: 10px;
      padding: 9px 0; border-bottom: 1px solid #22223a; font-size: 0.83rem;
    }
    .check-row:last-child { border-bottom: none; }
    .check-label { color: #aaa; min-width: 220px; flex-shrink: 0; }
    .check-val   { color: #ddd; line-height: 1.5; }
    label { display: block; font-size: 0.78rem; font-weight: 600; color: #999; margin-bottom: 5px; margin-top: 14px; }
    input[type="text"], input[type="password"] {
      width: 100%; padding: 9px 12px; background: #0f0f1a; border: 1px solid #2e2e4a;
      border-radius: 8px; color: #fff; font-size: 0.88rem; outline: none; transition: border-color .2s;
    }
    input:focus { border-color: #8b5cf6; }
    .hint { font-size: 0.72rem; color: #555; margin-top: 4px; }
    button {
      margin-top: 18px; width: 100%; padding: 11px;
      background: #8b5cf6; color: #fff; font-size: 0.9rem; font-weight: 700;
      border: none; border-radius: 8px; cursor: pointer; transition: background .2s;
    }
    button:hover { background: #7c3aed; }
    .result {
      margin-top: 18px; padding: 12px 16px; border-radius: 10px;
      font-size: 0.83rem; line-height: 1.7; border: 1px solid;
    }
    .result.ok    { background: rgba(34,197,94,.1);  border-color: rgba(34,197,94,.3);  color: #86efac; }
    .result.warn  { background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.3); color: #fcd34d; }
    .result.error { background: rgba(239,68,68,.1);  border-color: rgba(239,68,68,.3);  color: #fca5a5; }
    .nav { display: flex; gap: 10px; }
    a.btn-link {
      padding: 8px 18px; background: rgba(139,92,246,.15); border: 1px solid #8b5cf6;
      color: #8b5cf6; border-radius: 8px; text-decoration: none; font-size: 0.83rem; font-weight: 600;
    }
    a.btn-link:hover { background: #8b5cf6; color: #fff; }
    .fix-box {
      background: #0f0f1a; border: 1px solid #2e2e4a; border-radius: 10px;
      padding: 14px 16px; margin-top: 14px; font-size: 0.78rem; color: #888; line-height: 1.8;
    }
    .fix-box code {
      display: block; background: #0a0a12; padding: 10px 14px; border-radius: 7px;
      color: #a78bfa; font-family: monospace; margin: 8px 0; white-space: pre; overflow-x: auto;
    }
  </style>
</head>
<body>

<div class="nav">
  <a href="index.php" class="btn-link">← Login test</a>
  <a href="auth.php"  class="btn-link">Auth test →</a>
</div>

<div class="card">
  <div class="card-title">🔍 Diagnóstico del entorno</div>
  <?php foreach ($checks as $label => [$status, $msg]): ?>
  <div class="check-row">
    <span class="check-label"><?= htmlspecialchars($label) ?></span>
    <span class="check-val"><?= icon($status) ?> <?= $msg ?></span>
  </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-title">🔌 Probar conexión MySQL</div>
  <p style="font-size:.8rem;color:#666;margin-bottom:4px;">
    Obtén estos datos en <strong style="color:#888">cPanel → MySQL Databases</strong>.
  </p>

  <form method="POST">
    <label>Host</label>
    <input type="text" name="host" value="<?= htmlspecialchars($host) ?>" placeholder="localhost">
    <div class="hint">Casi siempre "localhost". Algunos hostings usan "127.0.0.1".</div>

    <label>Usuario MySQL</label>
    <input type="text" name="user" value="<?= htmlspecialchars($user) ?>" placeholder="u6514444_usuario">

    <label>Contraseña</label>
    <input type="password" name="pass" value="<?= htmlspecialchars($pass) ?>">

    <label>Nombre de la base de datos</label>
    <input type="text" name="db" value="<?= htmlspecialchars($db) ?>" placeholder="u6514444_streamhub">

    <button type="submit">Probar conexión</button>
  </form>

  <?php if ($submitted): ?>
    <?php if ($connResult): ?>
    <div class="result <?= $connResult[0] ?>">
      <strong><?= $connResult[0] === 'ok' ? '✅ Conexión OK' : '❌ Error de conexión' ?></strong><br>
      <?= $connResult[1] ?>
    </div>
    <?php endif; ?>

    <?php if ($tableResult): ?>
    <div class="result <?= $tableResult[0] ?>">
      <strong><?= icon($tableResult[0]) ?> Tabla usuarios</strong><br>
      <?= $tableResult[1] ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if ($submitted && $connResult && $connResult[0] === 'ok'): ?>
  <div class="fix-box">
    <strong style="color:#8b5cf6">✅ ¡Conexión exitosa!</strong> Crea o actualiza <code>includes/.env.php</code> en el servidor con estos valores:
    <code>&lt;?php
return [
    'DB_HOST' =&gt; '<?= htmlspecialchars($host) ?>',
    'DB_USER' =&gt; '<?= htmlspecialchars($user) ?>',
    'DB_PASS' =&gt; '<?= htmlspecialchars($pass) ?>',
    'DB_NAME' =&gt; '<?= htmlspecialchars($db) ?>',
    'BASE_URL' =&gt; '/',   // o '/spicy/' si está en subcarpeta
];</code>
  </div>
  <?php endif; ?>
</div>

</body>
</html>
