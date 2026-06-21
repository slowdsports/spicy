<?php
/**
 * StreamHub Admin - Importador de FotMob
 *
 * Uso:
 * admin/fotmob.php?filtrarLiga=77   (ID de liga en FotMob, ej. fotmob.com/leagues/77/...)
 *
 * FotMob usa su propio espacio de IDs (ligas, equipos, partidos), que puede
 * colisionar con los IDs ya usados por Sofascore en las mismas tablas. Para
 * evitarlo, todos los IDs que vienen de FotMob se guardan con un offset fijo
 * (+900000000). El offset es invisible para el admin: aquí siempre se recibe
 * y se llama a la API de FotMob con el ID real, sin offset.
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

const FOTMOB_OFFSET = 900000000;

/* ─────────────────────────────────────────────
   Liga recibida (ID real de FotMob, sin offset)
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

// Próximos primero (igual que Sofascore: next, y si no hay, last)
$proximos = array_values(array_filter($allMatches, function ($m) {
    return empty($m['status']['finished']) && empty($m['status']['cancelled']);
}));

if (!empty($proximos)) {
    $eventos = $proximos;
} else {
    $jugados = array_values(array_filter($allMatches, function ($m) {
        return !empty($m['status']['finished']);
    }));
    usort($jugados, fn($a, $b) => strcmp($b['status']['utcTime'] ?? '', $a['status']['utcTime'] ?? ''));
    $eventos = array_slice($jugados, 0, 20);
}

if (empty($eventos)) {
    exit('No hay partidos próximos ni recientes para esta liga en FotMob.');
}

/* ─────────────────────────────────────────────
   Liga
───────────────────────────────────────────── */
$ligaIdReal  = (int)$leagueData['details']['id'];
$ligaId      = FOTMOB_OFFSET + $ligaIdReal;
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

$stmt = $conn->prepare("SELECT id FROM ligas WHERE id=?");
$stmt->bind_param("i", $ligaId);
$stmt->execute();
$existeLiga = $stmt->get_result()->num_rows > 0;
$stmt->close();

if (!$existeLiga) {
    $stmt = $conn->prepare("
        INSERT INTO ligas (id, ligaNombre, ligaImg, ligaPais, tipo, season)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssss", $ligaId, $ligaName, $ligaSlug, $countryCode, $sport, $ligaSeason);
    $stmt->execute();
    $stmt->close();
}

downloadFile("https://images.fotmob.com/image_resources/logo/leaguelogo/{$ligaIdReal}.png", $ligaImgDir . $ligaId . ".png");
downloadFile("https://images.fotmob.com/image_resources/logo/leaguelogo/{$ligaIdReal}.png", $ligaDarkDir . $ligaId . ".png");

/* ─────────────────────────────────────────────
   Procesar eventos
───────────────────────────────────────────── */
date_default_timezone_set('America/Tegucigalpa');

$agregados = 0;

foreach ($eventos as $m) {

    $homeIdReal  = (int)($m['home']['id'] ?? 0);
    $awayIdReal  = (int)($m['away']['id'] ?? 0);
    $matchIdReal = (int)($m['id'] ?? 0);
    $utcTime     = $m['status']['utcTime'] ?? null;

    if (!$homeIdReal || !$awayIdReal || !$matchIdReal || !$utcTime) continue;

    $homeId = FOTMOB_OFFSET + $homeIdReal;
    $awayId = FOTMOB_OFFSET + $awayIdReal;
    $gameId = FOTMOB_OFFSET + $matchIdReal;

    foreach ([
        [$homeId, $homeIdReal, $m['home']['name'] ?? ''],
        [$awayId, $awayIdReal, $m['away']['name'] ?? ''],
    ] as [$tId, $tIdReal, $tName]) {

        $stmt = $conn->prepare("SELECT id FROM equipos WHERE id=?");
        $stmt->bind_param("i", $tId);
        $stmt->execute();
        $existeEquipo = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$existeEquipo) {
            $logo = "assets/img/equipos/fm/{$tId}.png";
            $stmt = $conn->prepare("INSERT INTO equipos (id, nombre, logo, pais) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $tId, $tName, $logo, $countryCode);
            $stmt->execute();
            $stmt->close();
        }

        downloadFile("https://images.fotmob.com/image_resources/logo/teamlogo/{$tIdReal}.png", $equipoImgDir . $tId . ".png");
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
