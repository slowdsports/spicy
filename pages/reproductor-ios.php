<?php
/**
 * StreamHub - Reproductor iOS
 * Se activa automáticamente en iPhone/iPad/iPod cuando la fuente
 * tiene url_ios configurada. Soporta dos modos:
 *   tipo_ios = 'hls'    → Clappr con la URL m3u8
 *   tipo_ios = 'iframe' → iframe embebido
 */

if (!isset($fuenteData)) {
    die('Acceso denegado');
}

$nombre   = htmlspecialchars($fuenteData['nombre']);
$urlIos   = $fuenteData['url_ios'] ?? '';
$tipoIos  = $fuenteData['tipo_ios'] ?? 'hls';
$jsURL    = json_encode($urlIos);
$jsNombre = json_encode($nombre);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../includes/ads.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> - Tele Deportes</title>

    <?php if ($tipoIos === 'hls'): ?>
    <script src="//cdn.jsdelivr.net/npm/clappr@latest/dist/clappr.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/level-selector@latest/dist/level-selector.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/clappr-pip@latest/dist/clappr-pip.min.js"></script>
    <?php endif; ?>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #000;
            overflow: hidden;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        #wrapper {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #player {
            width: 100%;
            height: 100%;
            display: none;
        }

        #status {
            width: 100%;
            height: 100%;
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

        .s-title   { font-size: 1.15em; font-weight: 600; color: #fff; }
        .s-subtitle { font-size: 0.88em; color: rgba(255,255,255,.55); max-width: 320px; line-height: 1.65; }

        iframe#player {
            display: block;
            border: none;
            background: #000;
        }
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
        var URL_IOS  = <?= $jsURL ?>;
        var TIPO_IOS = <?= json_encode($tipoIos) ?>;

        var statusEl = document.getElementById('status');
        var playerEl = document.getElementById('player');

        function showPlayer() {
            statusEl.style.display = 'none';
            playerEl.style.display = 'block';
        }

        function showError(msg) {
            statusEl.innerHTML =
                '<div style="font-size:2.5rem;">⚠️</div>' +
                '<div class="s-title">Error al cargar</div>' +
                '<div class="s-subtitle">' + msg + '</div>';
            statusEl.style.display = 'flex';
            playerEl.style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {

            if (TIPO_IOS === 'iframe') {
                // ── iFrame ─────────────────────────────────────────────────
                var iframe = document.createElement('iframe');
                iframe.src             = URL_IOS;
                iframe.style.cssText   = 'width:100%;height:100%;border:none;background:#000;';
                iframe.allowFullscreen = true;
                iframe.setAttribute('allow', 'autoplay; fullscreen');
                playerEl.appendChild(iframe);
                showPlayer();

            } else {
                // ── HLS / M3U8 con Clappr ─────────────────────────────────
                if (typeof Clappr === 'undefined') {
                    showError('No se pudo cargar el reproductor.');
                    return;
                }

                window.player = new Clappr.Player({
                    source:   URL_IOS,
                    parentId: '#player',
                    width:    '100%',
                    height:   '100%',
                    autoplay: true,
                    mute:     false,
                    playback: { playInline: true },
                    plugins:  [LevelSelector, ClapprPip.PipButton, ClapprPip.PipPlugin],
                    events: {
                        onReady: function () {
                            showPlayer();
                            var p = this.getPlugin('click_to_pause');
                            if (p) p.disable();
                        },
                        onError: function () {
                            showError('El canal no está disponible en este momento.');
                        }
                    }
                });
            }
        });

        document.addEventListener('contextmenu', function (e) { e.preventDefault(); });
    </script>
</body>
</html>
