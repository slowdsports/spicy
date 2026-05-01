<?php
/**
 * Admin API - Gestión de usuarios & acceso Spicy
 */
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';

try {
    $conn = getDBConnection();

    // ── GRANT SPICY ───────────────────────────────────────────────
    if ($action === 'grant_spicy') {
        $userId = (int)($input['user_id'] ?? 0);
        $cafes  = max(1, (int)($input['cafes'] ?? 1));
        $meses  = max(1, (int)($input['meses'] ?? $cafes));
        $notas  = trim($input['notas'] ?? '') ?: null;

        if (!$userId) resp(false, 'Usuario inválido');

        $stmt = $conn->prepare("SELECT rol, spicy_hasta FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) resp(false, 'Usuario no encontrado');

        // Extend from current expiry if still active, otherwise from now
        $base = ($user['spicy_hasta'] && strtotime($user['spicy_hasta']) > time())
            ? new DateTime($user['spicy_hasta'])
            : new DateTime();
        $base->add(new DateInterval("P{$meses}M"));
        $newExpiry = $base->format('Y-m-d H:i:s');

        $stmt2 = $conn->prepare("UPDATE usuarios SET rol = 'spicy', spicy_hasta = ? WHERE id = ?");
        $stmt2->bind_param('si', $newExpiry, $userId);
        $ok = $stmt2->execute();
        $stmt2->close();

        if (!$ok) resp(false, 'Error al actualizar usuario');

        $stmt3 = $conn->prepare("INSERT INTO donaciones (user_id, cafes, meses, notas) VALUES (?,?,?,?)");
        $stmt3->bind_param('iiis', $userId, $cafes, $meses, $notas);
        $stmt3->execute();
        $stmt3->close();

        resp(true, "Spicy concedido hasta {$newExpiry}");
    }

    // ── REVOKE SPICY ──────────────────────────────────────────────
    if ($action === 'revoke_spicy') {
        $userId = (int)($input['user_id'] ?? 0);
        if (!$userId) resp(false, 'Usuario inválido');

        $stmt = $conn->prepare("UPDATE usuarios SET rol = 'usuario', spicy_hasta = NULL WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $ok = $stmt->execute();
        $stmt->close();

        resp($ok, $ok ? 'Acceso Spicy revocado' : 'Error al revocar');
    }

    // ── DELETE DONATION ───────────────────────────────────────────
    if ($action === 'delete_donacion') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) resp(false, 'ID inválido');

        $stmt = $conn->prepare("DELETE FROM donaciones WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();

        resp($ok, $ok ? 'Donación eliminada' : 'Error al eliminar');
    }

    resp(false, 'Acción no reconocida');

} catch (Throwable $e) {
    resp(false, 'Error del servidor: ' . $e->getMessage());
}

function resp(bool $ok, string $msg): void {
    echo json_encode(['success' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit();
}
