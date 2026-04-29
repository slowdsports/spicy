<?php
/**
 * StreamHub - Reproductor Tipo 3: DASH (Bitmovin)
 * ============================================================
 * Streams DASH con soporte ClearKey DRM
 * $fuenteData campos: url (base64), ck_keyid (base64), ck_key (base64), tipo
 */

if (!isset($fuenteData)) {
    die('Acceso denegado');
}

$nombre = htmlspecialchars($fuenteData['nombre']);
// json_encode garantiza escapado seguro para insertar en JS
$jsURL = json_encode($fuenteData['url']);
$jsKEYID = json_encode($fuenteData['ck_keyid'] ?? '');
$jsKEY = json_encode($fuenteData['ck_key'] ?? '');
$jsTIPO = (int) $fuenteData['tipo'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="cache-control" content="no-cache">
    <meta http-equiv="pragma" content="no-cache">
    <meta name="robots" content="noindex">
    <meta name="referrer" content="none">
    <title><?= $nombre ?> - Tele Deportes</title>

    <!-- Bitmovin Player -->
    <script src="//cdn.bitmovin.com/player/web/8/bitmovinplayer.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #000;
            overflow: hidden;
        }

        #wrapper {
            position: relative;
            width: 100%;
            height: 100vh;
        }

        #player {
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

        /* Watermark personalizado */
        .bmpui-ui-watermark {
            background-image: url("https://eduveel1.github.io/baleada/img/iRTVW_PLAYER.png");
            top: 0;
            left: 0;
            min-width: 5em;
        }

        /* Seek bar y volumen en color de marca */
        .bmpui-ui-seekbar .bmpui-seekbar .bmpui-seekbar-playbackposition,
        .bmpui-ui-volumeslider .bmpui-seekbar .bmpui-seekbar-playbackposition {
            background-color: #6366f1;
        }

        .bmpui-ui-seekbar .bmpui-seekbar .bmpui-seekbar-playbackposition-marker,
        .bmpui-ui-volumeslider .bmpui-seekbar .bmpui-seekbar-playbackposition-marker {
            border-color: #6366f1;
            background-color: #6366f1;
        }

        /* Selectores y estados activos */
        .bmpui-ui-selectbox,
        .bmpui-on {
            color: #6366f1;
        }
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
        // ============================================================
        // VARIABLES DESDE PHP
        // ============================================================
        var url = <?= $jsURL ?>;
        var ck_keyid = <?= $jsKEYID ?>;
        var ck_key = <?= $jsKEY ?>;
        var type = <?= $jsTIPO ?>;
        console.log("URL: " + url + "\\nCK_KEYID: " + ck_keyid + "\\nCK_KEY: " + ck_key + "\\nTIPO: " + type);

        // ============================================================
        // BLOQUEAR INSPECTOR DE ELEMENTOS
        // ============================================================
        (function () {
            var devtools = { open: false };
            var threshold = 160;
            setInterval(function () {
                if (window.outerHeight - window.innerHeight > threshold ||
                    window.outerWidth - window.innerWidth > threshold) {
                    devtools.open = true;
                } else {
                    devtools.open = false;
                }
            }, 500);
            document.addEventListener('contextmenu', function (e) { e.preventDefault(); });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'F12' ||
                    (e.ctrlKey && e.shiftKey && ['I', 'C', 'J'].indexOf(e.key) > -1)) {
                    e.preventDefault();
                }
            });
        })();

        // ============================================================
        // INTERCEPTOR XHR — bypass licencia Bitmovin
        // ============================================================
        function override(url) {
            // Null-check: el watermark puede no existir aún en el DOM
            var wm = document.querySelector('button.bmpui-ui-watermark');
            if (wm) wm.setAttribute('disabled', 'disabled');

            if (url.indexOf('licensing.bitmovin.com/licensing') > -1)
                return 'data:text/plain;charset=utf-8;base64,eyJzdGF0dXMiOiJncmFudGVkIiwibWVzc2FnZSI6IlRoZXJlIHlvdSBnby4ifQ==';
            if (url.indexOf('licensing.bitmovin.com/impression') > -1)
                return 'data:text/plain;charset=utf-8;base64,eyJzdGF0dXMiOiJncmFudGVkIiwibWVzc2FnZSI6IlRoZXJlIHlvdSBnby4ifQ==';
            return url;
        }

        var _xhrOpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function () {
            arguments[1] = override(arguments[1]);
            return _xhrOpen.apply(this, arguments);
        };

        // ============================================================
        // INICIALIZAR BITMOVIN
        // ============================================================
        document.addEventListener('DOMContentLoaded', function () {
            var container = document.getElementById('player');

            var config = {
                key: '11d3698c-efdf-42f1-8769-54663995de2b',
                analytics: false,
                cast: { enable: true },
                playback: { autoplay: true, muted: true },
                style: { width: '100%', height: '100%' }
            };
            var source;
            source = {
                dash: url,
                drm: {
                    clearkey: [{
                        keyId: ck_keyid,
                        key: ck_key
                    }]
                }
            };

            var statusEl = document.getElementById('status');
            function showPlayer() { statusEl.style.display = 'none'; }

            var player = new bitmovin.player.Player(container, config);
            player.load(source).then(showPlayer).catch(function (err) {
                console.error('Bitmovin error:', err);
            });
        });
    </script>
</body>

</html>