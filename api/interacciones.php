<?php
/**
 * API - Interacciones de usuario (like, guardar, reportar)
 * POST /api/interacciones.php
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

$action   = trim($_POST['action'] ?? '');
$fuenteId = (int)($_POST['fuente_id'] ?? 0);
$userId   = userId();

if ($fuenteId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'fuente_id inválido']);
    exit;
}

try {
    $conn = getDBConnection();

    switch ($action) {

        // ── LIKE ────────────────────────────────────────────────────
        case 'love': {
            $check = $conn->prepare("SELECT id FROM canal_likes WHERE user_id = ? AND fuente_id = ? LIMIT 1");
            $check->bind_param('ii', $userId, $fuenteId);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            $check->close();

            if ($exists) {
                $del = $conn->prepare("DELETE FROM canal_likes WHERE user_id = ? AND fuente_id = ?");
                $del->bind_param('ii', $userId, $fuenteId);
                $del->execute();
                $del->close();
                echo json_encode(['ok' => true, 'active' => false]);
            } else {
                $ins = $conn->prepare("INSERT IGNORE INTO canal_likes (user_id, fuente_id) VALUES (?, ?)");
                $ins->bind_param('ii', $userId, $fuenteId);
                $ins->execute();
                $ins->close();
                echo json_encode(['ok' => true, 'active' => true]);
            }
            break;
        }

        // ── GUARDAR ─────────────────────────────────────────────────
        case 'save': {
            $check = $conn->prepare("SELECT id FROM canal_guardados WHERE user_id = ? AND fuente_id = ? LIMIT 1");
            $check->bind_param('ii', $userId, $fuenteId);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            $check->close();

            if ($exists) {
                $del = $conn->prepare("DELETE FROM canal_guardados WHERE user_id = ? AND fuente_id = ?");
                $del->bind_param('ii', $userId, $fuenteId);
                $del->execute();
                $del->close();
                $active = false;
            } else {
                $ins = $conn->prepare("INSERT IGNORE INTO canal_guardados (user_id, fuente_id) VALUES (?, ?)");
                $ins->bind_param('ii', $userId, $fuenteId);
                $ins->execute();
                $ins->close();
                $active = true;
            }

            regenerarGuardadosJson($conn, $userId);

            echo json_encode(['ok' => true, 'active' => $active]);
            break;
        }

        // ── REPORTE ─────────────────────────────────────────────────
        case 'report': {
            $comentario = mb_substr(trim(strip_tags($_POST['comentario'] ?? '')), 0, 500);

            // Detección de IP
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
                ?? $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['REMOTE_ADDR']
                ?? '';
            $ip = mb_substr(trim(explode(',', $ip)[0]), 0, 45);

            // Dispositivo desde User-Agent
            $ua          = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $dispositivo = detectDevice($ua);

            // País via IP (timeout 2s para no bloquear)
            $pais = detectCountry($ip);

            $ins = $conn->prepare(
                "INSERT INTO canal_reportes (user_id, fuente_id, comentario, dispositivo, pais, ip)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $ins->bind_param('iissss', $userId, $fuenteId, $comentario, $dispositivo, $pais, $ip);
            $ins->execute();
            $ins->close();

            // Total de reportes para esta fuente
            $countRes      = $conn->query("SELECT COUNT(*) AS total FROM canal_reportes WHERE fuente_id = {$fuenteId}");
            $totalReportes = (int)($countRes->fetch_assoc()['total'] ?? 0);

            // Auto-desactivar al alcanzar 5 reportes
            if ($totalReportes >= 5) {
                $chkAuto = $conn->query(
                    "SELECT id FROM canal_autodesactivados WHERE fuente_id = {$fuenteId} LIMIT 1"
                );
                if ($chkAuto->num_rows === 0) {
                    $upd = $conn->prepare("UPDATE fuentes SET activo = 0 WHERE id = ?");
                    $upd->bind_param('i', $fuenteId);
                    $upd->execute();
                    $upd->close();

                    $razon = 'Acumuló ' . $totalReportes . ' reportes de usuarios';
                    $log   = $conn->prepare(
                        "INSERT INTO canal_autodesactivados (fuente_id, total_reportes, razon) VALUES (?, ?, ?)"
                    );
                    $log->bind_param('iis', $fuenteId, $totalReportes, $razon);
                    $log->execute();
                    $log->close();
                }
            }

            echo json_encode(['ok' => true, 'reportes' => $totalReportes]);
            break;
        }

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Acción no reconocida']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error interno']);
}

// ── HELPERS ───────────────────────────────────────────────────────────────────

function regenerarGuardadosJson(mysqli $conn, int $userId): void
{
    $res = $conn->query("
        SELECT f.id, f.nombre, c.logo
        FROM canal_guardados g
        JOIN fuentes  f ON f.id = g.fuente_id
        LEFT JOIN canales c ON c.id = f.canal
        WHERE g.user_id = {$userId}
        ORDER BY g.created_at DESC
    ");

    $items = [];
    while ($row = $res->fetch_assoc()) {
        $items[] = [
            'id'     => (int)$row['id'],
            'nombre' => $row['nombre'],
            'logo'   => $row['logo'] ?? '',
        ];
    }

    $dir = __DIR__ . '/../data/guardados';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents(
        $dir . '/' . $userId . '.json',
        json_encode(
            ['user_id' => $userId, 'updated_at' => date('c'), 'fuentes' => $items],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        )
    );
}

function detectDevice(string $ua): string
{
    $ua = strtolower($ua);
    if (preg_match('/mobile|android|iphone|ipod|windows phone/', $ua)) return 'Móvil';
    if (preg_match('/tablet|ipad/', $ua)) return 'Tablet';
    return 'Escritorio';
}

function detectCountry(string $ip): string
{
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return '';
    }
    $ctx  = stream_context_create(['http' => ['timeout' => 2]]);
    $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country", false, $ctx);
    if (!$json) return '';
    $data = json_decode($json, true);
    return $data['country'] ?? '';
}
