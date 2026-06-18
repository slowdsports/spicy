<?php
/**
 * Proxy Keepalive — despierta los proxies de Render.com antes de que duerman.
 *
 * Render Free tier duerme tras ~15 min de inactividad. El arranque en frío
 * tarda ~50 s, así que este script usa curl_multi para despertar todos los
 * proxies activos EN PARALELO en lugar de uno por uno.
 *
 * ── Cron en cPanel (cada 14 minutos) ─────────────────────────────────────────
 *   Via CLI (recomendado):
 *     * /14 * * * * php /home/user/public_html/cron/proxy_keepalive.php >> /home/user/logs/proxy.log 2>&1
 *
 *   Via HTTP (curl):
 *     * /14 * * * * curl -s --max-time 120 "https://tusitio.com/cron/proxy_keepalive.php?key=TU_CLAVE" >> /home/user/logs/proxy.log 2>&1
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── Clave de acceso (solo para invocación HTTP) ───────────────────────────────
const KEEPALIVE_SECRET = '';

// ── Timeout por proxy (segundos) — Render tarda hasta 60 s en arranque frío ──
const CURL_TIMEOUT = 90;

// ── Archivo de log ────────────────────────────────────────────────────────────
const LOG_FILE = __DIR__ . '/../data/logs/proxy_keepalive.log';
const LOG_MAX  = 500 * 1024; // 500 KB — rotación simple

// =============================================================================

$isCli = (php_sapi_name() === 'cli');

// Seguridad HTTP
if (!$isCli) {
    if (($_GET['key'] ?? '') !== KEEPALIVE_SECRET) {
        http_response_code(403);
        exit("403 Forbidden\n");
    }
    header('Content-Type: text/plain; charset=utf-8');
}

define('ROOT', dirname(__DIR__));
require_once ROOT . '/includes/config.php';
require_once ROOT . '/includes/db.php';

// ── Helper de log ─────────────────────────────────────────────────────────────
function kLog(string $msg): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    echo $line;

    $dir = dirname(LOG_FILE);
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    // Rotación simple: si supera el límite, truncar al último 20 %
    if (file_exists(LOG_FILE) && filesize(LOG_FILE) > LOG_MAX) {
        $content = file_get_contents(LOG_FILE);
        file_put_contents(LOG_FILE, substr($content, (int)(LOG_MAX * 0.8)));
    }

    file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

// ── Obtener proxies activos ───────────────────────────────────────────────────
$proxies = [];

try {
    $conn = getDBConnection();
    $res  = $conn->query("SELECT id, nombre, url FROM proxies WHERE activo = 1 ORDER BY id ASC");
    if ($res) {
        $proxies = $res->fetch_all(MYSQLI_ASSOC);
    }
} catch (Throwable $e) {
    kLog("ERROR al leer proxies de la BD: " . $e->getMessage());
    exit(1);
}

if (empty($proxies)) {
    kLog("Sin proxies activos. Nada que hacer.");
    exit(0);
}

kLog("=== Inicio keepalive — " . count($proxies) . " proxy(s) activo(s) ===");

// ── Ping en paralelo con curl_multi ──────────────────────────────────────────
$mh      = curl_multi_init();
$handles = [];

foreach ($proxies as $proxy) {
    // La URL ya tiene trailing-slash por diseño del sistema de proxies
    $url  = rtrim($proxy['url'], '/') . '/';
    $name = $proxy['nombre'] ?: "Proxy #{$proxy['id']}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => CURL_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => CURL_TIMEOUT,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_SSL_VERIFYPEER => false,   // Render usa cert válido, pero por si acaso
        CURLOPT_USERAGENT      => 'StreamHub-Keepalive/1.0',
        CURLOPT_HTTPHEADER     => ['Accept: text/plain,*/*'],
    ]);

    $handles[$proxy['id']] = ['handle' => $ch, 'url' => $url, 'name' => $name, 'start' => microtime(true)];
    curl_multi_add_handle($mh, $ch);
}

// Ejecutar todas las solicitudes en paralelo
$running = null;
do {
    $status = curl_multi_exec($mh, $running);
    if ($running > 0) curl_multi_select($mh, 1.0);
} while ($running > 0 && $status === CURLM_OK);

// ── Procesar resultados ───────────────────────────────────────────────────────
$ok = 0;
$fail = 0;

foreach ($handles as $id => $info) {
    $ch      = $info['handle'];
    $elapsed = round(microtime(true) - $info['start'], 1);
    $http    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err     = curl_error($ch);

    if ($err) {
        kLog("  FAIL  [{$info['name']}] {$info['url']} — Error: {$err} ({$elapsed}s)");
        $fail++;
    } elseif ($http >= 200 && $http < 400) {
        kLog("  OK    [{$info['name']}] {$info['url']} — HTTP {$http} ({$elapsed}s)");
        $ok++;
    } else {
        kLog("  WARN  [{$info['name']}] {$info['url']} — HTTP {$http} ({$elapsed}s)");
        $fail++;
    }

    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}

curl_multi_close($mh);

kLog("=== Fin: {$ok} OK, {$fail} fallidos ===");
