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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> - StreamHub</title>
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
        .container {
        width: 100% !important;
        height: 100vh !important;
        }
        #player-container {
            height: 100% !important;
            width: 100% !important;
            border: none;
            background: #000;
        }
    </style>
</head>
<body>
    <div class="container">
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
        function initClappr(config) {
            window.player = new Clappr.Player({
                source: config.url,
                parentId: '#player-container',
                width: '100%',
                height: '100%',
                autoplay: true,
                mute: false,
                
                // Plugins disponibles
                plugins: [LevelSelector, ClapprPip.PipButton, ClapprPip.PipPlugin, DashShakaPlayback, ChromecastPlugin, ClapprPip.PipButton, ClapprPip.PipPlugin],
                events: {
                    onReady: function () {
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