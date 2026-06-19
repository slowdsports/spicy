<?php
/**
 * StreamHub Admin - Importador de partidos de la Liga 16 (FIFA World Cup)
 * desde un archivo JSON local (16.json), sin llamar a la API de Sofascore.
 *
 * Caso de uso: cuando el servidor no puede alcanzar api.sofascore.com
 * (bloqueo/rate-limit), se sube manualmente el JSON exportado
 * (respuesta de /unique-tournament/16/season/{id}/events/next/0)
 * junto a este script, y este se encarga de insertarlo en la BD.
 *
 * Uso: admin/importar_liga16_json.php
 * Requiere que 16.json esté en la raíz del proyecto (junto a index.php).
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

const LIGA_ID_FIJA = 16;
$jsonPath = __DIR__ . '/../16.json';

/* ─────────────────────────────────────────────
   Carpetas imágenes
───────────────────────────────────────────── */
$ligaImgDir   = __DIR__ . '/../assets/img/ligas/sf/';
$ligaDarkDir  = __DIR__ . '/../assets/img/ligas/sf/dark/';
$equipoImgDir = __DIR__ . '/../assets/img/equipos/sf/';

foreach ([$ligaImgDir, $ligaDarkDir, $equipoImgDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

/* ─────────────────────────────────────────────
   Helpers
───────────────────────────────────────────── */
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
   Cargar JSON local
───────────────────────────────────────────── */
if (!is_file($jsonPath)) {
    exit("Error: no se encontró el archivo 16.json en la raíz del proyecto ({$jsonPath}).");
}

$raw  = file_get_contents($jsonPath);
$data = json_decode($raw, true);

if ($data === null) {
    exit("Error: 16.json no es un JSON válido (" . json_last_error_msg() . ").");
}

if (empty($data['events'])) {
    exit("Error: 16.json no contiene eventos (clave 'events' vacía o ausente).");
}

/* ─────────────────────────────────────────────
   DB
───────────────────────────────────────────── */
$conn = getDBConnection();
$conn->set_charset("utf8mb4");

$fuentesValidas = array_flip(array_column(
    $conn->query("SELECT id FROM fuentes")->fetch_all(MYSQLI_ASSOC),
    'id'
));

$agregados = 0;
$omitidos  = 0;
$ligaNombreGlobal = '';

date_default_timezone_set('America/Tegucigalpa');

/* ─────────────────────────────────────────────
   Procesar eventos
───────────────────────────────────────────── */
foreach ($data['events'] as $event) {

    $ligaId = (int)($event['tournament']['uniqueTournament']['id'] ?? 0);

    // Por seguridad, este script solo inserta partidos de la liga 16
    if ($ligaId !== LIGA_ID_FIJA) {
        $omitidos++;
        continue;
    }

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
    $ligaName = $event['tournament']['name'] ?? '';
    $ligaSlug = $event['tournament']['slug'] ?? '';
    $sport    = $event['tournament']['uniqueTournament']['category']['sport']['slug'] ?? 'football';
    $seasonId = (int)($event['season']['id'] ?? 0);

    $ligaNombreGlobal = $event['tournament']['uniqueTournament']['name'] ?? $ligaName;

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
            $ligaNombreGlobal,
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
    if ($omitidos > 0) {
        echo " ({$omitidos} eventos del JSON fueron omitidos por no ser de la liga " . LIGA_ID_FIJA . ".)";
    }
} else {
    echo "No se agregaron partidos nuevos.";
    if ($omitidos > 0) {
        echo " ({$omitidos} eventos del JSON fueron omitidos por no ser de la liga " . LIGA_ID_FIJA . ".)";
    }
}
