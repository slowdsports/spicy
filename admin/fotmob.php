<?php
/**
 * StreamHub Admin - Importador de FotMob
 *
 * Uso:
 * admin/fotmob.php?filtrarLiga=77   (ID de liga en FotMob, ej. fotmob.com/leagues/77/...)
 *
 * Los IDs se guardan tal cual los da FotMob, sin offset — FotMob es el único
 * proveedor en uso (Sofascore quedó descartado por su bloqueo anti-bot). Si
 * quedara algún equipo huérfano de una importación vieja de Sofascore con el
 * mismo ID numérico, el UPSERT de más abajo lo sobreescribe con los datos
 * reales de FotMob en vez de dejarlo con el nombre/logo viejo.
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

// Tope de partidos a importar por corrida — una temporada completa (300+)
// no tiene sentido cargarla de una, solo los próximos que realmente se van
// a transmitir pronto.
const MAX_PARTIDOS_IMPORT = 30;

/* ─────────────────────────────────────────────
   Liga recibida (ID real de FotMob)
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
$ligaImgDir   = __DIR__ . '/../assets/img/ligas/fm/';
$ligaDarkDir  = __DIR__ . '/../assets/img/ligas/fm/dark/';
$equipoImgDir = __DIR__ . '/../assets/img/equipos/fm/';

foreach ([$ligaImgDir, $ligaDarkDir, $equipoImgDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

/* ─────────────────────────────────────────────
   Helpers
───────────────────────────────────────────── */
$fotmobLastError = '';

function fotmobFetch(string $url): ?array
{
    global $fotmobLastError;
    $fotmobLastError = '';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);

    $body      = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        $fotmobLastError = "error de conexión: {$curlError}";
        return null;
    }

    if ($httpCode !== 200) {
        $fotmobLastError = "FotMob respondió HTTP {$httpCode}";
        return null;
    }

    $data = json_decode($body, true);
    if ($data === null) {
        $fotmobLastError = 'la respuesta no es JSON válido';
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

/* ─────────────────────────────────────────────
   DB
───────────────────────────────────────────── */
$conn = getDBConnection();
$conn->set_charset("utf8mb4");

/* ─────────────────────────────────────────────
   Liga + partidos (FotMob trae todo en una sola llamada)
───────────────────────────────────────────── */
$leagueData = fotmobFetch("https://www.fotmob.com/api/data/leagues?id={$apiLeague}");

if (!$leagueData || empty($leagueData['details']['id'])) {
    $motivo = $fotmobLastError !== '' ? $fotmobLastError : 'la liga no existe en FotMob';
    exit("No se encontró la liga en FotMob ({$motivo}).");
}

$allMatches = $leagueData['fixtures']['allMatches'] ?? [];

if (empty($allMatches)) {
    exit('No hay partidos para esta liga en FotMob.');
}

// Próximos primero, ordenados por fecha más cercana, tope MAX_PARTIDOS_IMPORT.
// Si no hay ninguno próximo, se cae a los últimos jugados (mismo tope).
$proximos = array_values(array_filter($allMatches, function ($m) {
    return empty($m['status']['finished']) && empty($m['status']['cancelled']);
}));

if (!empty($proximos)) {
    usort($proximos, fn($a, $b) => strcmp($a['status']['utcTime'] ?? '', $b['status']['utcTime'] ?? ''));
    $eventos = array_slice($proximos, 0, MAX_PARTIDOS_IMPORT);
} else {
    $jugados = array_values(array_filter($allMatches, function ($m) {
        return !empty($m['status']['finished']);
    }));
    usort($jugados, fn($a, $b) => strcmp($b['status']['utcTime'] ?? '', $a['status']['utcTime'] ?? ''));
    $eventos = array_slice($jugados, 0, MAX_PARTIDOS_IMPORT);
}

if (empty($eventos)) {
    exit('No hay partidos próximos ni recientes para esta liga en FotMob.');
}

/* ─────────────────────────────────────────────
   Liga
───────────────────────────────────────────── */
$ligaId      = (int)$leagueData['details']['id'];
$ligaName    = $leagueData['details']['name'] ?? '';
$ligaSlug    = $leagueData['details']['seopath'] ?? '';
$ligaSeason  = $leagueData['details']['selectedSeason'] ?? null;
$countryCode = strtolower($leagueData['details']['country'] ?? 'int');
$countryName = $leagueData['details']['country'] ?? 'INT';
$sport       = 'football'; // FotMob solo cubre fútbol

$stmt = $conn->prepare("INSERT IGNORE INTO paises (paisCodigo, paisNombre) VALUES (?, ?)");
$stmt->bind_param("ss", $countryCode, $countryName);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("
    INSERT INTO ligas (id, ligaNombre, ligaImg, ligaPais, tipo, season)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE ligaNombre=VALUES(ligaNombre), ligaImg=VALUES(ligaImg), ligaPais=VALUES(ligaPais), season=VALUES(season)
");
$stmt->bind_param("isssss", $ligaId, $ligaName, $ligaSlug, $countryCode, $sport, $ligaSeason);
$stmt->execute();
$stmt->close();

downloadFile("https://images.fotmob.com/image_resources/logo/leaguelogo/{$ligaId}.png", $ligaImgDir . $ligaId . ".png");
downloadFile("https://images.fotmob.com/image_resources/logo/leaguelogo/{$ligaId}.png", $ligaDarkDir . $ligaId . ".png");

/* ─────────────────────────────────────────────
   Procesar eventos
───────────────────────────────────────────── */
date_default_timezone_set('America/Tegucigalpa');

$agregados = 0;

foreach ($eventos as $m) {

    $homeId  = (int)($m['home']['id'] ?? 0);
    $awayId  = (int)($m['away']['id'] ?? 0);
    $gameId  = (int)($m['id'] ?? 0);
    $utcTime = $m['status']['utcTime'] ?? null;

    if (!$homeId || !$awayId || !$gameId || !$utcTime) continue;

    foreach ([
        [$homeId, $m['home']['name'] ?? ''],
        [$awayId, $m['away']['name'] ?? ''],
    ] as [$tId, $tName]) {

        $logo = "assets/img/equipos/fm/{$tId}.png";
        $stmt = $conn->prepare("
            INSERT INTO equipos (id, nombre, logo, pais)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), logo=VALUES(logo), pais=VALUES(pais)
        ");
        $stmt->bind_param("isss", $tId, $tName, $logo, $countryCode);
        $stmt->execute();
        $stmt->close();

        downloadFile("https://images.fotmob.com/image_resources/logo/teamlogo/{$tId}.png", $equipoImgDir . $tId . ".png");
    }

    $stmt = $conn->prepare("SELECT id FROM partidos WHERE id=?");
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    $existePartido = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if (!$existePartido) {

        $fecha = date('Y-m-d H:i:s', strtotime($utcTime));

        $stmt = $conn->prepare("
            INSERT INTO partidos (
                id, local, visitante, liga, fecha_hora, tipo,
                starp, vix,
                canal1, canal2, canal3, canal4, canal5, canal6, canal7
            )
            VALUES (?, ?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)
        ");
        $stmt->bind_param(
            "iiiiss",
            $gameId,
            $homeId,
            $awayId,
            $ligaId,
            $fecha,
            $sport
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
    echo "✓ Se agregaron {$agregados} partidos de {$ligaName} (FotMob).";
} else {
    echo "No se agregaron partidos nuevos.";
}
