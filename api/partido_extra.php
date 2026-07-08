<?php
/**
 * API pública — Datos extendidos de un partido (tabla de posiciones, h2h,
 * alineaciones, goleadores, MVP, estadísticas y timeline), consumido por
 * assets/js/partido.js para llenar los skeletons de pages/partido.php de
 * forma asíncrona.
 *
 * Separado de api/live_score.php a propósito: esto no necesita frescura de
 * segundo a segundo (la tabla/h2h/alineaciones/stats casi no cambian
 * durante el partido), así que cachea más tiempo y no se re-consulta con
 * polling.
 *
 * Las fotos de jugadores (MVP, goleadores) se descargan una sola vez a
 * assets/img/jugadores/ — los siguientes usuarios que visiten cualquier
 * partido con ese jugador ya las sirven desde el propio servidor, sin
 * volver a pegarle a la CDN de FotMob.
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

function respond(array $data, int $httpCode = 200): never {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// FotMob manda las etiquetas de stats en inglés (y algunas con el prefijo
// "FotMob rating" tal cual) — se traducen acá, con fallback al texto
// original si aparece una etiqueta nueva que no está en la tabla.
const STAT_LABELS_ES = [
    'FotMob rating'               => 'Calificación',
    'Minutes played'               => 'Minutos jugados',
    'Goals'                        => 'Goles',
    'Assists'                      => 'Asistencias',
    'Total shots'                  => 'Remates totales',
    'Accurate passes'              => 'Pases precisos',
    'Chances created'              => 'Ocasiones creadas',
    'Shots on target'              => 'Remates al arco',
    'Shots off target'             => 'Remates desviados',
    'Shot accuracy'                => 'Precisión de remate',
    'Defensive actions'            => 'Acciones defensivas',
    'Ball possession'              => 'Posesión de balón',
    'Touches'                      => 'Toques de balón',
    'Touches in opposition box'    => 'Toques en área rival',
    'Big chances'                  => 'Ocasiones claras',
    'Big chances missed'           => 'Ocasiones claras falladas',
    'Hit woodwork'                 => 'Tiros al palo',
    'Passes into final third'      => 'Pases al último tercio',
    'Accurate crosses'             => 'Centros precisos',
    'Accurate long balls'          => 'Balones largos precisos',
    'Dispossessed'                 => 'Pérdidas de balón',
    'Tackles'                      => 'Entradas',
    'Blocks'                       => 'Bloqueos',
    'Clearances'                   => 'Despejes',
    'Interceptions'                => 'Intercepciones',
    'Recoveries'                   => 'Recuperaciones',
    'Dribbled past'                => 'Regateado',
    'Ground duels won'             => 'Duelos terrestres ganados',
    'Aerial duels won'             => 'Duelos aéreos ganados',
    'Was fouled'                   => 'Faltas recibidas',
    'Fouls committed'              => 'Faltas cometidas',
    'Duels won'                    => 'Duelos ganados',
    'Duels lost'                   => 'Duelos perdidos',
    'Corners'                      => 'Córners',
    'Offsides'                     => 'Fueras de lugar',
    'Yellow cards'                 => 'Tarjetas amarillas',
    'Red cards'                    => 'Tarjetas rojas',
    'Saves'                        => 'Atajadas',
    'Expected goals (xG)'          => 'Goles esperados (xG)',
    'Expected goals on target (xGOT)' => 'xG al arco (xGOT)',
];

function translateStat(string $label): string {
    return STAT_LABELS_ES[$label] ?? $label;
}

function downloadPlayerImage(?int $playerId): ?string {
    if (!$playerId) return null;

    $dir  = __DIR__ . '/../assets/img/jugadores';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $dest = $dir . "/{$playerId}.png";
    $publicPath = 'assets/img/jugadores/' . $playerId . '.png';

    if (file_exists($dest)) return $publicPath;

    $bin = @file_get_contents("https://images.fotmob.com/image_resources/playerimages/{$playerId}.png");
    if ($bin) {
        @file_put_contents($dest, $bin);
        return $publicPath;
    }
    return null;
}

$partidoId = (int)($_GET['id'] ?? 0);
$ligaId    = (int)($_GET['liga'] ?? 0);

if (!$partidoId) {
    respond(['ok' => false, 'error' => 'id de partido inválido'], 404);
}

// Las dos llamadas a FotMob se hacen en paralelo (curl_multi) — secuenciales
// tardarían el doble, y es justo la demora que queremos evitar.
$mh = curl_multi_init();
$handles = [];

$mkHandle = function (string $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    return $ch;
};

$matchCacheFile  = __DIR__ . "/../data/live_cache/match_full_{$partidoId}.json";
$leagueCacheFile = $ligaId ? __DIR__ . "/../data/live_cache/league_table_{$ligaId}.json" : null;

$needMatch  = !(file_exists($matchCacheFile) && (time() - filemtime($matchCacheFile)) < 60);
$needLeague = $ligaId && !($leagueCacheFile && file_exists($leagueCacheFile) && (time() - filemtime($leagueCacheFile)) < 600);

if ($needMatch) {
    $handles['match'] = $mkHandle("https://www.fotmob.com/api/data/matchDetails?matchId={$partidoId}");
    curl_multi_add_handle($mh, $handles['match']);
}
if ($needLeague) {
    $handles['league'] = $mkHandle("https://www.fotmob.com/api/data/leagues?id={$ligaId}");
    curl_multi_add_handle($mh, $handles['league']);
}

if ($handles) {
    $running = null;
    do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running > 0);

    foreach ($handles as $key => $ch) {
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = curl_multi_getcontent($ch);
        if ($code === 200 && $body) {
            $data = json_decode($body, true);
            if ($data !== null) {
                $dir = __DIR__ . '/../data/live_cache';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $file = $key === 'match' ? $matchCacheFile : $leagueCacheFile;
                file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        }
        curl_multi_remove_handle($mh, $ch);
    }
}
curl_multi_close($mh);

$matchDetails = file_exists($matchCacheFile) ? (json_decode(file_get_contents($matchCacheFile), true) ?: null) : null;
$leagueData   = $leagueCacheFile && file_exists($leagueCacheFile) ? (json_decode(file_get_contents($leagueCacheFile), true) ?: null) : null;

/* ── Tabla de posiciones ─────────────────────────────────────────── */
$standings = $leagueData['table'][0]['data']['table']['all'] ?? null;
if (is_array($standings) && (!isset($standings[0]) || !isset($standings[0]['pts']))) {
    $standings = null;
}

