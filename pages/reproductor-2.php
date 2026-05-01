<?php
/**
 * StreamHub - Reproductor Tipo 2: HLS
 * ============================================================
    * Configuración para streams HLS (HTTP Live Streaming)
    * Reproductores soportados: Video.js, HLS.js, Shaka Player
 */

if (!isset($fuenteData)) {
    die('Acceso denegado');
}

$url = htmlspecialchars($fuenteData['url']);
$nombre = htmlspecialchars($fuenteData['nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../includes/ads.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> - Tele Deportes</title>
    <link rel="stylesheet" href="../assets/css/clappr.css">
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
    
    <!-- Clappr CDN -->
    <script src="//cdn.jsdelivr.net/npm/clappr@latest/dist/clappr.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/level-selector@latest/dist/level-selector.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/clappr-pip@latest/dist/clappr-pip.min.js"></script>
    <script src="//cdn.jsdelivr.net/gh/clappr/dash-shaka-playback@latest/dist/dash-shaka-playback.min.js"></script>
    <script src='//cdn.jsdelivr.net/npm/clappr-chromecast-plugin@latest/dist/clappr-chromecast-plugin.min.js'></script>
    <script src='//cdn.jsdelivr.net/npm/clappr-pip@latest/dist/clappr-pip.min.js'></script>
    <script src="//ewwink.github.io/clappr-youtube-plugin/clappr-youtube-plugin.js"></script>

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

        // ============================================================
        // CONFIGURACIÓN DE REPRODUCCIÓN M3U8
        // ============================================================
        const PLAYER_CONFIG = {
            id: <?= (int)$fuenteData['id'] ?>,
            nombre: '<?= $nombre ?>',
            url: '<?= $url ?>',
            tipo: 1,
            // OPCIONES PARA AGREGAR REPRODUCTORES:
            // - useJWPlayer: true  -> Usa JW Player
            // - useClappr: true    -> Usa Clappr
            // - useHLS.js: true    -> Usa HLS.js (para navegadores sin soporte nativo)
        };

        /**
         * REPRODUCTOR 2: Clappr
         * Reproductor de código abierto basado en Flash/HTML5
         * Configuración: https://github.com/clappr/clappr/blob/master/docs/README.md
         */
        var statusEl = document.getElementById('status');
        function showPlayer() { statusEl.style.display = 'none'; }

        function initClappr(config) {
            window.player = new Clappr.Player({
                source: config.url,
                parentId: '#player-container',
                width: '100%',
                height: '100%',
                autoplay: true,
                mute: true,
                
                // Plugins disponibles
                plugins: [LevelSelector, ClapprPip.PipButton, ClapprPip.PipPlugin, DashShakaPlayback, ChromecastPlugin, ClapprPip.PipButton, ClapprPip.PipPlugin],
                events: {
                    onReady: function () {
                        showPlayer();
                        var plugin = this.getPlugin("click_to_pause");
                        plugin && plugin.disable();
                    },
                },
                
                // AGREGAR OPCIONES AQUÍ:
                // watermark: 'url',
                // watermarkLink: 'url',
                // hideMediaControlDelay: 3000,
            });
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            initClappr(PLAYER_CONFIG);
        });
    </script>
</body>
</html>