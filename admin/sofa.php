<?php
/**
 * StreamHub Admin - Importador de Sofascore (CORREGIDO)
 *
 * Uso:
 * admin/sofa.php?filtrarLiga=17
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

/* ─────────────────────────────────────────────
   Seguridad
───────────────────────────────────────────── */
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    exit('Sin permisos.');
}

/* ─────────────────────────────────────────────
   Liga recibida
───────────────────────────────────────────── */
$apiLeague = isset($_POST['filtrarLiga'])
    ? (int)$_POST['filtrarLiga']
    : (int)($_GET['filtrarLiga'] ?? 0);

if (!$apiLeague) {
    exit('Error: no se especificó liga.');
}

/* ─────────────────────────────────────────────
   Carpetas imágenes
───────────────────────────────────────────── */
$ligaImgDir  = __DIR__ . '/../assets/img/ligas/sf/';
$ligaDarkDir = __DIR__ . '/../assets/img/ligas/sf/dark/';
$equipoImgDir = __DIR__ . '/../assets/img/equipos/sf/';

foreach ([$ligaImgDir, $ligaDarkDir, $equipoImgDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

/* ─────────────────────────────────────────────
   Helpers
───────────────────────────────────────────── */
$sofaLastError = '';

// Sofascore bloquea a nivel de fingerprint TLS (Fastly Bot Manager) cualquier
// cliente que no sea un navegador real — curl con cualquier combinación de
// headers recibe 403 siempre, incluso en el home. tools/sofascore-bridge abre
// un Chromium real vía Playwright para pasar ese chequeo.
//
// - Si SOFA_BRIDGE_URL está seteado (.env.php): se le pega por HTTP a un
//   bridge ya corriendo (local en dev, o desplegado en Render en producción,
//   donde el hosting compartido no puede correr Chromium).
// - Si no está seteado (caso típico en local): se invoca tools/sofascore-bridge/fetch.js
//   directo con proc_open, sin necesidad de tener el server HTTP corriendo aparte.
function sofaFetch(string $url): ?array
{
    global $sofaLastError;
    $sofaLastError = '';

    if (SOFA_BRIDGE_URL !== '') {
        return sofaFetchViaHttpBridge($url);
    }
    return sofaFetchViaLocalProcess($url);
}

function sofaFetchViaHttpBridge(string $url): ?array
{
    global $sofaLastError;

    $endpoint = rtrim(SOFA_BRIDGE_URL, '/') . '/fetch?' . http_build_query(['url' => $url]);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 45, // Render free tier puede tardar en despertar (cold start)
        CURLOPT_HTTPHEADER     => SOFA_BRIDGE_SECRET !== '' ? ["x-bridge-key: " . SOFA_BRIDGE_SECRET] : [],
    ]);

    $body      = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        $sofaLastError = "bridge HTTP: error de conexión: {$curlError}";
        return null;
    }

    if ($httpCode !== 200) {
        $detalle = is_string($body) ? trim(substr($body, 0, 200)) : '';
        $sofaLastError = "bridge HTTP respondió {$httpCode}" . ($detalle !== '' ? " — {$detalle}" : '');
        return null;
    }

    $data = json_decode($body, true);
    if ($data === null) {
        $sofaLastError = 'la respuesta del bridge no es JSON válido';
        return null;
    }

    return $data;
}

function sofaFetchViaLocalProcess(string $url): ?array
{
    global $sofaLastError;

    $node   = 'node';
    $script = __DIR__ . '/../tools/sofascore-bridge/fetch.js';
    $cmd    = escapeshellarg($node) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($url);

    $descriptors = [
        1 => ['pipe', 'w'], // stdout
        2 => ['pipe', 'w'], // stderr
    ];
    $process = proc_open($cmd, $descriptors, $pipes);

    if (!is_resource($process)) {
        $sofaLastError = 'no se pudo iniciar el bridge de Node/Playwright';
        return null;
    }

    $body   = stream_get_contents($pipes[1]);
    $errOut = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        $detalle = trim($errOut) !== '' ? trim($errOut) : "exit code {$exitCode}";
        $sofaLastError = "bridge Playwright falló: {$detalle}";
        return null;
    }

    $data = json_decode($body, true);
    if ($data === null) {
        $sofaLastError = 'la respuesta no es JSON válido';
        return null;
    }

    return $data;
}

