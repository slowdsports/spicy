<?php
/**
 * StreamHub - Reproductor Tipo 5: iFrame Directo
 * ============================================================
 * Configuración para embeds iframe de terceros
 * Casos de uso: Twitch, YouTube directo, plataformas de streaming propias
 * 
 * Características:
 * - Embed directo de iframe
 * - Mínimo procesamiento
 * - Compatible con cualquier plataforma que ofrezca iframe embed
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
        // CONFIGURACIÓN DE IFRAME DIRECTO
        // ============================================================
        const PLAYER_CONFIG = {
            id: <?= (int)$fuenteData['id'] ?>,
            nombre: '<?= $nombre ?>',
            url: '<?= $url ?>',
            tipo: 5
        };

        // ============================================================
        // INICIALIZAR REPRODUCTOR IFRAME
        // ============================================================
        var statusEl = document.getElementById('status');
        function showPlayer() { statusEl.style.display = 'none'; }

        function initializePlayer() {
            const container = document.getElementById('player-container');

            const iframe = document.createElement('iframe');
            iframe.src = PLAYER_CONFIG.url;
            iframe.allowFullscreen = true;
            iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
            iframe.sandbox.add('allow-same-origin');
            iframe.sandbox.add('allow-scripts');
            iframe.sandbox.add('allow-presentation');
            iframe.onload = showPlayer;

            container.innerHTML = '';
            container.appendChild(iframe);
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', initializePlayer);
    </script>
</body>
</html>