/* ── H2H y alineaciones ──────────────────────────────────────────── */
$h2hMatches = $matchDetails['content']['h2h']['matches']     ?? [];
$lineupHome = $matchDetails['content']['lineup']['homeTeam'] ?? null;
$lineupAway = $matchDetails['content']['lineup']['awayTeam'] ?? null;

/* ── Estado del partido (más rico que matches.json: started/finished
   reales de FotMob, no nuestra ventana de 3h) ──────────────────────── */
$hStatus = $matchDetails['header']['status'] ?? [];
$matchStatus = [
    'started'   => (bool)($hStatus['started']   ?? false),
    'finished'  => (bool)($hStatus['finished']  ?? false),
    'cancelled' => (bool)($hStatus['cancelled'] ?? false),
];

/* ── Goleadores (con foto local descargada una vez) ─────────────────── */
function extractGoals(array $goalsByPlayer): array {
    $out = [];
    foreach ($goalsByPlayer as $events) {
        foreach ($events as $ev) {
            $out[] = [
                'minute'    => (string)($ev['timeStr'] ?? $ev['time'] ?? ''),
                'player'    => $ev['fullName'] ?? $ev['nameStr'] ?? '',
                'playerId'  => (int)($ev['playerId'] ?? 0),
                'photo'     => downloadPlayerImage((int)($ev['playerId'] ?? 0)),
                'ownGoal'   => !empty($ev['ownGoal']),
                'assist'    => $ev['assistInput'] ?? null,
                'time'      => (int)($ev['time'] ?? 0),
            ];
        }
    }
    usort($out, fn($a, $b) => $a['time'] <=> $b['time']);
    return $out;
}
$homeGoals = extractGoals($matchDetails['header']['events']['homeTeamGoals'] ?? []);
$awayGoals = extractGoals($matchDetails['header']['events']['awayTeamGoals'] ?? []);