function downloadFile(string $url, string $dest): void
{
    if (file_exists($dest)) return;

    $bin = @file_get_contents($url);
    if ($bin) {
        @file_put_contents($dest, $bin);
    }
}

function asignarCanalesDefecto(int $ligaId, string $sport): array
{
    $c = [
        'starp' => null,
        'vix'   => null,
        'canal1'=> null,
        'canal2'=> null,
        'canal3'=> null,
        'canal4'=> null,
        'canal5'=> null,
        'canal6'=> null,
        'canal7'=> null
    ];

    if ($sport === 'tennis') {
        $c['canal1']=49;
        $c['canal2']=90;
        $c['canal3']=314;
        $c['canal4']=315;
        $c['canal5']=144;
        $c['canal6']=145;
        $c['canal7']=146;
        $c['starp']=1;
        return $c;
    }

    switch ($ligaId) {
        case 8:
            $c['canal3']=314;
            break;

        case 54:
            $c['canal1']=32;
            $c['canal2']=33;
            $c['canal3']=34;
            $c['canal4']=35;
            $c['canal5']=36;
            break;

        case 7:
        case 679:
            $c['starp']=1;
            $c['vix']=1;
            break;

        case 17:
        case 23:
        case 35:
        case 278:
        case 17015:
            $c['starp']=1;
            break;

        case 325:
        case 279:
        case 11621:
        case 11536:
        case 11539:
        case 13475:
            $c['vix']=1;
            break;
    }

    return $c;
}

/* ─────────────────────────────────────────────
   DB
───────────────────────────────────────────── */
$conn = getDBConnection();
$conn->set_charset("utf8mb4");

// IDs de fuentes que realmente existen (para evitar FK violations al insertar)
$fuentesValidas = array_flip(array_column(
    $conn->query("SELECT id FROM fuentes")->fetch_all(MYSQLI_ASSOC),
    'id'
));

$agregados = 0;
$ligaNombreGlobal = '';

/* ─────────────────────────────────────────────
   Temporada
───────────────────────────────────────────── */
$seasonData = sofaFetch("https://api.sofascore.com/api/v1/unique-tournament/{$apiLeague}/seasons");

if (!$seasonData || empty($seasonData['seasons'])) {
    $motivo = $sofaLastError !== '' ? $sofaLastError : 'la liga no tiene temporadas registradas en Sofascore';
    exit("No se encontró temporada ({$motivo}).");
}

$seasonId = (int)$seasonData['seasons'][0]['id'];

/* ─────────────────────────────────────────────
   Próximos partidos
───────────────────────────────────────────── */
$eventsData = sofaFetch(
    "https://api.sofascore.com/api/v1/unique-tournament/{$apiLeague}/season/{$seasonId}/events/next/0"
);

if (!$eventsData || empty($eventsData['events'])) {
    $eventsData = sofaFetch(
        "https://api.sofascore.com/api/v1/unique-tournament/{$apiLeague}/season/{$seasonId}/events/last/0"
    );
}

if (!$eventsData || empty($eventsData['events'])) {
    $motivo = $sofaLastError !== '' ? $sofaLastError : 'no hay partidos próximos ni recientes para esta liga';
    exit("No hay partidos ({$motivo}).");
}

/* ─────────────────────────────────────────────
   Procesar eventos
───────────────────────────────────────────── */
date_default_timezone_set('America/Tegucigalpa');

