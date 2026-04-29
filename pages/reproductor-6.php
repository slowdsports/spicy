<?php
/**
 * StreamHub - Reproductor Tipo 6: YouTube
 * ============================================================
 * Configuración para videos y canales de YouTube
 * Tipos de URL soportadas: YouTube video, YouTube Live, YouTube Channel
 * 
 * Características:
 * - Reproductor nativo de YouTube
 * - Integraciones de API de YouTube
 * - Transmisiones en vivo y contenido grabado
 */

if (!isset($fuenteData)) {
    die('Acceso denegado');
}

$url = htmlspecialchars($fuenteData['url']);
$nombre = htmlspecialchars($fuenteData['nombre']);

// Extraer ID de video de diferentes formatos de URL de YouTube
function extractYouTubeId($url) {
    // Formatos: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/embed/ID, youtube.com/live/ID
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/live\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

$youtubeId = extractYouTubeId($url);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> - Tele Deportes</title>
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
        iframe {
            border: none;
            width: 100%;
            height: 100%;
            display: block;
        }
        .error-message {
            color: #ef4444;
            text-align: center;
            padding: 2rem;
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

    <!-- YouTube API CDN (opcional, para control avanzado) -->
    <script src="https://www.youtube.com/iframe_api"></script>

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
        // CONFIGURACIÓN YOUTUBE
        // ============================================================
        const PLAYER_CONFIG = {
            id: <?= (int)$fuenteData['id'] ?>,
            nombre: '<?= $nombre ?>',
            url: '<?= $url ?>',
            youtubeId: '<?= $youtubeId ?>',
            tipo: 6
        };

        // ============================================================
        // INICIALIZAR REPRODUCTOR YOUTUBE
        // ============================================================
        var statusEl = document.getElementById('status');
        function showPlayer() { statusEl.style.display = 'none'; }

        function initializePlayer() {
            const container = document.getElementById('player-container');

            // Validar que se extrajo el ID de YouTube
            if (!PLAYER_CONFIG.youtubeId) {
                container.innerHTML = `
                    <div class="error-message">
                        <p>URL de YouTube no válida</p>
                        <p>${PLAYER_CONFIG.url}</p>
                    </div>
                `;
                return;
            }

            // Crear iframe de YouTube
            const iframe = document.createElement('iframe');
            iframe.src = `https://www.youtube.com/embed/${PLAYER_CONFIG.youtubeId}?autoplay=1&controls=1&modestbranding=1&rel=0`;
            iframe.allowFullscreen = true;
            iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
            iframe.onload = showPlayer;

            container.innerHTML = '';
            container.appendChild(iframe);
        }

        /**
         * FUNCIÓN ALTERNATIVA: YouTube API para control avanzado
         * Descomenta para usar control manual de reproducción
         */
        function initWithYouTubeAPI() {
            const container = document.getElementById('player-container');

            // Crear elemento para el reproductor
            const playerDiv = document.createElement('div');
            playerDiv.id = 'youtube-player';
            playerDiv.style.width = '100%';
            playerDiv.style.height = '100%';
            container.appendChild(playerDiv);

            // Esperar a que la API de YouTube esté lista
            window.onYouTubeIframeAPIReady = function() {
                window.youtubePlayer = new YT.Player('youtube-player', {
                    width: '100%',
                    height: '100%',
                    videoId: PLAYER_CONFIG.youtubeId,
                    events: {
                        'onReady': onPlayerReady,
                        'onStateChange': onPlayerStateChange,
                        'onError': onPlayerError
                    },
                    playerVars: {
                        autoplay: 1,
                        controls: 1,
                        modestbranding: 1,
                        rel: 0,
                        // AGREGAR MÁS OPCIONES AQUÍ:
                        // cc_load_policy: 1,  // Subtítulos por defecto
                        // iv_load_policy: 3,  // Sin anotaciones
                    }
                });
            };
        }

        // Callbacks de YouTube API
        function onPlayerReady(event) {
            console.log('YouTube player listo');
            // event.target.playVideo();
        }

        function onPlayerStateChange(event) {
            const states = {
                '-1': 'Descargado',
                '0': 'Final',
                '1': 'Reproduciendo',
                '2': 'Pausado',
                '3': 'Buffering',
                '5': 'Iniciando'
            };
            console.log('Estado: ' + states[event.data]);
        }

        function onPlayerError(event) {
            console.error('Error de YouTube:', event.data);
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', initializePlayer);
    </script>
</body>
</html>
