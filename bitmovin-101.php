<?php
/**
 * Reproductor standalone — Fuente 101 con Bitmovin, sin anuncios.
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$fuenteId = 101;
$fuenteData = null;

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, nombre, url, ck_key, ck_keyid FROM fuentes WHERE id = ? AND activo = 1 LIMIT 1");
    $stmt->bind_param('i', $fuenteId);
    $stmt->execute();
    $fuenteData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (Throwable $e) {
    $fuenteData = null;
}

if (!$fuenteData) {
    http_response_code(404);
    die('Fuente no disponible');
}

$nombre  = htmlspecialchars($fuenteData['nombre']);
$jsURL   = json_encode($fuenteData['url']);
$jsKEYID = json_encode($fuenteData['ck_keyid'] ?? '');
$jsKEY   = json_encode($fuenteData['ck_key']   ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="cache-control" content="no-cache">
    <meta http-equiv="pragma" content="no-cache">
    <meta name="robots" content="noindex">
    <meta name="referrer" content="none">
    <title><?= $nombre ?></title>

    <script src="//cdn.bitmovin.com/player/web/8/bitmovinplayer.js"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #000; overflow: hidden; }
        #wrapper { position: relative; width: 100%; height: 100vh; }
        #player { width: 100%; height: 100%; background: #000; }
        #status {
            position: absolute;
            inset: 0;
            z-index: 100;
            background: #000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 18px;
            padding: 32px;
            text-align: center;
        }
        .spinner {
            width: 46px; height: 46px;
            border: 4px solid rgba(255,255,255,0.12);
            border-top-color: #8b5cf6;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .s-title { font-size: 1.15em; font-weight: 600; color: #fff; }
    </style>
</head>
<body>
<div id="wrapper">
    <div id="status">
        <div class="spinner"></div>
        <div class="s-title">Cargando canal...</div>
    </div>
    <div id="player"></div>
</div>

<script>
var url      = <?= $jsURL ?>;
var ck_keyid = <?= $jsKEYID ?>;
var ck_key   = <?= $jsKEY ?>;

var statusEl = document.getElementById('status');
function showPlayer() { statusEl.style.display = 'none'; }
function showError(msg) {
    statusEl.innerHTML = '<div class="s-title">' + msg + '</div>';
}

// Interceptar peticiones de licencia Bitmovin para omitir validación
(function () {
    var GRANT = 'data:text/plain;charset=utf-8;base64,eyJzdGF0dXMiOiJncmFudGVkIiwibWVzc2FnZSI6IlRoZXJlIHlvdSBnby4ifQ==';
    function override(u) {
        if (u.indexOf('licensing.bitmovin.com/licensing')  > -1) return GRANT;
        if (u.indexOf('licensing.bitmovin.com/impression') > -1) return GRANT;
        return u;
    }
    var _open = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function () {
        arguments[1] = override(arguments[1]);
        return _open.apply(this, arguments);
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    var player = new bitmovin.player.Player(document.getElementById('player'), {
        key:      '11d3698c-efdf-42f1-8769-54663995de2b',
        analytics: false,
        cast:     { enable: true },
        playback: { autoplay: true, muted: true },
        style:    { width: '100%', height: '100%' }
    });

    var source = { dash: url };
    if (ck_keyid && ck_key) {
        source.drm = { clearkey: [{ keyId: ck_keyid, key: ck_key }] };
    }

    player.load(source).then(showPlayer).catch(function (err) {
        showError('Error al cargar el stream.');
        console.error('Bitmovin error:', err);
    });
});
</script>
</body>
</html>