foreach ($eventsData['events'] as $event) {

    /* País */
    $countryCode = $event['tournament']['uniqueTournament']['category']['slug'] ?? 'international';
    $countryName = $event['tournament']['uniqueTournament']['category']['name'] ?? 'International';

    $stmt = $conn->prepare("
        INSERT IGNORE INTO paises (paisCodigo, paisNombre)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ss", $countryCode, $countryName);
    $stmt->execute();
    $stmt->close();

    /* Liga */
    $ligaId   = (int)$event['tournament']['uniqueTournament']['id'];
    $ligaName = $event['tournament']['name'] ?? '';
    $ligaSlug = $event['tournament']['slug'] ?? '';
    $sport    = $event['tournament']['uniqueTournament']['category']['sport']['slug'] ?? 'football';

    $ligaNombreGlobal = $ligaName;

    $stmt = $conn->prepare("SELECT id FROM ligas WHERE id=?");
    $stmt->bind_param("i", $ligaId);
    $stmt->execute();
    $existeLiga = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if (!$existeLiga) {

        $stmt = $conn->prepare("
            INSERT INTO ligas
            (id, ligaNombre, ligaImg, ligaPais, tipo, season)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "issssi",
            $ligaId,
            $ligaName,
            $ligaSlug,
            $countryCode,
            $sport,
            $seasonId
        );
        $stmt->execute();
        $stmt->close();
    }

    downloadFile(
        "https://api.sofascore.app/api/v1/unique-tournament/{$ligaId}/image",
        $ligaImgDir . $ligaId . ".png"
    );

    downloadFile(
        "https://api.sofascore.app/api/v1/unique-tournament/{$ligaId}/image/dark",
        $ligaDarkDir . $ligaId . ".png"
    );

    /* Equipo local */
    $homeId   = (int)$event['homeTeam']['id'];
    $homeName = $event['homeTeam']['name'] ?? '';

    $stmt = $conn->prepare("SELECT id FROM equipos WHERE id=?");
    $stmt->bind_param("i", $homeId);
    $stmt->execute();
    $homeExiste = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if (!$homeExiste) {
        $logo = "assets/img/equipos/sf/{$homeId}.png";

        $stmt = $conn->prepare("
            INSERT INTO equipos (id, nombre, logo, pais)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $homeId, $homeName, $logo, $countryCode);
        $stmt->execute();
        $stmt->close();
    }

    downloadFile(
        "https://api.sofascore.app/api/v1/team/{$homeId}/image",
        $equipoImgDir . $homeId . ".png"
    );

    /* Equipo visitante */
    $awayId   = (int)$event['awayTeam']['id'];
    $awayName = $event['awayTeam']['name'] ?? '';

    $stmt = $conn->prepare("SELECT id FROM equipos WHERE id=?");
    $stmt->bind_param("i", $awayId);
    $stmt->execute();
    $awayExiste = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if (!$awayExiste) {
        $logo = "assets/img/equipos/sf/{$awayId}.png";

        $stmt = $conn->prepare("
            INSERT INTO equipos (id, nombre, logo, pais)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $awayId, $awayName, $logo, $countryCode);
        $stmt->execute();
        $stmt->close();
    }

    downloadFile(
        "https://api.sofascore.app/api/v1/team/{$awayId}/image",
        $equipoImgDir . $awayId . ".png"
    );

    /* Partido */
    $gameId = (int)$event['id'];

    $stmt = $conn->prepare("SELECT id FROM partidos WHERE id=?");
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    $existePartido = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if (!$existePartido) {

        $fecha = date('Y-m-d H:i:s', $event['startTimestamp']);

        $canales = asignarCanalesDefecto($ligaId, $sport);

        // Nullificar cualquier canal cuyo ID no exista en fuentes
        foreach ($canales as &$v) {
            if ($v !== null && !isset($fuentesValidas[$v])) $v = null;
        }
        unset($v);

        $stmt = $conn->prepare("
            INSERT INTO partidos (
                id, local, visitante, liga, fecha_hora, tipo,
                starp, vix,
                canal1, canal2, canal3, canal4, canal5, canal6, canal7
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiiissiiiiiiiii",
            $gameId,
            $homeId,
            $awayId,
            $ligaId,
            $fecha,
            $sport,
            $canales['starp'],
            $canales['vix'],
            $canales['canal1'],
            $canales['canal2'],
            $canales['canal3'],
            $canales['canal4'],
            $canales['canal5'],
            $canales['canal6'],
            $canales['canal7']
        );

        $stmt->execute();
        $stmt->close();

        $agregados++;
    }
}

/* ─────────────────────────────────────────────
   Resultado
───────────────────────────────────────────── */
if ($agregados > 0) {
    echo "✓ Se agregaron {$agregados} partidos de {$ligaNombreGlobal}.";
} else {
    echo "No se agregaron partidos nuevos.";
}