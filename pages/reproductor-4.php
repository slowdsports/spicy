<?php
/**
 * StreamHub - Reproductor Tipo 4: DASH-DRM
 * ============================================================
 * Configuración para streams DASH con protección Widevine DRM
 * Reproductores soportados: Bitmovin Player (recomendado), JW Player
 * 
 * Características:
 * - DASH con Widevine Digital Rights Management
 * - Protección contra descarga y piratería
 * - Requiere claves CK (ck_key, ck_keyid)
 * - Compatible con navegadores modernos
 */

if (!isset($fuenteData)) {
    die('Acceso denegado');
}

$url = htmlspecialchars($fuenteData['url']);
$nombre = htmlspecialchars($fuenteData['nombre']);
$ckKey = htmlspecialchars($fuenteData['ck_key'] ?? '');
$ckKeyId = htmlspecialchars($fuenteData['ck_keyid'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> - StreamHub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            overflow: hidden;
        }
        #player-container {
            width: 100%;
            height: 100vh;
            background: #000;
        }
    </style>
</head>
<body>
    <div id="player-container"></div>

    <!-- Bitmovin Player CDN (mejor para DRM) -->
    <script src="https://cdn.bitmovin.com/player/web/8/bitmovinplayer-8.79.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.bitmovin.com/player/web/8/bitmovinplayer-8.79.0.min.css">
    
    <!-- JW Player CDN (alternativa) -->
    <script src="//ssl.p.jwpcdn.com/player/v/8.24.0/jwplayer.js"></script>

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
        // CONFIGURACIÓN DE REPRODUCCIÓN DASH-DRM
        // ============================================================
        const PLAYER_CONFIG = {
            id: <?= (int)$fuenteData['id'] ?>,
            nombre: '<?= $nombre ?>',
            url: '<?= $url ?>',
            tipo: 4,
            ckKey: '<?= $ckKey ?>',
            ckKeyId: '<?= $ckKeyId ?>'
        };

        // ============================================================
        // INICIALIZAR REPRODUCTOR Bitmovin (recomendado para DRM)
        // ============================================================
        function initializePlayer() {
            initBitmovinPlayer(PLAYER_CONFIG);
        }

        /**
         * REPRODUCTOR 1: Bitmovin Player (RECOMENDADO PARA DRM)
         * Excelente soporte para Widevine, PlayReady, FairPlay
         * Configuración: https://developer.bitmovin.com/playback/web/setup
         */
        function initBitmovinPlayer(config) {
            // Configurar versión del reproductor
            bitmovin.player.VERSION_PATH = 'https://cdn.bitmovin.com/player/web/8';

            // Configuración de reproducción
            const playerConfig = {
                key: 'YOUR_BITMOVIN_PLAYER_KEY',  // REEMPLAZAR CON CLAVE REAL
                playback: {
                    autoplay: true,
                    muted: false,
                },
                
                // Configuración de adaptabilidad
                adaptation: {
                    type: 'bola',  // o 'throughput', 'manual'
                    desktop: {
                        minBitrate: 500000,    // 500 kbps mínimo
                        maxBitrate: 8000000,   // 8 mbps máximo
                        startBitrate: 2000000  // 2 mbps inicial
                    }
                }
            };

            // Crear reproductor
            const player = bitmovin.player.Player.new({
                target: document.getElementById('player-container'),
                key: playerConfig.key,
                playback: playerConfig.playback,
                adaptation: playerConfig.adaptation
            });

            // Cargar stream con configuración DRM
            player.load({
                dash: {
                    url: config.url
                },
                
                // CONFIGURACIÓN WIDEVINE DRM
                drm: {
                    widevine: {
                        licenseUrl: config.ckKey,
                        // AGREGAR OPCIONES AQUÍ:
                        // customData: {...},
                        // licenseRequestHeaders: {...},
                        // jsonp: false,
                    }
                }
            }).then(() => {
                console.log('Stream cargado con protección DRM');
            }).catch((error) => {
                console.error('Error al cargar stream:', error);
            });

            // AGREGAR LISTENERS AQUÍ:
            // player.on(bitmovin.player.Player.EventType.Error, handleError);
            // player.on(bitmovin.player.Player.EventType.Ready, handleReady);
        }

        /**
         * REPRODUCTOR 2: JW Player (con soporte DRM)
         * Alternativa si se tiene licencia de JW Player Premium
         */
        function initJWPlayer(config) {
            const playerContainer = document.getElementById('player-container');
            
            jwplayer(playerContainer).setup({
                file: config.url,
                type: 'dash',
                
                autostart: true,
                controls: true,
                width: '100%',
                height: '100%',
                
                // Configuración DRM para JW Player
                drm: {
                    widevine: {
                        licenseUrl: config.ckKey,
                        // customHeaders: {...},
                    }
                },
                
                // AGREGAR OPCIONES AQUÍ:
                // drmHeaders: {...},
                // licenseRequestHeaders: {...},
            });
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', initializePlayer);
    </script>
</body>
</html>
