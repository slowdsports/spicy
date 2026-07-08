<?php
/**
 * API pública — Marcador en vivo de un partido (proxy a FotMob).
 *
 * El id del partido ES el matchId real de FotMob (único proveedor en uso,
 * sin offset — ver admin/fotmob.php), así que se usa directo.
 *
 * FotMob no bloquea peticiones de servidor (curl simple + User-Agent basta,
 * a diferencia de Sofascore) así que no hace falta ningún bridge Playwright
 * para esto. Cachea la respuesta unos segundos en disco para no golpear
 * FotMob por cada usuario que tenga el partido abierto al mismo tiempo.
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

const LIVE_SCORE_CACHE_TTL = 12; // segundos

function respond(array $data, int $httpCode = 200): never {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$fotmobMatchId = (int)($_GET['id'] ?? 0);

if (!$fotmobMatchId) {
    respond(['ok' => false, 'error' => 'id de partido inválido'], 404);
}

$cacheDir  = __DIR__ . '/../data/live_cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
$cacheFile = $cacheDir . '/' . $fotmobMatchId . '.json';

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < LIVE_SCORE_CACHE_TTL) {
    $cached = json_decode(file_get_contents($cacheFile), true);
    if ($cached !== null) respond($cached);
}

$ch = curl_init("https://www.fotmob.com/api/data/matchDetails?matchId={$fotmobMatchId}");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
]);
$body     = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($body === false || $httpCode !== 200) {
    // Si falla FotMob pero tenemos un valor viejo cacheado, mejor devolver
    // ese que nada — un marcador de hace 30s sigue siendo útil.
    if (file_exists($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if ($cached !== null) respond($cached);
    }
    respond(['ok' => false, 'error' => "FotMob respondió HTTP {$httpCode}"], 502);
}

$data = json_decode($body, true);
if ($data === null) {
    respond(['ok' => false, 'error' => 'respuesta de FotMob no es JSON válido'], 502);
}

$teams    = $data['header']['teams'] ?? [];
$status   = $data['header']['status'] ?? [];
$infoBox  = $data['content']['matchFacts']['infoBox'] ?? [];

// Minuto en vivo: FotMob manda liveTime.short con marcas bidi invisibles
// (ej. "73‎’‎") mezcladas con el apóstrofe — más simple armar
// el propio "N'" a partir de liveTime.long ("mm:ss"), que es texto plano.
// Medio tiempo se detecta por halfs (1er tiempo terminado, 2do sin empezar).
$minute = null;
if (!empty($status['started']) && empty($status['finished'])) {
    $halfs = $status['halfs'] ?? [];
    if (!empty($halfs['firstHalfEnded']) && empty($halfs['secondHalfStarted']) && empty($halfs['firstExtraHalfStarted'])) {
        $minute = 'MT';
    } else {
        $long = $status['liveTime']['long'] ?? '';
        if ($long !== '') {
            $mins = (int)strtok($long, ':');
            $minute = $mins . "'";
        }
    }
}

$result = [
    'ok'        => true,
    'homeScore' => isset($teams[0]['score']) ? (int)$teams[0]['score'] : null,
    'awayScore' => isset($teams[1]['score']) ? (int)$teams[1]['score'] : null,
    'started'   => (bool)($status['started'] ?? false),
    'finished'  => (bool)($status['finished'] ?? false),
    'cancelled' => (bool)($status['cancelled'] ?? false),
    'minute'    => $minute,
    'venue'     => $infoBox['Stadium']['name'] ?? null,
    'referee'   => $infoBox['Referee']['text'] ?? null,
];

file_put_contents($cacheFile, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

respond($result);
