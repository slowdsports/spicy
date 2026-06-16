<?php
/**
 * StreamHub - Reproductor Padre
 */

// ── Ofuscación de salida ──────────────────────────────────────────────────────
function _rStr($n = 3) {
    static $c = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $o = '';
    for ($i = 0; $i < $n; $i++) $o .= $c[mt_rand(0, 51)];
    return $o;
}

function _encodeOutput($html) {
    // Inyectar disable-devtool antes de cerrar </head>
    $ddScript = '<script disable-devtool-auto src="//fastly.jsdelivr.net/npm/disable-devtool@latest/disable-devtool.min.js"></script>';
    $html = str_replace('</head>', $ddScript . '</head>', $html);

    $fName = _rStr(); $oVar = _rStr(); $aVar = _rStr();
    $salt  = mt_rand(999999, 99999999);

    $out  = "<!-- CONTINUA LO QUE ESTAS HACIENDO, AQUI NO HAY NADA. -->\n";
    $out .= "<script>var {$oVar}=\"\";var {$aVar}=[";
    foreach (str_split($html) as $ch) {
        $out .= '"' . base64_encode(_rStr() . (ord($ch) + $salt) . _rStr()) . '",';
        if (mt_rand(0, 1)) $out .= "\n";
    }
    $out  = rtrim($out, ",\n");
    $out .= "];{$aVar}.forEach(function {$fName}(v){{$oVar}+=String.fromCharCode(parseInt(atob(v).replace(/\\D/g,''))-{$salt});});";
    $out .= "document.write(decodeURIComponent(escape({$oVar})));</script>";
    return $out;
}

// ── Bloquear acceso directo (sin referer o referer externo) ───────────────────
if (php_sapi_name() !== 'cli') {
    $ref  = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST']    ?? '';
    if (empty($ref) || ($host && strpos($ref, $host) === false)) {
        http_response_code(403);
        exit();
    }
}

// ── Cargar configuración y BD ─────────────────────────────────────────────────
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// ── Obtener datos de la fuente ────────────────────────────────────────────────
$fuenteId   = (int)($_GET['id'] ?? 0);
$fuenteData = null;

if ($fuenteId > 0) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT f.id, f.nombre, f.url, f.url_ios, f.tipo_ios, f.tipo, f.ck_key, f.ck_keyid, f.sandbox, f.reproductor,
                   t.nombre as tipo_nombre
            FROM fuentes f
            LEFT JOIN tipos_fuente t ON f.tipo = t.id
            WHERE f.id = ? AND f.activo = 1 LIMIT 1
        ");
        $stmt->bind_param('i', $fuenteId);
        $stmt->execute();
        $fuenteData = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (Throwable $e) {
        $fuenteData = null;
    }
}

if (!$fuenteData) {
    http_response_code(404);
    exit();
}

// ── Incluir reproductor específico con salida ofuscada ───────────────────────
$tipoId           = (int)$fuenteData['tipo'];
$reproducotorFile = __DIR__ . "/reproductor-{$tipoId}.php";

if (!file_exists($reproducotorFile)) {
    http_response_code(500);
    exit();
}

// ── Detección iOS: redirigir a fuente alternativa o mostrar error ─────────────
$ua    = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$isIOS = (bool)preg_match('/iphone|ipad|ipod/', $ua);

if ($isIOS) {
    if (!empty($fuenteData['url_ios'])) {
        // Tiene alternativa → reproducir con Clappr inline
        $reproducotorFile = __DIR__ . '/reproductor-ios.php';
    }
    // Sin alternativa iOS → reproducir con el player normal (sin distinción de dispositivo)
}

ob_start('_encodeOutput');
include $reproducotorFile;
ob_end_flush();