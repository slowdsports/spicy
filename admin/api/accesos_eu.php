<?php
/**
 * Admin API - Gestión de accesos europeos
 * POST /admin/api/accesos_eu.php
 * Acciones: aprobar, denegar
 */
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/eu_check.php';

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';
$id     = (int)($input['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit();
}

try {
    $conn = getDBConnection();
    _euEnsureTable();

    if ($action === 'aprobar') {
        $stmt = $conn->prepare(
            "UPDATE eu_access SET estado = 'aprobado', updated_at = NOW() WHERE id = ?"
        );
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        euResp($ok, $ok ? 'Acceso aprobado' : 'Error al aprobar');
    }

    if ($action === 'denegar') {
        $stmt = $conn->prepare(
            "UPDATE eu_access SET estado = 'denegado', updated_at = NOW() WHERE id = ?"
        );
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        euResp($ok, $ok ? 'Acceso denegado' : 'Error al denegar');
    }

    euResp(false, 'Acción no reconocida');

} catch (Throwable $e) {
    euResp(false, 'Error del servidor: ' . $e->getMessage());
}

function euResp(bool $ok, string $msg): void {
    echo json_encode(['success' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit();
}
