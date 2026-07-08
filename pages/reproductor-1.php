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

$nombre = htmlspecialchars($fuenteData['nombre']);

// URL NO se pasa al cliente — la sirve api/stream.php bajo token firmado
$jsBase = json_encode(BASE_URL);
$jsFid  = (int)$streamFuenteId;
$jsTok  = json_encode($streamToken);
$jsTs   = (int)$streamTs;

// JW Player sigue siendo el default (no romper fuentes existentes que nunca
// configuraron este campo); Bitmovin es opt-in por fuente, igual que en
// reproductor-3.php (DASH).
$allowed     = ['bitmovin', 'jwplayer'];
$reproductor = in_array($fuenteData['reproductor'] ?? '', $allowed)
    ? $fuenteData['reproductor']
    : 'jwplayer';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../includes/ads.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> - Tele Deportes</title>
    <?php if ($reproductor === 'bitmovin'): ?>
    <script src="//cdn.bitmovin.com/player/web/8/bitmovinplayer.js"></script>
    <?php else: ?>
    <link rel="stylesheet" href="../assets/css/jw.css">
    <?php endif; ?>
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
        <?php if ($reproductor === 'bitmovin'): ?>
        /* Estilos de marca para Bitmovin */
        .bmpui-ui-watermark {
            background-image: url("https://eduveel1.github.io/baleada/img/iRTVW_PLAYER.png");
            top: 0; left: 0; min-width: 5em;
        }
        .bmpui-ui-seekbar .bmpui-seekbar .bmpui-seekbar-playbackposition,
        .bmpui-ui-volumeslider .bmpui-seekbar .bmpui-seekbar-playbackposition { background-color: #6366f1; }
        .bmpui-ui-seekbar .bmpui-seekbar .bmpui-seekbar-playbackposition-marker,
        .bmpui-ui-volumeslider .bmpui-seekbar .bmpui-seekbar-playbackposition-marker {
            border-color: #6366f1; background-color: #6366f1;
        }
        .bmpui-ui-selectbox, .bmpui-on { color: #6366f1; }
        <?php endif; ?>
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

    <?php if ($reproductor !== 'bitmovin'): ?>
    <!-- JW Player CDN -->
    <script src="//ssl.p.jwpcdn.com/player/v/8.24.0/jwplayer.js"></script>
    <script>jwplayer.key = 'XSuP4qMl+9tK17QNb+4+th2Pm9AWgMO/cYH8CI0HGGr7bdjo';</script>
    <?php endif; ?>

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
            tipo: 1,
            // OPCIONES PARA AGREGAR REPRODUCTORES:
            // - useJWPlayer: true  -> Usa JW Player
            // - useClappr: true    -> Usa Clappr
            // - useHLS.js: true    -> Usa HLS.js (para navegadores sin soporte nativo)
        };

        // ── Token de sesión (la URL nunca aparece en el source) ─────────────
        var _BASE = <?= $jsBase ?>;
        var _FID  = <?= $jsFid ?>;
        var _TOK  = <?= $jsTok ?>;
        var _TS   = <?= $jsTs ?>;

        var statusEl = document.getElementById('status');
        function showPlayer() { statusEl.style.display = 'none'; }
        function showError(msg) {
            statusEl.innerHTML =
                '<div style="font-size:2rem;margin-bottom:8px;">⚠️</div>' +
                '<div class="s-title">' + msg + '</div>';
            statusEl.style.display = 'flex';
        }

        <?php if ($reproductor === 'bitmovin'): ?>
        // Interceptar licencias Bitmovin ANTES de que el player emita la primera petición
        (function () {
            var GRANT = 'data:text/plain;charset=utf-8;base64,eyJzdGF0dXMiOiJncmFudGVkIiwibWVzc2FnZSI6IlRoZXJlIHlvdSBnby4ifQ==';
            function override(u) {
                var wm = document.querySelector('button.bmpui-ui-watermark');
                if (wm) wm.setAttribute('disabled', 'disabled');
                if (u.indexOf('licensing.bitmovin.com/licensing')  > -1) return GRANT;
                if (u.indexOf('licensing.bitmovin.com/impression') > -1) return GRANT;
                return u;
            }
            var _open = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function () { arguments[1] = override(arguments[1]); return _open.apply(this, arguments); };
        })();
        <?php endif; ?>

        // ============================================================
        // INICIALIZAR REPRODUCTOR
        // ============================================================
        function initializePlayer() {
            // Solicitar la URL al servidor — token HMAC vinculado a la sesión
            fetch(_BASE + 'api/stream.php?id=' + _FID + '&ts=' + _TS + '&t=' + encodeURIComponent(_TOK), {
                credentials: 'same-origin'
            })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (sd) {
                if (!sd.url) { showError('Stream no disponible'); return; }
                PLAYER_CONFIG.url    = sd.url;
                PLAYER_CONFIG.keyId  = sd.keyId || '';
                PLAYER_CONFIG.key    = sd.key   || '';
<?php if ($reproductor === 'bitmovin'): ?>
                initBitmovin(PLAYER_CONFIG);
<?php else: ?>
                initJWPlayer(PLAYER_CONFIG);
<?php endif; ?>
            })
            .catch(function (err) {
                showError('No se pudo conectar con el servidor.');
                console.error(err);
            });
        }

        <?php if ($reproductor === 'bitmovin'): ?>
        /**
         * REPRODUCTOR: Bitmovin
         * Mismo bypass de licencia que reproductor-3.php (DASH), pero con
         * source.hls en vez de source.dash — este tipo de fuente es HLS simple.
         */
        function initBitmovin(config) {
            var player = new bitmovin.player.Player(document.getElementById('player-container'), {
                key:       '11d3698c-efdf-42f1-8769-54663995de2b',
                analytics: false,
                cast:      { enable: true },
                playback:  { autoplay: true, muted: true },
                style:     { width: '100%', height: '100%' }
            });

            var source = { hls: config.url };
            if (config.keyId && config.key) {
                source.drm = { clearkey: [{ keyId: config.keyId, key: config.key }] };
            }
            player.load(source).then(showPlayer).catch(function (err) {
                showError('Error al cargar el stream.');
                console.error('Bitmovin error:', err);
            });
        }
        <?php endif; ?>

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
