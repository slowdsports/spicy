<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="cache-control" content="no-cache">
    <meta name="robots" content="noindex">
    <meta name="referrer" content="none">
    <title>KVEA Test</title>
    <script src="//cdn.bitmovin.com/player/web/8/bitmovinplayer.js"></script>
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
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .s-title { font-size: 1.15em; font-weight: 600; color: #fff; }
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
// Bypass de licencia Bitmovin
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

var statusEl = document.getElementById('status');
function showPlayer() { statusEl.style.display = 'none'; }

document.addEventListener('DOMContentLoaded', function () {
    var player = new bitmovin.player.Player(document.getElementById('player'), {
        key:      '11d3698c-efdf-42f1-8769-54663995de2b',
        analytics: false,
        cast:     { enable: true },
        playback: { autoplay: true, muted: true },
        style:    { width: '100%', height: '100%' }
    });

    var source = {
        dash: 'https://live-oneapp-prd-news.akamaized.net/Content/CMAF_OL2-CTR-4s-v2/Live/channel(kvea)/master.mpd',
        drm: {
            clearkey: [{
                keyId: 'ce7ab3022e753307997f58afe001bac4',
                key:   '72d631a66e635c60829a0fe7705516c1'
            }]
        }
    };

    player.load(source).then(showPlayer).catch(function (err) {
        console.error('Bitmovin error:', err);
        document.querySelector('.s-title').textContent = 'Error: ' + (err.message || err.code || JSON.stringify(err));
    });
});
</script>
</body>
</html>
