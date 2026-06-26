<?php
/**
 * StreamHub - Reproductor Tipo 3: DASH
 * ============================================================
 * Soporta tres reproductores según $fuenteData['reproductor']:
 *   - bitmovin  (predeterminado) — Bitmovin Player v8 con bypass de licencia
 *   - clappr    — Clappr + DashShakaPlayback plugin
 *   - jwplayer  — JW Player (requiere licencia propia en el script src)
 *
 * Campos de $fuenteData utilizados:
 *   url         — URL del stream DASH (string, puede ser base64 en PHP padre)
 *   ck_keyid    — ClearKey ID  (vacío si no hay DRM)
 *   ck_key      — ClearKey Key (vacío si no hay DRM)
 *   reproductor — 'bitmovin' | 'clappr' | 'jwplayer'
 *
 * Para implementar Clappr o JW Player busca los bloques marcados con TODO.
 */

if (!isset($fuenteData)) {
    die('Acceso denegado');
}

// El check de Referer en reproductor.php no detecta cuando todo canal.php se
// incrusta en un iframe de OTRO sitio (este request sigue llegando con referer
// same-host, porque viene de nuestro propio canal.php). Eso lo cubre el guard
// JS de más abajo; estos headers son la versión sin JS, exigida por el navegador.
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: frame-ancestors 'self'");

$nombre      = htmlspecialchars($fuenteData['nombre']);
// URL y DRM keys NO se pasan al cliente — las sirve api/stream.php bajo token firmado
$jsBase      = json_encode(BASE_URL);
$jsFid       = (int)$streamFuenteId;
$jsTok       = json_encode($streamToken);
$jsTs        = (int)$streamTs;
$jsBlockUrl  = json_encode(BASE_URL . 'block.html');

