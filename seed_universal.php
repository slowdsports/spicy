<?php
/**
 * Script temporal — sembrar canal "Universal" y sus 5 fuentes DASHM.
 * Ejecutar una sola vez (local o producción) y luego borrar este archivo.
 *
 *   php seed_universal.php
 *
 * Idempotente: si el canal o alguna fuente ya existen (mismo nombre),
 * no los duplica.
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cache.php';

const CANAL_NOMBRE = 'Universal';
const CANAL_LOGO   = 'https://upload.wikimedia.org/wikipedia/commons/9/98/Universal_TV_HD.svg';
const CATEGORIA    = 'Entretenimiento';

$fuentes = [
    [
        'nombre' => 'Premiere',
        'url'    => 'https://unipluslivedash.akamaized.net/universalpremiert0/manifest.mpd',
        'keys'   => [
            '4518f2fc410a5327b26a26e8c0cebd17:16da291229b2b68ded5419cc35339beb',
            '3ef088eaf6685839a1d77c9bc89b1e20:68c8566c7cb4400fac63d0abe8eea6d4',
            '2528892552c65b5399017c2f2a194ab7:fc87001860c95eb094c2dd5fc2d2e2bd',
        ],
    ],
    [
        'nombre' => 'Cinema',
        'url'    => 'https://unipluslivedash.akamaized.net/universalcinemat0/manifest.mpd',
        'keys'   => [
            '693c14512fe754679776b9352fa84fa2:5c04eb44db6dfabcbb73be28408e831d',
            '47b9ccb70c5c512f9195712d11923df6:8a7dba41bad7d0f2a9ebecd3eaf9c2ff',
            'e97732b12ed754588cd3e01e3e6ea7ee:494c340693c70e21b4490c2ec6430627',
        ],
    ],
    [
        'nombre' => 'Crime',
        'url'    => 'https://unipluslivedash.akamaized.net/universalcrimet0/manifest.mpd',
        'keys'   => [
            'd32ac134b9db51b3b9b5b3159dbd40a4:7d9adfe9370b15ad52e128a1c1a2ff2e',
            '1975774dfcbe5243a2b0ceaea09e3b91:7c408bd106bf8bdf6c9fdde9bf35f6fc',
            '4506a08548a4594bb6035540a1a4875c:4b4a1be7658f95a60f0a112ecbc05e6c',
        ],
    ],
    [
        'nombre' => 'Comedy',
        'url'    => 'https://unipluslivedash.akamaized.net/universalcomedyt0/manifest.mpd',
        'keys'   => [
            '5f329c4b801a5edb9a5aaf814d116b0e:71a0806dd2999d5dcbddbdf7dcebe574',
            '62280a1fea50530c8a1a0c3033697693:d62597f8fb6e7a1a84584fa071ef8834',
            '3317a330c1795a2395f35f64966c7f8e:848631ea8ef0c2492f85008f443ba7ad',
        ],
    ],
    [
        'nombre' => 'Reality',
        'url'    => 'https://unipluslivedash.akamaized.net/universalrealityt0/manifest.mpd',
        'keys'   => [
            '25261b6f75ce58bc9be5e25b438e2af7:dd8f7f46cb39d619f63a1b01ecdd0b78',
            'e7e60190bf265d0db1090b8ed7c06c3a:ed2a63ff6b077418967882439a2ab12a',
            '5d494197700f5d238b86c9ef2c40cb1a:171a6b6ba3b7acd781085c03036572e7',
        ],
    ],
];

$conn = getDBConnection();

// ── Categoría del canal ──────────────────────────────────────────────────
$categoriaNombre = CATEGORIA;
$stmt = $conn->prepare('SELECT id FROM categorias_canal WHERE nombre = ? LIMIT 1');
$stmt->bind_param('s', $categoriaNombre);
$stmt->execute();
$catRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$catRow) {
    die("Categoría '" . CATEGORIA . "' no encontrada en categorias_canal.\n");
}
$categoriaId = (int)$catRow['id'];

// ── Tipo de fuente DASHM ──────────────────────────────────────────────────
$stmt = $conn->prepare('SELECT id FROM tipos_fuente WHERE nombre = ? LIMIT 1');
$tipoNombre = 'DASHM';
$stmt->bind_param('s', $tipoNombre);
$stmt->execute();
$tipoRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$tipoRow) {
    die("Tipo 'DASHM' no encontrado en tipos_fuente.\n");
}
$tipoId = (int)$tipoRow['id'];

// ── Canal "Universal" (crear si no existe) ───────────────────────────────
$canalNombre = CANAL_NOMBRE;
$stmt = $conn->prepare('SELECT id FROM canales WHERE nombre = ? LIMIT 1');
$stmt->bind_param('s', $canalNombre);
$stmt->execute();
$canalRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($canalRow) {
    $canalId = (int)$canalRow['id'];
    echo "Canal '" . CANAL_NOMBRE . "' ya existe (id $canalId), se reutiliza.\n";
} else {
    $logo   = CANAL_LOGO;
    $activo = 1;
    $stmt = $conn->prepare('INSERT INTO canales (nombre, logo, category, activo) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssii', $canalNombre, $logo, $categoriaId, $activo);
    $stmt->execute();
    $canalId = $stmt->insert_id;
    $stmt->close();
    echo "Canal '" . CANAL_NOMBRE . "' creado (id $canalId).\n";
}

// ── Fuentes ───────────────────────────────────────────────────────────────
foreach ($fuentes as $f) {
    $stmt = $conn->prepare('SELECT id FROM fuentes WHERE nombre = ? AND canal = ? LIMIT 1');
    $stmt->bind_param('si', $f['nombre'], $canalId);
    $stmt->execute();
    $existe = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existe) {
        echo "  Fuente '{$f['nombre']}' ya existe (id {$existe['id']}), se omite.\n";
        continue;
    }

    $ckKey = implode("\n", array_map(fn($k) => '--key ' . $k, $f['keys']));

    $nombre     = $f['nombre'];
    $url        = $f['url'];
    $urlIos     = null;
    $tipoIos    = 'hls';
    $ckKeyId    = null;
    $pais       = null;
    $epg        = null;
    $activo     = 1;
    $sandbox    = 1;
    $mostrarTv  = 1;
    $reproductor = 'bitmovin';
    $usarProxy  = 0;
    $soloSpicy  = 0;

    $stmt = $conn->prepare(
        'INSERT INTO fuentes
            (nombre, canal, url, url_ios, tipo_ios, ck_key, ck_keyid, pais, tipo, epg, activo, sandbox, mostrar_tv, reproductor, usar_proxy, solo_spicy)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );
    $stmt->bind_param(
        'sissssssissiisii',
        $nombre, $canalId, $url, $urlIos, $tipoIos, $ckKey, $ckKeyId, $pais, $tipoId, $epg, $activo, $sandbox, $mostrarTv, $reproductor, $usarProxy, $soloSpicy
    );
    $stmt->execute();
    $fuenteId = $stmt->insert_id;
    $stmt->close();

    echo "  Fuente '{$f['nombre']}' creada (id $fuenteId).\n";
}

// ── Regenerar cachés JSON que lee el sitio público ───────────────────────
regenerateCanalesCache();
regenerateFuentesCache();

echo "Listo. Cachés regeneradas.\n";
