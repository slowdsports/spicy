<?php
/**
 * API - Suscripción / desuscripción a notificaciones push (Web Push)
 * POST /api/push_subscribe.php
 *
 * El navegador ya hizo pushManager.subscribe() en el cliente (ver
 * assets/js/push.js) — acá solo guardamos/borramos ese objeto de
 * suscripción atado al usuario logueado, para que cron/push_notify.php
 * sepa a quién avisarle cuando juega un equipo favorito.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autenticado']);
    exit;
}

$raw  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $raw['action'] ?? '';
$userId = userId();

try {
    $conn = getDBConnection();

    // Auto-migración — igual que equipo_guardados, para no depender de correr
    // SQL a mano en el servidor al desplegar esta feature.
    $conn->query("
        CREATE TABLE IF NOT EXISTS push_subscriptions (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id INT UNSIGNED NOT NULL,
            endpoint VARCHAR(500) NOT NULL,
            p256dh VARCHAR(255) NOT NULL,
            auth VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_endpoint (endpoint(255)),
            KEY idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    if ($action === 'subscribe') {
        $sub      = $raw['subscription'] ?? [];
        $endpoint = trim((string)($sub['endpoint'] ?? ''));
        $p256dh   = trim((string)($sub['keys']['p256dh'] ?? ''));
        $auth     = trim((string)($sub['keys']['auth'] ?? ''));

        if ($endpoint === '' || $p256dh === '' || $auth === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Suscripción inválida']);
            exit;
        }

        // p256dh debe ser una clave pública EC sin comprimir (65 bytes, arranca
        // con 0x04) en base64url — si no, cron/push_notify.php truena al cifrar
        // el payload para ESTA fila y frena el aviso de TODOS los demás usuarios
        // en la misma corrida. Se rechaza acá antes de que entre a la tabla.
        $rawKey = base64_decode(strtr($p256dh, '-_', '+/'), true);
        if ($rawKey === false || strlen($rawKey) !== 65 || $rawKey[0] !== "\x04") {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Clave de suscripción inválida']);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), p256dh = VALUES(p256dh), auth = VALUES(auth)
        ");
        $stmt->bind_param('isss', $userId, $endpoint, $p256dh, $auth);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['ok' => true]);
    } elseif ($action === 'unsubscribe') {
        $endpoint = trim((string)($raw['endpoint'] ?? ''));
        if ($endpoint !== '') {
            $stmt = $conn->prepare("DELETE FROM push_subscriptions WHERE user_id = ? AND endpoint = ?");
            $stmt->bind_param('is', $userId, $endpoint);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'Acción no reconocida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error interno']);
}
