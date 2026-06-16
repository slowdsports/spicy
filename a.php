<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="cache-control" content="no-cache">
    <meta name="robots" content="noindex">
    <meta name="referrer" content="none">
    <title>KVEA Test</title>
    <script>
    // Cargar solo el reproductor necesario para no bajar dos librerías grandes
    var _iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    if (_iOS) {
        document.write('<script src="https:\/\/cdn.jsdelivr.net\/npm\/shaka-player@4\/dist\/shaka-player.compiled.js"><\/script>');
    } else {
        document.write('<script src="\/\/cdn.bitmovin.com\/player\/web\/8\/bitmovinplayer.js"><\/script>');
    }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #000; overflow: hidden; }
        #wrapper { position: relative; width: 100%; height: 100vh; }
        #player { width: 100%; height: 100%; background: #000; }
        #shaka-video {
            display: none;
            width: 100%; height: 100%;
            background: #000;
            object-fit: contain;
        }
        #status {
            position: absolute;
            inset: 0; z-index: 100;
            background: #000;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 18px; padding: 32px; text-align: center;
        }
        .spinner {
            width: 46px; height: 46px;
            border: 4px solid rgba(255,255,255,0.12);
            border-top-color: #8b5cf6;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .s-title { font-size: 1.15em; font-weight: 600; color: #fff; }
        /* Bitmovin branding */
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
    <video id="shaka-video" playsinline autoplay muted controls></video>
</div>

<script>
var DASH_URL = 'https://live-oneapp-prd-news.akamaized.net/Content/CMAF_OL2-CTR-4s-v2/Live/channel(kvea)/master.mpd';
var CK_KEYID = 'ce7ab3022e753307997f58afe001bac4';
var CK_KEY   = '72d631a66e635c60829a0fe7705516c1';

var isIOS    = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
var statusEl = document.getElementById('status');

function showPlayer() { statusEl.style.display = 'none'; }
function showError(msg) {
    document.querySelector('.spinner').style.display = 'none';
    document.querySelector('.s-title').textContent = msg;
    console.error('[player]', msg);
}

// ══════════════════════════════════════════════════════════════
// iOS — Shaka Player con MMS + ClearKey
// Shaka tiene soporte específico para CENC-CTR via MMS en iOS 17+
// ══════════════════════════════════════════════════════════════
if (isIOS) {

    var vid = document.getElementById('shaka-video');
    vid.style.display = 'block';

    shaka.polyfill.installAll(); // activa MMS automáticamente en iOS 17+

    if (!shaka.Player.isBrowserSupported()) {
        showError('Este dispositivo no puede reproducir el canal.');
    } else {
        var shk = new shaka.Player(vid);

        shk.configure({
            drm: {
                clearKeys: {
                    // keyId: key  (ambos en hex)
                    [CK_KEYID]: CK_KEY
                }
            },
            streaming: {
                bufferingGoal:        12,   // acumular 12s antes de considerar estable
                rebufferingGoal:      2,
                bufferBehind:         5,
                // Desactivar modo low-latency — CMAF chunked puede causar artefactos
                lowLatencyMode:       false,
                inaccurateManifestTolerance: 0
            },
            abr: {
                enabled:           true,
                defaultBandwidthEstimate: 1000000,  // estimar 1 Mbps al inicio
                switchInterval:    8,   // no cambiar de calidad más de 1 vez cada 8s
                bandwidthUpgradeTarget:   0.85,
                bandwidthDowngradeTarget: 0.95
            }
        });

        shk.addEventListener('error', function (e) {
            console.error('[Shaka] error event:', e.detail);
            showError('Error ' + e.detail.code + ': ' + e.detail.message);
        });

        shk.load(DASH_URL)
            .then(function () {
                console.log('[Shaka] cargado OK');
                showPlayer();
                vid.play().catch(function () {});
            })
            .catch(function (e) {
                console.error('[Shaka] load error:', e);
                showError('Error ' + (e.code || '') + ': ' + (e.message || JSON.stringify(e)));
            });
    }

// ══════════════════════════════════════════════════════════════
// No-iOS — Bitmovin con DASH + ClearKey (funciona bien)
// ══════════════════════════════════════════════════════════════
} else {

    (function () {
        var GRANT = 'data:text/plain;charset=utf-8;base64,eyJzdGF0dXMiOiJncmFudGVkIiwibWVzc2FnZSI6IlRoZXJlIHlvdSBnby4ifQ==';
        function ov(u) {
            if (u.indexOf('licensing.bitmovin.com/licensing')  > -1) return GRANT;
            if (u.indexOf('licensing.bitmovin.com/impression') > -1) return GRANT;
            return u;
        }
        var _o = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function () { arguments[1] = ov(arguments[1]); return _o.apply(this, arguments); };
    })();

    document.addEventListener('DOMContentLoaded', function () {
        var player = new bitmovin.player.Player(document.getElementById('player'), {
            key:      '11d3698c-efdf-42f1-8769-54663995de2b',
            analytics: false,
            cast:     { enable: true },
            playback: { autoplay: true, muted: true },
            style:    { width: '100%', height: '100%' },
            buffer: {
                video: { forwardduration: 30, backwardduration: 5 },
                audio: { forwardduration: 30, backwardduration: 5 }
            },
            adaptation: {
                startupBitrate:    1500000,
                maxStartupBitrate: 3000000
            },
            tweaks: {
                max_video_download_delay: 12,
                startup_threshold:        2
            }
        });

        player.load({ dash: DASH_URL, drm: { clearkey: [{ keyId: CK_KEYID, key: CK_KEY }] } })
            .then(showPlayer)
            .catch(function (err) {
                showError('Error: ' + (err.message || err.code || JSON.stringify(err)));
            });
    });
}
</script>
</body>
</html>
