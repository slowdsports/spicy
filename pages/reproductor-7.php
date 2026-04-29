<?php
/**
 * StreamHub - Reproductor 7: WBC / HLS dinámico
 * Obtiene el stream vía wbc.php (campo final_url).
 * Windows  → verifica extensión Chrome VideoPlayer; si falta, muestra error.
 * Android/iOS → Clappr inline.
 */

if (!isset($fuenteData)) {
    die('Acceso denegado');
}

$nombre    = htmlspecialchars($fuenteData['nombre']);
$jsChannel = json_encode($fuenteData['url'] ?? '');
$jsNombre  = json_encode($nombre);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> - Tele Deportes</title>
    <link rel="stylesheet" href="../assets/css/clappr.css">
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

        .s-icon { font-size: 3rem; line-height: 1; }

        .s-title {
            font-size: 1.15em;
            font-weight: 600;
            color: #fff;
        }

        .s-subtitle {
            font-size: 0.88em;
            color: rgba(255, 255, 255, 0.6);
            max-width: 380px;
            line-height: 1.65;
        }

        .s-subtitle strong { color: #fff; }

        .btn-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 4px;
        }

        .btn {
            padding: 11px 26px;
            border-radius: 8px;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-block;
            transition: opacity 0.15s ease, transform 0.15s ease;
        }

        .btn:hover { opacity: 0.82; transform: scale(1.03); }

        .btn-red   { background: #8b5cf6; color: #fff; }
        .btn-ghost { background: rgba(255, 255, 255, 0.1); color: #fff; }
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

    <!-- Clappr (Android / iOS) -->
    <script src="//cdn.jsdelivr.net/npm/clappr@latest/dist/clappr.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/level-selector@latest/dist/level-selector.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/clappr-pip@latest/dist/clappr-pip.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/clappr-chromecast-plugin@latest/dist/clappr-chromecast-plugin.min.js"></script>

    <script>
        var CHANNEL = <?= $jsChannel ?>;
        var NOMBRE  = <?= $jsNombre ?>;
        var EXT_ID  = 'opmeopcambhfimffbomjgemehjkbbmji';
        var EXT_STORE = 'https://chromewebstore.google.com/detail/videoplayer-mpdm3u8iptvep/' + EXT_ID;

        // ── Detección de dispositivo ──────────────────────────────────────────────
        var ua        = navigator.userAgent.toLowerCase();
        var isWindows = /windows nt/.test(ua) && !/android/.test(ua);
        var isAndroid = /android/.test(ua);
        var isIOS     = /iphone|ipad|ipod/.test(ua);
        var isMobile  = isAndroid || isIOS;

        var statusEl = document.getElementById('status');
        var playerEl = document.getElementById('player');

        // ── Helpers de UI ─────────────────────────────────────────────────────────
        function setStatus(contentFn) {
            statusEl.innerHTML = '';
            contentFn(statusEl);
            statusEl.style.display = 'flex';
            playerEl.style.display = 'none';
        }

        function loading(msg) {
            setStatus(function(el) {
                var sp = document.createElement('div');
                sp.className = 'spinner';
                var ti = document.createElement('div');
                ti.className = 's-title';
                ti.textContent = msg;
                el.appendChild(sp);
                el.appendChild(ti);
            });
        }

        function showPlayer() {
            statusEl.style.display = 'none';
            playerEl.style.display = 'block';
        }

        // ── Detección de extensión ────────────────────────────────────────────────
        // Hace fetch a pages/player.html (la página principal del player).
        // Si la extensión está instalada y el recurso es accesible, la respuesta
        // tendrá status > 0; si no existe la extensión, el fetch lanza ERR_FAILED.
        function checkExtension() {
            return new Promise(function(resolve) {
                var done = false;
                function finish(val) { if (!done) { done = true; resolve(val); } }
                fetch('chrome-extension://' + EXT_ID + '/pages/player.html', { method: 'HEAD' })
                    .then(function(r) { finish(true); })
                    .catch(function() { finish(false); });
                setTimeout(function() { finish(false); }, 4000);
            });
        }

        // ── Clappr (móvil) ────────────────────────────────────────────────────────
        function initClappr(url) {
            showPlayer();
            window.player = new Clappr.Player({
                source: url,
                parentId: '#player',
                width: '100%',
                height: '100%',
                autoplay: true,
                mute: false,
                plugins: [LevelSelector, ClapprPip.PipButton, ClapprPip.PipPlugin, ChromecastPlugin],
                events: {
                    onReady: function() {
                        var p = this.getPlugin('click_to_pause');
                        if (p) p.disable();
                    }
                }
            });
        }

        // ── Reproductor embebido de la extensión (Windows) ───────────────────────
        function initExtensionPlayer(url) {
            showPlayer();
            var iframe = document.createElement('iframe');
            iframe.src = 'chrome-extension://' + EXT_ID + '/pages/player.html#' + url;
            iframe.style.cssText = 'width:100%;height:100%;border:none;background:#000;';
            iframe.allowFullscreen = true;
            playerEl.appendChild(iframe);
        }

        // ── Pantalla Windows: extensión no detectada ──────────────────────────────
        function showExtensionError(url) {
            setStatus(function(el) {
                var icon = document.createElement('div');
                icon.className = 's-icon';
                icon.textContent = '🔌';

                var title = document.createElement('div');
                title.className = 's-title';
                title.textContent = 'Extensión no instalada';

                var sub = document.createElement('div');
                sub.className = 's-subtitle';
                sub.innerHTML = 'Para reproducir este canal en Windows necesitas la extensión ' +
                    '<strong>VideoPlayer (MPD/M3U8/IPTV)</strong> en Google Chrome.';

                var row = document.createElement('div');
                row.className = 'btn-row';

                var installBtn = document.createElement('a');
                installBtn.className = 'btn btn-red';
                installBtn.textContent = 'Instalar extensión';
                installBtn.href = EXT_STORE;
                installBtn.target = '_blank';
                installBtn.rel = 'noopener';

                var tryBtn = document.createElement('button');
                tryBtn.className = 'btn btn-ghost';
                tryBtn.textContent = 'Ya la tengo, reproducir';
                tryBtn.addEventListener('click', function() { initExtensionPlayer(url); });

                row.appendChild(installBtn);
                row.appendChild(tryBtn);
                el.appendChild(icon);
                el.appendChild(title);
                el.appendChild(sub);
                el.appendChild(row);
            });
        }

        // ── Flujo principal ───────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            loading('Cargando canal...');

            fetch('wbc.php?ch=' + encodeURIComponent(CHANNEL))
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function(data) {
                    if (!data.success || !data.final_url) {
                        setStatus(function(el) {
                            var icon = document.createElement('div');
                            icon.className = 's-icon';
                            icon.textContent = '⚠️';
                            var title = document.createElement('div');
                            title.className = 's-title';
                            title.textContent = 'Canal no disponible';
                            var sub = document.createElement('div');
                            sub.className = 's-subtitle';
                            sub.textContent = data.error || 'El canal no está disponible en este momento.';
                            el.appendChild(icon);
                            el.appendChild(title);
                            el.appendChild(sub);
                        });
                        return;
                    }

                    var streamUrl = data.final_url;

                    if (isMobile) {
                        // Android / iOS → Clappr inline
                        initClappr(streamUrl);
                    } else if (isWindows) {
                        // Windows → verificar extensión
                        loading('Verificando extensión...');
                        checkExtension().then(function(installed) {
                            if (installed) {
                                initExtensionPlayer(streamUrl);
                            } else {
                                showExtensionError(streamUrl);
                            }
                        });
                    } else {
                        // macOS / Linux / otros → Clappr como fallback
                        initClappr(streamUrl);
                    }
                })
                .catch(function(err) {
                    setStatus(function(el) {
                        var icon = document.createElement('div');
                        icon.className = 's-icon';
                        icon.textContent = '⚠️';
                        var title = document.createElement('div');
                        title.className = 's-title';
                        title.textContent = 'Error de conexión';
                        var sub = document.createElement('div');
                        sub.className = 's-subtitle';
                        sub.textContent = 'No se pudo obtener el canal. Intenta de nuevo más tarde.';
                        el.appendChild(icon);
                        el.appendChild(title);
                        el.appendChild(sub);
                    });
                    console.error(err);
                });
        });

        // ── DevTools detection (pasivo) ───────────────────────────────────────────
        (function() {
            var devtools = { open: false };
            setInterval(function() {
                devtools.open = (window.outerHeight - window.innerHeight > 160 ||
                    window.outerWidth - window.innerWidth > 160);
            }, 500);
            document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F12' ||
                    (e.ctrlKey && e.shiftKey && ['I', 'C', 'J'].indexOf(e.key) > -1)) {
                    e.preventDefault();
                }
            });
        })();
    </script>
<?php include __DIR__ . '/../includes/ads.php'; ?>
</body>
</html>
