<?php
/**
 * Chat SSE stream — envía mensajes en tiempo real al cliente
 * Tecnología: Server-Sent Events (SSE) + MySQL polling cada 500ms
 */

// Deshabilitar output buffering antes de cualquier output
if (ob_get_level()) ob_end_clean();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Cabeceras SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');

set_time_limit(0);
ignore_user_abort(true);

$canal_id   = intval($_GET['canal'] ?? 0);
$last_id    = intval($_GET['last_id'] ?? 0);
$session_id = session_id();
$user_id    = userId();

if ($canal_id <= 0) {
    echo "event: error\ndata: {\"msg\":\"canal_invalido\"}\n\n";
    flush();
    exit;
}

// Crear tablas si no existen (sólo al arrancar el script, no en el loop)
try {
    $db = getDBConnection();

    $db->query("CREATE TABLE IF NOT EXISTS `chat_messages` (
        `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `canal_id`   int(11) NOT NULL,
        `user_id`    int(10) UNSIGNED NOT NULL DEFAULT 0,
        `user_name`  varchar(100) NOT NULL,
        `user_rol`   enum('admin','spicy','usuario') NOT NULL DEFAULT 'usuario',
        `message`    varchar(500) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_canal_id` (`canal_id`, `id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->query("CREATE TABLE IF NOT EXISTS `chat_online` (
        `session_id` varchar(128) NOT NULL,
        `user_id`    int(10) UNSIGNED NOT NULL DEFAULT 0,
        `canal_id`   int(11) NOT NULL,
        `last_seen`  timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`session_id`, `canal_id`),
        KEY `idx_canal_lastseen` (`canal_id`, `last_seen`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    echo "event: error\ndata: {\"msg\":\"db_init\"}\n\n";
    flush();
    exit;
}

// Registrar presencia online
if ($session_id) {
    try {
        $p = $db->prepare("INSERT INTO chat_online (session_id, user_id, canal_id)
                           VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE last_seen = NOW()");
        $p->bind_param('sii', $session_id, $user_id, $canal_id);
        $p->execute();
        $p->close();
    } catch (Throwable $e) {}
}

// Si es la primera conexión, arrancar desde ~30 mensajes atrás para mostrar historial
if ($last_id === 0) {
    try {
        $h = $db->prepare("SELECT id, user_id, user_name, user_rol, message, created_at
                           FROM chat_messages
                           WHERE canal_id = ?
                           ORDER BY id DESC
                           LIMIT 30");
        $h->bind_param('i', $canal_id);
        $h->execute();
        $rows = array_reverse($h->get_result()->fetch_all(MYSQLI_ASSOC));
        $h->close();

        foreach ($rows as $row) {
            $last_id = max($last_id, (int)$row['id']);
            echo "id: {$row['id']}\nevent: message\ndata: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n\n";
        }
        flush();
    } catch (Throwable $e) {}
}

// Liberar el lock de sesión para no bloquear otras peticiones del mismo usuario
session_write_close();

// ── Loop principal ────────────────────────────────────────────────────────────
$start          = time();
$last_heartbeat = 0;
$timeout        = 25; // segundos antes de forzar reconexión del cliente

while (!connection_aborted()) {

    if (time() - $start >= $timeout) {
        echo "event: timeout\ndata: {}\n\n";
        flush();
        break;
    }

    try {
        // Nuevos mensajes
        $s = $db->prepare("SELECT id, user_id, user_name, user_rol, message, created_at
                           FROM chat_messages
                           WHERE canal_id = ? AND id > ?
                           ORDER BY id ASC
                           LIMIT 20");
        $s->bind_param('ii', $canal_id, $last_id);
        $s->execute();
        $res = $s->get_result();
        $sent = false;

        while ($row = $res->fetch_assoc()) {
            $last_id = (int)$row['id'];
            echo "id: {$row['id']}\nevent: message\ndata: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n\n";
            $sent = true;
        }
        $s->close();

        // Heartbeat cada 5 s: actualizar presencia + contar usuarios online
        $now = time();
        if ($now - $last_heartbeat >= 5) {
            if ($session_id) {
                try {
                    $u = $db->prepare("INSERT INTO chat_online (session_id, user_id, canal_id)
                                       VALUES (?, ?, ?)
                                       ON DUPLICATE KEY UPDATE last_seen = NOW()");
                    $u->bind_param('sii', $session_id, $user_id, $canal_id);
                    $u->execute();
                    $u->close();
                } catch (Throwable $e) {}
            }

            $c = $db->prepare("SELECT COUNT(DISTINCT session_id) AS cnt
                               FROM chat_online
                               WHERE canal_id = ? AND last_seen > DATE_SUB(NOW(), INTERVAL 35 SECOND)");
            $c->bind_param('i', $canal_id);
            $c->execute();
            $cnt = (int)($c->get_result()->fetch_assoc()['cnt'] ?? 0);
            $c->close();

            echo "event: heartbeat\ndata: " . json_encode(['users' => $cnt]) . "\n\n";
            $last_heartbeat = $now;
            $sent = true;
        }

        if ($sent) flush();

    } catch (Throwable $e) {
        // En error de BD, mandar ping vacío para mantener viva la conexión
        echo ": ping\n\n";
        flush();
    }

    usleep(500000); // 0.5 s
}
