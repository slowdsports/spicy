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

$url = htmlspecialchars($fuenteData['url']);
$nombre = htmlspecialchars($fuenteData['nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
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

        var statusEl = document.getElementById('status');
        function showPlayer() { statusEl.style.display = 'none'; }

        // ============================================================
        // INICIALIZAR REPRODUCTOR (JW Player por defecto)
        // ============================================================
        function initializePlayer() {
            initJWPlayer(PLAYER_CONFIG);
        }

        /**
         * REPRODUCTOR 1: JW Player
         * Recomendado para la mayoría de casos
         * Soporta: M3U8, DASH, VP9, MP4
         * Configuración completa en: https://docs.jwplayer.com/players/reference/setup-options
         */
        function initJWPlayer(config) {
            const playerContainer = document.getElementById('player-container');
            
            jwplayer(playerContainer).setup({
                // Archivo multimedia
                file: config.url,
                type: 'hls',
                
                // Comportamiento
                autostart: true,
                controls: true,
                width: '100%',
                height: '100%',
                
                // Reproducción
                playback: {
                    autostart: true,
                    muted: true,
                    dvrSeekLimit: 0  // Permitir DVR completo
                },
                
                // Interfaz
                ui: {
                    controlbar: {
                        settings: true
                    }
                },
                
                // Calidad adaptativa (HLS nativo)
                hlsVariantSelection: 'auto',  // O 'manual' para selector
                
                // AGREGAR OPCIONES AQUÍ:
                // captions: [{...}],  -> Para subtítulos
                // poster: 'url',      -> Para poster/thumbnail
                // logo: {file: '', link: ''}, -> Para logo watermark
                // analytics: {...}   -> Para tracking
            }).on('ready', showPlayer);
        }

        /**
         * REPRODUCTOR 2: Clappr (Alternativa)
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
                mute: true,
                
                // Plugins disponibles
                plugins: [
                    // Clappr.MediaControlPlugin,
                    // Clappr.PosterPlugin,
                ],
                
                // AGREGAR OPCIONES AQUÍ:
                // watermark: 'url',
                // watermarkLink: 'url',
                // hideMediaControlDelay: 3000,
            });
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', initializePlayer);
    </script>
</body>
</html>