/* ── MVP (jugador del partido) ───────────────────────────────────── */
$potm = $matchDetails['content']['matchFacts']['playerOfTheMatch'] ?? null;
$mvp  = null;
if ($potm) {
    $topStats = [];
    foreach (($potm['stats'][0]['stats'] ?? []) as $label => $s) {
        $topStats[] = ['label' => translateStat($label), 'value' => $s['stat']['value'] ?? null];
    }
    $mvp = [
        'id'     => (int)($potm['id'] ?? 0),
        'name'   => $potm['name']['fullName'] ?? '',
        'team'   => $potm['teamName'] ?? '',
        'rating' => $potm['rating']['num'] ?? null,
        'photo'  => downloadPlayerImage((int)($potm['id'] ?? 0)),
        'stats'  => array_slice($topStats, 0, 6),
    ];
}

/* ── Estadísticas comparativas (posesión, tiros, etc.) ──────────────── */
$statRows = $matchDetails['content']['stats']['Periods']['All']['stats'][0]['stats'] ?? [];
$teamStats = [];
foreach ($statRows as $row) {
    if (!isset($row['stats'][0], $row['stats'][1])) continue;
    $teamStats[] = [
        'title'       => translateStat($row['title'] ?? ''),
        'home'        => $row['stats'][0],
        'away'        => $row['stats'][1],
        'isPercent'   => ($row['type'] ?? '') === 'graph',
        'highlighted' => $row['highlighted'] ?? 'equal',
    ];
}

/* ── Timeline de eventos (goles, cambios, tarjetas, tiempo añadido) ──── */
$timelineRaw = $matchDetails['content']['matchFacts']['events']['events'] ?? [];
$timeline = [];
foreach ($timelineRaw as $ev) {
    $type = $ev['type'] ?? '';
    $item = [
        'type'   => $type,
        'minute' => (string)($ev['timeStr'] ?? $ev['time'] ?? ''),
        'time'   => (int)($ev['time'] ?? 0),
        'isHome' => $ev['isHome'] ?? null,
    ];
    if ($type === 'Goal') {
        $item['player'] = $ev['fullName'] ?? $ev['nameStr'] ?? '';
        $item['assist']  = $ev['assistInput'] ?? null;
        $item['ownGoal'] = !empty($ev['ownGoal']);
    } elseif ($type === 'Substitution') {
        $item['playerOut'] = $ev['swap'][0]['name'] ?? '';
        $item['playerIn']  = $ev['swap'][1]['name'] ?? '';
    } elseif (in_array($type, ['YellowCard', 'RedCard', 'YellowRedCard'], true)) {
        $item['player'] = $ev['fullName'] ?? $ev['nameStr'] ?? ($ev['card']['name'] ?? '');
    } elseif ($type === 'AddedTime') {
        $item['minutesAdded'] = $ev['minutesAddedInput'] ?? null;
    } else {
        continue; // otros tipos (ej. Halftime marker) no aportan al timeline visible
    }
    $timeline[] = $item;
}

respond([
    'ok'          => true,
    'matchStatus' => $matchStatus,
    'standings'   => $standings,
    'h2h'         => $h2hMatches,
    'lineup'      => ($lineupHome && $lineupAway && !empty($lineupHome['starters']) && !empty($lineupAway['starters']))
        ? ['home' => $lineupHome, 'away' => $lineupAway]
        : null,
    'homeGoals'  => $homeGoals,
    'awayGoals'  => $awayGoals,
    'mvp'        => $mvp,
    'teamStats'  => $teamStats,
    'timeline'   => $timeline,
]);
