<?php
/**
 * Chat poll — reemplaza SSE para compatibilidad con hosting compartido.
 * El cliente llama cada ~2 s; devuelve mensajes nuevos + conteo de usuarios.
 */

ini_set('display_errors', '0');
error_reporting(0);
while (ob_get_level() > 0) ob_end_clean();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');

if (session_status() === PHP_SESSION_NONE) session_start();
$session_id = session_id();
$user_id    = userId();
session_write_close();

$canal_id = intval($_GET['canal']   ?? 0);
$last_id  = intval($_GET['last_id'] ?? -1);  // -1 = primera llamada (cargar historial)

if ($canal_id <= 0) {
    echo json_encode(['ok' => false]);
    exit;
}

try {
    $db = getDBConnection();

    // Crear tablas solo en la primera llamada
    if ($last_id < 0) {
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
    }

    // Actualizar presencia del usuario en este canal
    if ($session_id) {
        $p = $db->prepare("INSERT INTO chat_online (session_id, user_id, canal_id)
                           VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE last_seen = NOW()");
        $p->bind_param('sii', $session_id, $user_id, $canal_id);
        $p->execute();
        $p->close();
    }

    // Primera llamada: historial (30 últimos mensajes)
    if ($last_id < 0) {
        $h = $db->prepare("SELECT id, user_id, user_name, user_rol, message, created_at
                           FROM chat_messages
                           WHERE canal_id = ?
                           ORDER BY id DESC LIMIT 30");
        $h->bind_param('i', $canal_id);
        $h->execute();
        $messages = array_reverse($h->get_result()->fetch_all(MYSQLI_ASSOC));
        $h->close();
    } else {
        // Poll normal: solo mensajes nuevos
        $s = $db->prepare("SELECT id, user_id, user_name, user_rol, message, created_at
                           FROM chat_messages
                           WHERE canal_id = ? AND id > ?
                           ORDER BY id ASC LIMIT 50");
        $s->bind_param('ii', $canal_id, $last_id);
        $s->execute();
        $messages = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();
    }

    // Contar usuarios online (visto en los últimos 35 s)
    $c = $db->prepare("SELECT COUNT(DISTINCT session_id) AS cnt
                       FROM chat_online
                       WHERE canal_id = ? AND last_seen > DATE_SUB(NOW(), INTERVAL 35 SECOND)");
    $c->bind_param('i', $canal_id);
    $c->execute();
    $online = (int)($c->get_result()->fetch_assoc()['cnt'] ?? 0);
    $c->close();

    echo json_encode(['ok' => true, 'messages' => $messages, 'users' => $online],
                     JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode(['ok' => false]);
}
