<?php
/**
 * StreamHub - Reproductor Tipo 1: M3U8
 * ============================================================
 * Configuración para streams M3U8 (HLS simple)
 * Reproductores soportados: JW Player, Clappr
 * 
 * Características:
 * - Reproducción de streams HTTP Live Streaming
 * - Compatible con adaptabilidad de bitrate
 * - Controles nativos de reproducción
 */

if (!isset($fuenteData)) {
    die('Acceso denegado');
}

$nombre  = htmlspecialchars($fuenteData['nombre']);
$jsBase  = json_encode(BASE_URL);
$jsFid   = (int)$streamFuenteId;
$jsTok   = json_encode($streamToken);
$jsTs    = (int)$streamTs;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../includes/ads.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> - Tele Deportes</title>
    <link rel="stylesheet" href="../assets/css/jw.css">
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            background: #000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            overflow: hidden;
        }
        #wrapper {
            position: relative;
            width: 100%;
            height: 100vh;
        }
        #player-container {
            width: 100%;
            height: 100%;
            background: #000;
        }
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
            width: 46px;
            height: 46px;
            border: 4px solid rgba(255,255,255,0.12);
            border-top-color: #8b5cf6;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .s-title {
            font-size: 1.15em;
            font-weight: 600;
            color: #fff;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <div id="status">
            <div class="spinner"></div>
            <div class="s-title">Cargando canal...</div>
        </div>
        <div id="player-container"></div>
    </div>

    <!-- JW Player CDN -->
    <script src="//ssl.p.jwpcdn.com/player/v/8.24.0/jwplayer.js"></script>
    <script>jwplayer.key = 'XSuP4qMl+9tK17QNb+4+th2Pm9AWgMO/cYH8CI0HGGr7bdjo';</script>
    
    <!-- Clappr CDN -->
    <script src="https://cdn.clappr.io/latest/clappr.min.js"></script>

    <script>
        // ============================================================
        // BLOQUEAR INSPECTOR DE ELEMENTOS
        // ============================================================
        (function() {
            let devtools = { open: false };
            const threshold = 160;

            setInterval(function() {
                if (window.outerHeight - window.innerHeight > threshold ||
                    window.outerWidth - window.innerWidth > threshold) {
                    if (!devtools.open) {
                        devtools.open = true;
                    }
                } else {
                    devtools.open = false;
                }
            }, 500);

            document.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                return false;
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'C' || e.key === 'J'))) {
                    e.preventDefault();
                    return false;
                }
            });
        })();

        var statusEl = document.getElementById('status');
        function showPlayer() { statusEl.style.display = 'none'; }
        function showError(msg) {
            statusEl.innerHTML = '<div class="s-title" style="color:#ef4444;font-size:.95em;">⚠ ' + (msg || 'Error al cargar') + '</div>';
        }

        document.addEventListener('DOMContentLoaded', function () {
            fetch(<?= $jsBase ?> + 'api/stream.php?id=<?= $jsFid ?>&t=' + encodeURIComponent(<?= $jsTok ?>) + '&ts=<?= $jsTs ?>')
                .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
                .then(function (d) {
                    if (!d.url) { showError('Stream no disponible'); return; }
                    var container = document.getElementById('player-container');
                    jwplayer(container).setup({
                        file: d.url, type: 'hls',
                        autostart: true, controls: true,
                        width: '100%', height: '100%',
                        playback: { autostart: true, muted: true, dvrSeekLimit: 0 },
                        hlsVariantSelection: 'auto'
                    }).on('ready', showPlayer);
                })
                .catch(function () { showError('Error de conexión'); });
        });
    </script>
</body>
</html>
