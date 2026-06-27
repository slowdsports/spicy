<?php
/**
 * Reproductor JW Player: obtiene solo la URL .m3u8 (con el token vigente)
 * desde la fuente externa en cada carga, igual que wbc.php, para no servir
 * nunca un enlace con el token ya caducado. El diseño y la configuración
 * del player son los nuestros (ver pages/reproductor-1.php) — no los de
 * la página de origen.
 */

function ztHttpGet(string $url): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
            'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$body ?: '', $code];
}

// "1" es el id de canal/partido y puede cambiarse vía ?id=
$ztPlayerId = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id'] ?? '1');
[$ztBody, $ztCode] = ztHttpGet("https://zicotv.cc/api/player/{$ztPlayerId}");

$streamUrl = null;
if ($ztBody && $ztCode === 200 && preg_match('/https?:\/\/[^\s"\'<>]+\.m3u8[^\s"\'<>]*/i', $ztBody, $m)) {
    $streamUrl = $m[0];
}

if (!$streamUrl) {
    http_response_code(502);
    die('No se pudo obtener la URL del stream.');
}

// zicotv.cc bloquea (403) el manifest/segmentos cuando el navegador los pide
// directo desde otro dominio (anti-hotlinking), aunque sí responde a
// peticiones server-side. Se reproduce a través de nuestro propio proxy
// (api/m3u8proxy.php) para que el navegador nunca toque su CDN directamente.
$proxyStreamUrl = 'api/m3u8proxy.php?url=' . rawurlencode($streamUrl);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Reproductor</title>
    <link rel="stylesheet" href="assets/css/jw.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            height: 100%;
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
            border: 4px solid rgba(255, 255, 255, 0.12);
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
    </script>

    <script>
        // URL .m3u8 obtenida en PHP arriba, con el token vigente en este momento.
        var STREAM_URL = <?= json_encode($proxyStreamUrl) ?>;

        var statusEl = document.getElementById('status');
        function showPlayer() { statusEl.style.display = 'none'; }
        function showError(msg) {
            statusEl.innerHTML =
                '<div style="font-size:2rem;margin-bottom:8px;">⚠️</div>' +
                '<div class="s-title">' + msg + '</div>';
            statusEl.style.display = 'flex';
        }

        /**
         * REPRODUCTOR: JW Player
         * Misma configuración que pages/reproductor-1.php
         */
        function initPlayer() {
            jwplayer('player-container').setup({
                file: STREAM_URL,
                type: 'hls',

                autostart: true,
                controls: true,
                width: '100%',
                height: '100%',

                playback: {
                    autostart: true,
                    muted: true,
                    playInline: true,
                    dvrSeekLimit: 0
                },

                ui: {
                    controlbar: { settings: true }
                },

                hlsVariantSelection: 'auto',
            })
            .on('ready', showPlayer)
            .on('error', function () { showError('No se pudo reproducir el canal.'); });
        }

        document.addEventListener('DOMContentLoaded', initPlayer);
    </script>
</body>
</html>