$allowed     = ['bitmovin', 'clappr', 'jwplayer'];
$reproductor = in_array($fuenteData['reproductor'] ?? '', $allowed)
    ? $fuenteData['reproductor']
    : 'bitmovin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../includes/ads.php'; ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="cache-control" content="no-cache">
    <meta http-equiv="pragma" content="no-cache">
    <meta name="robots" content="noindex">
    <meta name="referrer" content="none">
    <title><?= $nombre ?> - Tele Deportes</title>

    <script>
    // ── Bloqueo de incrustación cross-origin (iframe en otro sitio) ─────────
    // Corre antes de cargar cualquier librería del reproductor: si nos están
    // incrustando desde otro dominio, ni siquiera pedimos el stream.
    //
    // canal.php mete este reproductor en un iframe propio (mismo origen), así
    // que no basta con mirar el padre inmediato: hay que mirar window.top, el
    // frame raíz de toda la cadena. Si alguien incrusta canal.php entero en
    // OTRO sitio, window.top pasa a ser la ventana de ese otro sitio y leerla
    // lanza SecurityError por same-origin policy — eso es justo lo que
    // detectamos. Esto también cubre el truco de meternos en un iframe
    // sandbox: un documento sandbox sin allow-same-origin tiene origen opaco,
    // así que tocar window.top también falla ahí.
    (function () {
        if (window.top === window) return; // no estamos en ningún iframe

        try {
            void window.top.location.href; // same-origin → no hace nada más
        } catch (e) {
            setTimeout(function () {
                location.href = <?= $jsBlockUrl ?>;
            }, 400);
        }
    })();
    </script>

    <?php if ($reproductor === 'bitmovin'): ?>
    <!-- ── BITMOVIN ─────────────────────────────────────────── -->
    <script src="//cdn.bitmovin.com/player/web/8/bitmovinplayer.js"></script>
    <?php elseif ($reproductor === 'clappr'): ?>
    <!-- ── CLAPPR ──────────────────────────────────────────── -->
    <script src="//cdn.jsdelivr.net/npm/clappr@latest/dist/clappr.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/level-selector@latest/dist/level-selector.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/clappr-pip@latest/dist/clappr-pip.min.js"></script>
    <script src="//cdn.jsdelivr.net/gh/clappr/dash-shaka-playback@latest/dist/dash-shaka-playback.min.js"></script>
    <script src='//cdn.jsdelivr.net/npm/clappr-chromecast-plugin@latest/dist/clappr-chromecast-plugin.min.js'></script>
    <script src='//cdn.jsdelivr.net/npm/clappr-pip@latest/dist/clappr-pip.min.js'></script>
    <script src="//ewwink.github.io/clappr-youtube-plugin/clappr-youtube-plugin.js"></script>
    <?php elseif ($reproductor === 'jwplayer'): ?>
    <!-- ── JW PLAYER ─────────────────────────────────────────
         TODO: Reemplaza YOUR_LIBRARY_KEY con tu clave de biblioteca JW Player.
         Encuéntrala en dashboard.jwplayer.com → Players → tu player → Setup.
    -->
    <script src="//ssl.p.jwpcdn.com/player/v/8.24.0/jwplayer.js"></script>
    <script>jwplayer.key = 'XSuP4qMl+9tK17QNb+4+th2Pm9AWgMO/cYH8CI0HGGr7bdjo';</script>
    <link rel="stylesheet" href="../assets/css/jw.css">
    <?php endif; ?>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { background: #000; overflow: hidden; }

        #wrapper { position: relative; width: 100%; height: 100vh; }

        #player { width: 100%; height: 100%; background: #000; }

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
            width: 46px; height: 46px;
            border: 4px solid rgba(255,255,255,0.12);
            border-top-color: #8b5cf6;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .s-title { font-size: 1.15em; font-weight: 600; color: #fff; }

        /* Aviso de estabilidad en iOS 26 */
        .ios-notice {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            box-sizing: border-box;
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            background: rgba(20, 20, 24, 0.92);
            border-bottom: 1px solid rgba(139, 92, 246, 0.4);
            color: #fff;
            padding: 10px 40px;
            font-size: 0.82em;
            font-weight: 500;
            text-align: center;
            box-shadow: 0 6px 24px rgba(0,0,0,.35);
            transition: opacity .4s ease, transform .4s ease;
        }
        .ios-notice.ios-notice-hidden {
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
        }
        .ios-notice-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #22c55e; flex-shrink: 0;
        }
        .ios-notice-close {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,.55);
            font-size: 1.2em; line-height: 1; cursor: pointer; padding: 4px;
        }
        .ios-notice-close:hover { color: #fff; }

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
    </style>
</head>
<body>
<div id="wrapper">
    <div id="status">
        <div class="spinner"></div>
        <div class="s-title">Cargando canal...</div>
    </div>
    <div id="player"></div>
    <?php if ($isIOS): ?>
    <div id="ios-notice" class="ios-notice">
        <span class="ios-notice-dot"></span>
        <span>Los canales de este sitio son estables en iOS 26.</span>
        <button id="ios-notice-close" type="button" class="ios-notice-close" aria-label="Cerrar">&times;</button>
    </div>
    <?php endif; ?>
</div>

<script>
// ── Token de sesión (URL y keys nunca aparecen en el source) ────────────────
var _BASE = <?= $jsBase ?>;
var _FID  = <?= $jsFid ?>;
var _TOK  = <?= $jsTok ?>;
var _TS   = <?= $jsTs ?>;

// ── Protección básica ────────────────────────────────────────────────────────
(function () {
    document.addEventListener('contextmenu', function (e) { e.preventDefault(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F12' ||
            (e.ctrlKey && e.shiftKey && ['I', 'C', 'J'].indexOf(e.key) > -1)) {
            e.preventDefault();
        }
    });
})();

var statusEl = document.getElementById('status');
function showPlayer() { statusEl.style.display = 'none'; }
function showError(msg) {
    statusEl.innerHTML =
        '<div style="font-size:2rem;margin-bottom:8px;">⚠️</div>' +
        '<div class="s-title">' + msg + '</div>';
    statusEl.style.display = 'flex';
}

<?php if ($isIOS): ?>
// ── Aviso de estabilidad en iOS 26: se oculta solo a los 30s o al cerrarlo ──
(function () {
    var notice = document.getElementById('ios-notice');
    var closeBtn = document.getElementById('ios-notice-close');
    if (!notice) return;
    var hideTimer = setTimeout(function () { notice.classList.add('ios-notice-hidden'); }, 30000);
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            clearTimeout(hideTimer);
            notice.classList.add('ios-notice-hidden');
        });
    }
})();
<?php endif; ?>

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

