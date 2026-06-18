<?php
/**
 * api/stream.php — Endpoint seguro de datos de reproducción.
 *
 * Los reproductores NUNCA reciben URL ni DRM keys en el HTML/JS fuente.
 * En su lugar, hacen fetch a este endpoint con un token HMAC vinculado
 * a la sesión del usuario y con ventana de tiempo de 20 minutos.
 *
 * Flujo:
 *   1. reproductor.php genera: ts = time(), token = HMAC(id|ts|session_id, SECRET)
 *   2. El JS del reproductor llama: GET api/stream.php?id=X&t=TOKEN&ts=TS
 *   3. Este endpoint valida firma + ventana temporal + sesión
 *   4. Aplica proxy si corresponde y devuelve {url, keyId, key, ...}
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
// Sólo accesible desde el mismo origen
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_HOST'] ?? ''));

function failStream(string $msg, int $code = 403): void {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

// ── Parámetros ──────────────────────────────────────────────────────────────
$fuenteId = (int)($_GET['id'] ?? 0);
$tok      = trim($_GET['t']  ?? '');
$ts       = (int)($_GET['ts'] ?? 0);

if (!$fuenteId || $tok === '' || !$ts) {
    failStream('Parámetros requeridos', 400);
}

// ── Ventana de tiempo: 20 minutos ───────────────────────────────────────────
if (abs(time() - $ts) > 1200) {
    failStream('Token expirado');
}

// ── Validar firma HMAC ──────────────────────────────────────────────────────
// Mismo cálculo que hace reproductor.php al generar el token
$expected = hash_hmac('sha256', $fuenteId . '|' . $ts . '|' . session_id(), APP_SECRET);
if (!hash_equals($expected, $tok)) {
    failStream('Firma inválida');
}

// ── Obtener datos de la fuente ───────────────────────────────────────────────
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT id, url, url_ios, tipo_ios, ck_key, ck_keyid, sandbox, usar_proxy, tipo
        FROM fuentes WHERE id = ? AND activo = 1 LIMIT 1
    ");
    $stmt->bind_param('i', $fuenteId);
    $stmt->execute();
    $fuente = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (Throwable $e) {
    failStream('Error interno', 500);
}

if (!$fuente) {
    failStream('Fuente no encontrada', 404);
}

// ── Proxy geo-protección ─────────────────────────────────────────────────────
$url    = $fuente['url'];
$urlIos = $fuente['url_ios'] ?? '';

if (!empty($fuente['usar_proxy']) && (isSpicy() || isAdmin())) {
    try {
        $proxyRows = getDBConnection()
            ->query("SELECT url FROM proxies WHERE activo = 1")
            ->fetch_all(MYSQLI_ASSOC);
        if (!empty($proxyRows)) {
            $proxyBase = $proxyRows[array_rand($proxyRows)]['url'];
            $url = $proxyBase . $url;
            if (!empty($urlIos) && ($fuente['tipo_ios'] ?? 'hls') !== 'iframe') {
                $urlIos = $proxyBase . $urlIos;
            }
        }
    } catch (Throwable $e) { /* proxy best-effort */ }
}

// ── Respuesta ────────────────────────────────────────────────────────────────
echo json_encode([
    'url'      => $url,
    'url_ios'  => $urlIos ?: null,
    'tipo_ios' => $fuente['tipo_ios'] ?? 'hls',
    'keyId'    => $fuente['ck_keyid'] ?? '',
    'key'      => $fuente['ck_key']   ?? '',
    'sandbox'  => (int)($fuente['sandbox'] ?? 1),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
