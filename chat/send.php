<?php
/**
 * Chat send — recibe un mensaje POST y lo inserta en BD
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

function jsonOut(bool $ok, string $msg = '', array $extra = []): void {
    echo json_encode(array_merge(['ok' => $ok, 'msg' => $msg], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(false, 'Método no permitido');
}

if (!isLoggedIn()) {
    jsonOut(false, 'Debes iniciar sesión para chatear');
}

$canal_id = 0;   // chat global
$message  = trim($_POST['msg'] ?? '');
if ($message === '') jsonOut(false, 'El mensaje no puede estar vacío');

// Eliminar etiquetas HTML y limitar longitud
$message = strip_tags($message);
$message = mb_substr($message, 0, 500, 'UTF-8');

if ($message === '') jsonOut(false, 'Mensaje inválido');

// Rate limit: mínimo 800 ms entre mensajes (vía sesión)
$rate_key = 'chat_rl_global';
$now_ms   = microtime(true);

if (isset($_SESSION[$rate_key]) && ($now_ms - $_SESSION[$rate_key]) < 0.8) {
    jsonOut(false, 'Espera un momento antes de enviar otro mensaje');
}
$_SESSION[$rate_key] = $now_ms;

$user_id   = userId();
$user_name = userName();
$user_rol  = $_SESSION['user_rol'] ?? 'usuario';

// Normalizar rol a los valores permitidos en el ENUM
if (!in_array($user_rol, ['admin', 'spicy', 'usuario'])) {
    $user_rol = 'usuario';
}

try {
    $db = getDBConnection();

    // Crear tabla si no existe (idempotente)
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

    $stmt = $db->prepare("INSERT INTO chat_messages (canal_id, user_id, user_name, user_rol, message)
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iisss', $canal_id, $user_id, $user_name, $user_rol, $message);
    $stmt->execute();
    $new_id = (int)$db->insert_id;
    $stmt->close();

    jsonOut(true, '', ['id' => $new_id]);

} catch (Throwable $e) {
    jsonOut(false, 'Error al enviar el mensaje');
}
