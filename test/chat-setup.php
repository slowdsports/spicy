<?php
/**
 * Chat setup — crea las tablas del chat y verifica el estado.
 * Ejecutar UNA VEZ en el servidor, luego eliminar este archivo.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$results = [];

function runSQL(mysqli $db, string $label, string $sql): array {
    $ok  = $db->query($sql);
    $err = $db->error;
    return ['label' => $label, 'ok' => (bool)$ok, 'error' => $err];
}

try {
    $db = getDBConnection();
    $results[] = ['label' => 'Conexión a BD', 'ok' => true, 'error' => ''];

    // ── Crear tabla chat_messages ────────────────────────────────────────────
    $results[] = runSQL($db, 'CREATE TABLE chat_messages', "
        CREATE TABLE IF NOT EXISTS `chat_messages` (
            `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `canal_id`   int(11) NOT NULL,
            `user_id`    int(10) UNSIGNED NOT NULL DEFAULT 0,
            `user_name`  varchar(100) NOT NULL,
            `user_rol`   enum('admin','spicy','usuario') NOT NULL DEFAULT 'usuario',
            `message`    varchar(500) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_canal_id` (`canal_id`, `id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── Crear tabla chat_online ──────────────────────────────────────────────
    $results[] = runSQL($db, 'CREATE TABLE chat_online', "
        CREATE TABLE IF NOT EXISTS `chat_online` (
            `session_id` varchar(128) NOT NULL,
            `user_id`    int(10) UNSIGNED NOT NULL DEFAULT 0,
            `canal_id`   int(11) NOT NULL,
            `last_seen`  timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`session_id`, `canal_id`),
            KEY `idx_canal_lastseen` (`canal_id`, `last_seen`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── Verificar que existen ────────────────────────────────────────────────
    $tables = [];
    $r = $db->query("SHOW TABLES");
    while ($row = $r->fetch_row()) $tables[] = $row[0];

    $hasMsgs   = in_array('chat_messages', $tables);
    $hasOnline = in_array('chat_online',   $tables);

    $results[] = ['label' => 'Tabla chat_messages existe', 'ok' => $hasMsgs,   'error' => $hasMsgs   ? '' : 'No encontrada — posible falta de privilegio CREATE'];
    $results[] = ['label' => 'Tabla chat_online existe',   'ok' => $hasOnline, 'error' => $hasOnline ? '' : 'No encontrada — posible falta de privilegio CREATE'];

    // ── Verificar privilegios ────────────────────────────────────────────────
    $privOk = true; $privErr = '';
    try {
        $db->query("INSERT INTO `chat_messages` (canal_id, user_id, user_name, user_rol, message)
                    VALUES (0, 0, '__test__', 'usuario', '__test__')");
        $testId = (int)$db->insert_id;
        if ($testId > 0) {
            $db->query("DELETE FROM `chat_messages` WHERE id = $testId");
        }
    } catch (Throwable $e) {
        $privOk = false; $privErr = $e->getMessage();
    }
    if ($db->error) { $privOk = false; $privErr = $db->error; }
    $results[] = ['label' => 'INSERT en chat_messages', 'ok' => $privOk, 'error' => $privErr];

    // ── Test de sesión ───────────────────────────────────────────────────────
    $sessOk = session_status() !== PHP_SESSION_NONE || @session_start();
    $results[] = ['label' => 'Sesión PHP', 'ok' => $sessOk, 'error' => $sessOk ? 'session_id: ' . session_id() : 'session_start() falló'];

    // ── Privilegio CREATE explícito ──────────────────────────────────────────
    $canCreate = $db->query("CREATE TABLE IF NOT EXISTS `_test_priv_` (id int) ENGINE=InnoDB");
    if ($canCreate) {
        $db->query("DROP TABLE IF EXISTS `_test_priv_`");
    }
    $results[] = ['label' => 'Privilegio CREATE TABLE', 'ok' => (bool)$canCreate, 'error' => $canCreate ? '' : $db->error];

} catch (Throwable $e) {
    $results[] = ['label' => 'Error fatal', 'ok' => false, 'error' => $e->getMessage()];
}

function icon(bool $ok): string { return $ok ? '✅' : '❌'; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <title>Chat Setup</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: #0f0f17; color: #e0e0f0; font-family: system-ui, sans-serif; padding: 28px 16px; display: flex; flex-direction: column; align-items: center; gap: 20px; }
    .card { background: #1a1a28; border: 1px solid #2e2e4a; border-radius: 14px; padding: 24px 28px; width: 100%; max-width: 620px; }
    .card-title { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #8b5cf6; margin-bottom: 16px; }
    .row { display: flex; align-items: flex-start; gap: 10px; padding: 9px 0; border-bottom: 1px solid #22223a; font-size: .83rem; }
    .row:last-child { border-bottom: none; }
    .rl { min-width: 240px; color: #aaa; flex-shrink: 0; }
    .rv { color: #ddd; word-break: break-all; line-height: 1.5; }
    .err { color: #fca5a5; font-size: .75rem; margin-top: 2px; }
    .nav { display: flex; gap: 10px; }
    a.btn { padding: 8px 18px; background: rgba(139,92,246,.15); border: 1px solid #8b5cf6; color: #8b5cf6; border-radius: 8px; text-decoration: none; font-size: .83rem; font-weight: 600; }
    a.btn:hover { background: #8b5cf6; color: #fff; }
    .warn { background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.3); border-radius: 10px; padding: 14px; font-size: .82rem; color: #fcd34d; margin-top: 4px; line-height: 1.6; }
    code { background: #0a0a12; padding: 2px 7px; border-radius: 5px; color: #a78bfa; font-family: monospace; font-size: .78rem; }
  </style>
</head>
<body>
<div class="nav">
  <a href="index.php" class="btn">← Login test</a>
  <a href="db.php"    class="btn">DB test</a>
  <a href="auth.php"  class="btn">Auth test</a>
</div>

<div class="card">
  <div class="card-title">💬 Chat — Setup y diagnóstico</div>
  <?php foreach ($results as $r): ?>
  <div class="row">
    <span class="rl"><?= htmlspecialchars($r['label']) ?></span>
    <span class="rv">
      <?= icon($r['ok']) ?>
      <?php if ($r['ok'] && $r['error']): ?>
        <span style="color:#888"><?= htmlspecialchars($r['error']) ?></span>
      <?php elseif (!$r['ok'] && $r['error']): ?>
        <div class="err"><?= htmlspecialchars($r['error']) ?></div>
      <?php endif; ?>
    </span>
  </div>
  <?php endforeach; ?>
</div>

<?php
$allOk = array_reduce($results, fn($c, $r) => $c && $r['ok'], true);
if (!$allOk): ?>
<div class="card">
  <div class="card-title">🛠 Si falla el privilegio CREATE</div>
  <p style="font-size:.82rem;color:#888;margin-bottom:14px;">
    Ve a <strong style="color:#ccc">cPanel → phpMyAdmin</strong>, selecciona la base de datos
    <code>u5869826_streamhub</code> y ejecuta este SQL manualmente:
  </p>
  <div style="background:#0a0a12;border:1px solid #2e2e4a;border-radius:8px;padding:14px;font-family:monospace;font-size:.75rem;color:#a78bfa;white-space:pre;overflow-x:auto">CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `canal_id`   int(11) NOT NULL,
  `user_id`    int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_name`  varchar(100) NOT NULL,
  `user_rol`   enum('admin','spicy','usuario') NOT NULL DEFAULT 'usuario',
  `message`    varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_canal_id` (`canal_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_online` (
  `session_id` varchar(128) NOT NULL,
  `user_id`    int(10) UNSIGNED NOT NULL DEFAULT 0,
  `canal_id`   int(11) NOT NULL,
  `last_seen`  timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`session_id`, `canal_id`),
  KEY `idx_canal_lastseen` (`canal_id`, `last_seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;</div>
</div>
<?php else: ?>
<div class="card" style="border-color:rgba(34,197,94,.3)">
  <div class="card-title" style="color:#22c55e">✅ Todo correcto</div>
  <p style="font-size:.83rem;color:#aaa;">Las tablas existen y los privilegios son correctos. El chat debería funcionar.</p>
  <p style="font-size:.78rem;color:#555;margin-top:10px;">Recuerda eliminar este archivo del servidor una vez verificado.</p>
</div>
<?php endif; ?>
</body>
</html>
