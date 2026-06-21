<?php
/**
 * api/stream.php — Endpoint seguro de datos de stream DASH.
 *
 * Valida un token HMAC vinculado a la sesión PHP activa del usuario antes de
 * devolver la URL y las DRM keys. Sin sesión válida + firma correcta + ventana
 * temporal activa → 401/403. Jamás devuelve datos a peticiones anónimas.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

// Bloquear acceso fuera del mismo origen
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host   = $_SERVER['HTTP_HOST']   ?? '';
if ($origin && $host && strpos($origin, $host) === false) {
    http_response_code(403);
    echo json_encode(['error' => 'Origen no permitido']);
    exit;
}

function fail(string $msg, int $code = 403): void {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

// ── Parámetros ────────────────────────────────────────────────────────────────
$fuenteId = (int)($_GET['id'] ?? 0);
$ts       = (int)($_GET['ts'] ?? 0);
$tok      = trim($_GET['t'] ?? '');

if ($fuenteId <= 0 || $ts <= 0 || strlen($tok) < 32) {
    fail('Parámetros inválidos', 400);
}

// ── Ventana temporal: ±15 minutos ─────────────────────────────────────────────
if (abs(time() - $ts) > 900) {
    fail('Token expirado', 401);
}

// ── Firma HMAC vinculada al session_id del usuario ────────────────────────────
// Si el usuario no tiene sesión activa, session_id() devuelve '' y la firma
// nunca coincidirá con ningún token legítimo generado en reproductor.php.
$expected = hash_hmac('sha256', $fuenteId . '|' . $ts . '|' . session_id(), APP_SECRET);
if (!hash_equals($expected, $tok)) {
    fail('Firma inválida', 401);
}

// ── Fuente desde BD ───────────────────────────────────────────────────────────
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare(
        "SELECT id, url, url_ios, tipo_ios, ck_key, ck_keyid, sandbox, usar_proxy
           FROM fuentes
          WHERE id = ? AND activo = 1
          LIMIT 1"
    );
    $stmt->bind_param('i', $fuenteId);
    $stmt->execute();
    $fuente = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (Throwable $e) {
    fail('Error interno', 500);
}

if (!$fuente) {
    fail('Canal no disponible', 404);
}

// ── Proxy geo-protección (Spicy / Admin) ─────────────────────────────────────
if (!empty($fuente['usar_proxy']) && (isSpicy() || isAdmin())) {
    try {
        $proxyRows = $conn->query("SELECT url FROM proxies WHERE activo = 1")->fetch_all(MYSQLI_ASSOC);
        if (!empty($proxyRows)) {
            $base = $proxyRows[array_rand($proxyRows)]['url'];
            $fuente['url'] = $base . $fuente['url'];
            if (!empty($fuente['url_ios']) && ($fuente['tipo_ios'] ?? 'hls') !== 'iframe') {
                $fuente['url_ios'] = $base . $fuente['url_ios'];
            }
        }
    } catch (Throwable $e) { /* best-effort */ }
}

// ── Respuesta ─────────────────────────────────────────────────────────────────
echo json_encode([
    'url'     => $fuente['url']      ?? '',
    'url_ios' => $fuente['url_ios']  ?? '',
    'keyId'   => $fuente['ck_keyid'] ?? '',
    'key'     => $fuente['ck_key']   ?? '',
    'sandbox' => (bool)($fuente['sandbox'] ?? true),
]);