document.addEventListener('DOMContentLoaded', function () {

    // Solicitar datos al servidor — token HMAC vinculado a la sesión del usuario
    fetch(_BASE + 'api/stream.php?id=' + _FID + '&ts=' + _TS + '&t=' + encodeURIComponent(_TOK), {
        credentials: 'same-origin'
    })
    .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function (sd) {
        if (!sd.url) { showError('Stream no disponible'); return; }

        var url      = sd.url;
        var ck_keyid = sd.keyId || '';
        var ck_key   = sd.key   || '';

<?php if ($reproductor === 'bitmovin'): ?>
        // ── BITMOVIN ─────────────────────────────────────────────────────────
        // ui:false — la UI por defecto de Bitmovin detecta Smart TVs (WebOS,
        // Tizen, Vizio, Hisense, Xbox, PlayStation, Vidaa, Xumo, vía user-agent)
        // y les aplica su layout "tv" propio, que A PROPÓSITO no trae control
        // de volumen ni botón de pantalla completa (asume mando físico para
        // volumen y que la TV ya está siempre en pantalla completa). Para
        // nuestros canales sí los queremos siempre, así que construimos la
        // misma UI "main" que usan desktop/mobile a mano, sin dejar que
        // Bitmovin la cambie por la de TV. Ver attachFullBitmovinUi más abajo.
        var player = new bitmovin.player.Player(document.getElementById('player'), {
            key:      '11d3698c-efdf-42f1-8769-54663995de2b',
            analytics: false,
            cast:     { enable: true },
            playback: { autoplay: true, muted: true },
            style:    { width: '100%', height: '100%' },
            ui:       false
        });

        function attachFullBitmovinUi(p) {
            if (!window.bitmovin || !bitmovin.playerui) return;
            var UIFactory = bitmovin.playerui.UIFactory;
            var UIManager = bitmovin.playerui.UIManager;
            try {
                if (UIFactory.defaultLayouts && typeof UIFactory.defaultLayouts.main === 'function') {
                    new UIManager(p, UIFactory.defaultLayouts.main());
                } else if (typeof UIFactory.buildDefaultUI === 'function') {
                    UIFactory.buildDefaultUI(p);
                } else if (typeof UIFactory.modernUI === 'function') {
                    new UIManager(p, UIFactory.modernUI());
                }
            } catch (e) {
                console.error('No se pudo construir la UI de Bitmovin:', e);
            }
        }

        var source = { dash: url };
        if (ck_keyid && ck_key) {
            source.drm = { clearkey: [{ keyId: ck_keyid, key: ck_key }] };
        }
        player.load(source).then(function () {
            attachFullBitmovinUi(player);
            showPlayer();
        }).catch(function (err) {
            showError('Error al cargar el stream.');
            console.error('Bitmovin error:', err);
        });

<?php elseif ($reproductor === 'clappr'): ?>
        // ── CLAPPR ───────────────────────────────────────────────────────────
        var cfg = {
            source:   url,
            parentId: '#player',
            width:    '100%',
            height:   '100%',
            autoPlay: true,
            mute:     true,
            playback: { playInline: true }
        };
        if (ck_keyid && ck_key) {
            var ck = {};
            ck[ck_keyid] = ck_key;
            cfg.shakaConfiguration = { drm: { clearKeys: ck } };
        }
        var clapprPlayer = new Clappr.Player(cfg);
        showPlayer();
        clapprPlayer.on(Clappr.Events.PLAYER_ERROR, function (err) {
            showError('Error al cargar el stream.');
            console.error('Clappr error:', err);
        });

<?php elseif ($reproductor === 'jwplayer'): ?>
        // ── JW PLAYER ────────────────────────────────────────────────────────
        var setup = {
            file:      url,
            type:      'dash',
            width:     '100%',
            height:    '100%',
            autostart: true,
            mute:      true,
        };
        if (ck_keyid && ck_key) {
            setup.drm = { clearkey: { keyId: ck_keyid, key: ck_key } };
        }
        jwplayer('player').setup(setup);
        jwplayer('player').on('ready', showPlayer);
        jwplayer('player').on('error', function (err) {
            showError('Error al cargar el stream.');
            console.error('JWPlayer error:', err);
        });

<?php endif; ?>
    })
    .catch(function (err) {
        showError('No se pudo conectar con el servidor.');
        console.error(err);
    });

});
</script>
</body>
</html>
