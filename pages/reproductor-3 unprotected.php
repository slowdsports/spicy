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

$nombre      = htmlspecialchars($fuenteData['nombre']);
$jsURL       = json_encode($fuenteData['url']);
$jsKEYID     = json_encode($fuenteData['ck_keyid'] ?? '');
$jsKEY       = json_encode($fuenteData['ck_key']   ?? '');
$jsTIPO      = (int) $fuenteData['tipo'];

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
</div>

<script>
// ── Variables desde PHP ─────────────────────────────────────────
var url        = <?= $jsURL ?>;
var ck_keyid   = <?= $jsKEYID ?>;
var ck_key     = <?= $jsKEY ?>;

// ── Protección básica ───────────────────────────────────────────
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

<?php if ($reproductor === 'bitmovin'): ?>
// ════════════════════════════════════════════════════════════════
// BITMOVIN
// ════════════════════════════════════════════════════════════════

// Interceptar peticiones de licencia para omitir validación
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
    XMLHttpRequest.prototype.open = function () {
        arguments[1] = override(arguments[1]);
        return _open.apply(this, arguments);
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    var player = new bitmovin.player.Player(document.getElementById('player'), {
        key:      '11d3698c-efdf-42f1-8769-54663995de2b',
        analytics: false,
        cast:     { enable: true },
        playback: { autoplay: true, muted: true },
        style:    { width: '100%', height: '100%' }
    });

    var source = { dash: url };
    // Añadir ClearKey DRM solo si vienen los campos
    if (ck_keyid && ck_key) {
        source.drm = { clearkey: [{ keyId: ck_keyid, key: ck_key }] };
    }

    player.load(source).then(showPlayer).catch(function (err) {
        console.error('Bitmovin error:', err);
    });
});

<?php elseif ($reproductor === 'clappr'): ?>
// ════════════════════════════════════════════════════════════════
// CLAPPR
// Docs: https://github.com/clappr/clappr
// Plugin DASH: https://github.com/clappr/clappr-dash-shaka-playback
// ════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function () {
    var config = {
        source:   url,
        parentId: '#player',
        width:    '100%',
        height:   '100%',
        autoPlay: true,
        mute:     true,
        playback: { playInline: true }
    };

    // ClearKey DRM: ck_keyid y ck_key en hex vienen directo de la BD.
    // Shaka Player espera { drm: { clearKeys: { '<keyId_hex>': '<key_hex>' } } }
    if (ck_keyid && ck_key) {
        var clearKeys = {};
        clearKeys[ck_keyid] = ck_key;
        config.shakaConfiguration = { drm: { clearKeys: clearKeys } };
    }

    var clapprPlayer = new Clappr.Player(config);
    showPlayer();

    clapprPlayer.on(Clappr.Events.PLAYER_ERROR, function (err) {
        console.error('Clappr error:', err);
    });
});

<?php elseif ($reproductor === 'jwplayer'): ?>
// ════════════════════════════════════════════════════════════════
// JW PLAYER
// Docs: https://developer.jwplayer.com/jwplayer/
// TODO: Asegúrate de tener una licencia válida y de haber actualizado
//       el script src del <head> con tu clave de biblioteca.
// ════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function () {
    var setup = {
        file:      url,
        type:      'dash',
        width:     '100%',
        height:    '100%',
        autostart: true,
        mute:      true,
        // TODO: ajusta hlshtml5 / qualityLabels según necesites
    };

    // ClearKey DRM para JW Player (requiere licencia Enterprise o superior)
    if (ck_keyid && ck_key) {
        setup.drm = {
            clearkey: {
                keyId: ck_keyid,
                key:   ck_key
            }
        };
    }

    jwplayer('player').setup(setup);
    jwplayer('player').on('ready', showPlayer);
    jwplayer('player').on('error', function (err) {
        console.error('JWPlayer error:', err);
    });
});

<?php endif; ?>
</script>
</body>
</html>
