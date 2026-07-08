<?php
/**
 * StreamHub - Reproductor Tipo 4: DASH (Bitmovin, multi-key ClearKey)
 * ============================================================
 * Bitmovin Player v8 con bypass de licencia (igual que reproductor-3).
 * A diferencia de reproductor-3, este soporta VARIAS claves ClearKey por
 * canal (no solo un par keyId:key), útil cuando un stream DASH usa
 * distintas keys por período/track. El campo ck_key en BD puede traer:
 *
 *   - Un solo par:       keyId (en ck_keyid) + key (en ck_key)
 *   - Varios pares en ck_key, uno por línea, estilo shaka-packager/mp4decrypt:
 *       --key 4518f2fc410a5327b26a26e8c0cebd17:16da291229b2b68ded5419cc35339beb
 *       --key 3ef088eaf6685839a1d77c9bc89b1e20:68c8566c7cb4400fac63d0abe8eea6d4
 *       --key 2528892552c65b5399017c2f2a194ab7:fc87001860c95eb094c2dd5fc2d2e2bd
 *     (el prefijo "--key" es opcional; también acepta separarlos por
 *     comas, espacios o saltos de línea)
 */

if (!isset($fuenteData)) {
    die('Acceso denegado');
}

$nombre = htmlspecialchars($fuenteData['nombre']);

// URL y DRM keys NO se pasan al cliente — las sirve api/stream.php bajo token firmado
$jsBase = json_encode(BASE_URL);
$jsFid  = (int)$streamFuenteId;
$jsTok  = json_encode($streamToken);
$jsTs   = (int)$streamTs;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../includes/ads.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> - Tele Deportes</title>
    <script src="//cdn.bitmovin.com/player/web/8/bitmovinplayer.js"></script>
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
        // CONFIGURACIÓN DE REPRODUCCIÓN DASH
        // ============================================================
        const PLAYER_CONFIG = {
            id: <?= (int)$fuenteData['id'] ?>,
            nombre: '<?= $nombre ?>',
            tipo: 4,
        };

        // ── Token de sesión (URL y DRM keys nunca aparecen en el source) ────
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

        /**
         * Extrae uno o varios pares ClearKey (keyId:key) de los campos que
         * devuelve api/stream.php. Soporta:
         *   - Un solo par en keyId/key (formato clásico de las otras fuentes)
         *   - Varios pares empaquetados en "key", uno por línea, con o sin
         *     el prefijo "--key" (estilo shaka-packager / mp4decrypt), o
         *     separados por comas/espacios.
         * Devuelve un array de { kid, key } listo para bitmovin (la propiedad
         * del keyId en la API de Bitmovin se llama "kid", no "keyId"), o [].
         */
        function parseClearKeys(keyId, key) {
            var pairs = [];
            var re = /([0-9a-fA-F]{16,64})\s*:\s*([0-9a-fA-F]{16,64})/g;
            var match;
            if (key) {
                while ((match = re.exec(key)) !== null) {
                    pairs.push({ kid: match[1], key: match[2] });
                }
            }
            if (pairs.length > 0) return pairs;
            if (keyId && key) return [{ kid: keyId, key: key }];
            return [];
        }

        // Solicitar datos al servidor — token HMAC vinculado a la sesión del usuario
        document.addEventListener('DOMContentLoaded', function() {
            fetch(_BASE + 'api/stream.php?id=' + _FID + '&ts=' + _TS + '&t=' + encodeURIComponent(_TOK), {
                credentials: 'same-origin'
            })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (sd) {
                if (!sd.url) { showError('Stream no disponible'); return; }

                var player = new bitmovin.player.Player(document.getElementById('player'), {
                    key:       '11d3698c-efdf-42f1-8769-54663995de2b',
                    analytics: false,
                    cast:      { enable: true },
                    playback:  { autoplay: true, muted: true },
                    style:     { width: '100%', height: '100%' }
                });
                window.player = player;

                var source     = { dash: sd.url };
                var clearKeys  = parseClearKeys(sd.keyId || '', sd.key || '');
                if (clearKeys.length > 0) {
                    source.drm = { clearkey: clearKeys };
                }

                player.load(source).then(showPlayer).catch(function (err) {
                    showError('Error al cargar el stream.');
                    console.error('Bitmovin error:', err);
                });
            })
            .catch(function (err) {
                showError('No se pudo conectar con el servidor.');
                console.error(err);
            });
        });
    </script>
</body>
</html>
