<?php

/**
 * Stream Extractor API - sport.webcindario.com
 *
 * Uso:
 *   GET /stream_extractor.php              → canal por defecto (tudn)
 *   GET /stream_extractor.php?ch=tudn      → canal específico
 *   GET /stream_extractor.php?list=1       → lista todos los canales
 */

// ─── Cabeceras: siempre JSON, nunca caché ─────────────────────────────────────

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');          // CORS para reproductores web
header('Cache-Control: no-store, no-cache');
header('Pragma: no-cache');

// ─── Configuración ────────────────────────────────────────────────────────────

define('BASE_BRIDGE_URL', 'https://sport.webcindario.com');
define('TIMEOUT',         15);
define('USER_AGENT',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
    'AppleWebKit/537.36 (KHTML, like Gecko) ' .
    'Chrome/124.0.0.0 Safari/537.36'
);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function jsonOk(array $data): never
{
    echo json_encode(array_merge(['success' => true, 'ts' => time()], $data),
        JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function jsonErr(string $message, int $step = 0, int $http = 200): never
{
    http_response_code($http);
    echo json_encode([
        'success' => false,
        'error'   => $message,
        'step'    => $step,
        'ts'      => time(),
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function cleanUrl(string $url): string
{
    $url = trim(preg_replace('/[\r\n\t\s]/', '', $url));
    $url = str_replace(['\\/', '\\'], ['/', ''], $url);
    if (str_starts_with($url, '//')) {
        $url = 'https:' . $url;
    }
    return $url;
}

function httpGet(string $url, string $referer = ''): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => USER_AGENT,
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
        ],
    ]);
    if ($referer) {
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    }
    $body     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error    = curl_error($ch);
    curl_close($ch);
    return compact('body', 'httpCode', 'finalUrl', 'error');
}

// ─── PASO 1: URL puente → URL real ───────────────────────────────────────────

$r1 = httpGet(BASE_BRIDGE_URL);
if ($r1['error'] || $r1['httpCode'] !== 200) {
    jsonErr("No se pudo acceder a la URL puente ({$r1['httpCode']}): {$r1['error']}", 1);
}

if (!preg_match('/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>\s*sport\.webcindario\.com\s*<\/a>/i',
        $r1['body'], $m)) {
    jsonErr('No se encontro el enlace real en la URL puente', 1);
}

$realUrl  = cleanUrl($m[1]);
$p        = parse_url($realUrl);
$baseReal = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '');
$basePath = rtrim($p['path'] ?? '', '/');

// ─── PASO 2: Detectar main.php ────────────────────────────────────────────────

$r2 = httpGet($realUrl, BASE_BRIDGE_URL);
if ($r2['error'] || $r2['httpCode'] !== 200) {
    jsonErr("No se pudo acceder a la URL real ({$r2['httpCode']})", 2);
}

$mainFile = 'main.php';
if (preg_match('/src=["\']([^"\']+\.php[^"\']*)["\']/', $r2['body'], $mf)) {
    $candidate = basename(parse_url(cleanUrl($mf[1]), PHP_URL_PATH) ?? '');
    if ($candidate && str_ends_with($candidate, '.php')) {
        $mainFile = $candidate;
    }
}

$mainPhpUrl = $baseReal . $basePath . '/' . $mainFile;

// ─── PASO 3: Lista de canales ─────────────────────────────────────────────────

$r3 = httpGet($mainPhpUrl, $realUrl);
if ($r3['error'] || $r3['httpCode'] !== 200) {
    jsonErr("No se pudo obtener la lista de canales ({$r3['httpCode']})", 3);
}

preg_match_all(
    '/<a\s[^>]*href=["\']([^"\']*uv\.php\?ch=([^"\'&\s]+)[^"\']*)["\'][^>]*>([^<]+)<\/a>/i',
    $r3['body'], $channels, PREG_SET_ORDER
);

if (empty($channels)) {
    jsonErr('No se encontraron canales en la lista', 3);
}

$channelMap = [];
foreach ($channels as $ch) {
    $chId   = trim($ch[2]);
    $chHref = cleanUrl($ch[1]);
    if (!str_starts_with($chHref, 'http')) {
        $chHref = $baseReal . $basePath . '/' . ltrim($chHref, '/');
    }
    $channelMap[$chId] = [
        'id'   => $chId,
        'name' => trim($ch[3]),
        'url'  => $chHref,
    ];
}

// Si solo piden la lista → devolver y salir
if (!empty($_GET['list'])) {
    jsonOk(['channels' => array_values($channelMap)]);
}

// ─── PASO 4: Acceder al canal solicitado ─────────────────────────────────────

$target = trim($_GET['id'] ?? $_GET['ch'] ?? 'tudn');  // acepta ?id= o ?ch= (retrocompatible)

if (!isset($channelMap[$target])) {
    jsonErr(
        "Canal '{$target}' no encontrado. Disponibles: " . implode(', ', array_keys($channelMap)),
        4
    );
}

$ch   = $channelMap[$target];
$r4   = httpGet($ch['url'], $mainPhpUrl);

if ($r4['error'] || $r4['httpCode'] !== 200) {
    jsonErr("No se pudo acceder al canal '{$target}' ({$r4['httpCode']})", 4);
}

// ─── PASO 5: Extraer onclick → URL del stream ────────────────────────────────

$streamPath = null;
foreach ([
    '/onclick=["\']location\.href=["\']([^"\']+)["\']["\']/',
    '/onclick=["\']location\.href=([^\s"\'>;]+)/',
    "/location\.href\s*=\s*['\"]([^'\"]+)['\"]/",
] as $pat) {
    if (preg_match($pat, $r4['body'], $m)) {
        $streamPath = cleanUrl($m[1]);
        break;
    }
}

if (!$streamPath) {
    jsonErr('No se pudo extraer la URL del stream (onclick no encontrado)', 5);
}

// Construir URL completa del stream
if (str_starts_with($streamPath, 'http')) {
    $streamUrl = $streamPath;
} elseif (str_starts_with($streamPath, '/')) {
    $streamUrl = $baseReal . $streamPath;
} else {
    $streamUrl = $baseReal . $basePath . '/' . ltrim($streamPath, '/');
}

// ─── PASO 6: Seguir URL y extraer .m3u8 ──────────────────────────────────────

$r5    = httpGet($streamUrl, $ch['url']);
$m3u8  = null;

if (preg_match('/https?:\/\/[^\s"\'<>]+\.m3u8[^\s"\'<>]*/i', $r5['body'], $mm)) {
    $m3u8 = $mm[0];
}

// ─── Respuesta JSON final ─────────────────────────────────────────────────────

if (!$m3u8) {
    jsonErr('Stream encontrado pero no se resolvio el .m3u8', 6);
}

jsonOk([
    'channel'    => $ch['id'],
    'name'       => $ch['name'],
    'final_url'  => $r5['finalUrl'],
]);
