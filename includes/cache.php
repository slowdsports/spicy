<?php
/**
 * StreamHub - Regeneración de cachés JSON (data/*.json)
 *
 * Estas son las ÚNICAS funciones del sitio que deben consultar las tablas
 * que cachean. Se invocan solo cuando un admin guarda/borra el dato
 * correspondiente (admin/api/crud.php, admin/pages/config.php) o al pulsar
 * un botón "Actualizar JSON" (admin/api/generate_json.php). El resto de la
 * web (router público, navbar, canal.php, api/*.php) lee únicamente estos
 * archivos JSON y nunca toca la base de datos para estos datos.
 */

define('CACHE_DIR', __DIR__ . '/../data');

function _cacheWrite(string $file, $data): bool {
    if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);
    return file_put_contents(
        CACHE_DIR . '/' . $file,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    ) !== false;
}

/** config_sitio → data/config.json */
function regenerateSiteConfigCache(): bool {
    $conn = getDBConnection();
    $rows = $conn->query("SELECT clave, valor FROM config_sitio")->fetch_all(MYSQLI_ASSOC);

    $cfg = [];
    foreach ($rows as $r) { $cfg[$r['clave']] = $r['valor']; }

    return _cacheWrite('config.json', $cfg);
}

/** canales (+ categorias_canal) → data/channels.json */
function regenerateCanalesCache(): bool {
    $conn = getDBConnection();
    $canales = $conn->query("
        SELECT c.id, c.nombre, c.logo AS imagen, c.category AS categoria_id, c.views, c.activo
        FROM canales c ORDER BY c.nombre ASC
    ")->fetch_all(MYSQLI_ASSOC);

    $catMap = [];
    foreach ($conn->query("SELECT id, nombre FROM categorias_canal")->fetch_all(MYSQLI_ASSOC) as $cat) {
        $catMap[$cat['id']] = $cat['nombre'];
    }

    $json = array_map(fn($c) => [
        'id'          => (int)$c['id'],
        'name'        => $c['nombre'] ?? '',
        'category'    => $catMap[$c['categoria_id']] ?? (string)($c['categoria_id'] ?? ''),
        'logo'        => $c['imagen'] ?? '',
        'description' => '',
        'views'       => $c['views'] ?: '0',
        'active'      => (int)$c['activo'],
    ], $canales);

    return _cacheWrite('channels.json', $json);
}

/** fuentes → data/fuentes.json (nunca incluye url / ck_key / ck_keyid: ver api/stream.php) */
function regenerateFuentesCache(): bool {
    $conn = getDBConnection();
    $fuentes = $conn->query("
        SELECT f.id, f.nombre, f.canal, f.tipo, f.epg, f.activo, f.sandbox, f.mostrar_tv, f.solo_spicy,
               f.geo_bloqueado, p.paisNombre AS pais_nombre,
               (f.url_ios IS NOT NULL AND f.url_ios <> '') AS ios
        FROM fuentes f
        LEFT JOIN paises p ON f.pais = p.id
        ORDER BY f.nombre ASC
    ")->fetch_all(MYSQLI_ASSOC);

    $json = array_map(fn($f) => [
        'id'            => (int)$f['id'],
        'nombre'        => $f['nombre'] ?? '',
        'canal'         => (int)$f['canal'] ?: null,
        'tipo'          => (int)$f['tipo'] ?: null,
        'epg'           => $f['epg'] ?? '',
        'activo'        => (int)$f['activo'],
        'sandbox'       => (int)($f['sandbox'] ?? 1),
        'mostrar_tv'    => (int)($f['mostrar_tv'] ?? 1),
        'ios'           => (bool)$f['ios'],
        'solo_spicy'    => (bool)($f['solo_spicy'] ?? false),
        'geo_bloqueado' => (bool)($f['geo_bloqueado'] ?? false),
        'pais_nombre'   => $f['pais_nombre'] ?? '',
    ], $fuentes);

    return _cacheWrite('fuentes.json', $json);
}

/** partidos (+ joins equipos/ligas/fuentes/canales) → data/matches.json */
function regenerateMatchesCache(): bool {
    $conn = getDBConnection();

    // Auto-migración: esta función corre desde cron (cron/tdt_channels_sync.php,
    // etc.) y puede ser el primer punto de contacto con la BD tras un deploy —
    // no depende de que un admin haya abierto antes admin/?p=partidos (que es
    // donde también se auto-crea esta columna) para no romper el sitio entero.
    $colCheck = $conn->query("SHOW COLUMNS FROM partidos LIKE 'extra_min'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE partidos ADD COLUMN extra_min SMALLINT NOT NULL DEFAULT 0");
    }

    $partidos = $conn->query("
        SELECT
            p.id, p.fecha_hora, p.tipo, p.liga, p.extra_min,
            p.canal1,  p.canal2,  p.canal3,  p.canal4,  p.canal5,
            p.canal6,  p.canal7,  p.canal8,  p.canal9,  p.canal10,
            l.nombre AS equipo_local,    l.id AS id_local,
            v.nombre AS equipo_visitante, v.id AS id_visitante,
            li.ligaNombre AS nombre_liga,
            f1.nombre  AS fuente1_nombre,  f2.nombre  AS fuente2_nombre,
            f3.nombre  AS fuente3_nombre,  f4.nombre  AS fuente4_nombre,
            f5.nombre  AS fuente5_nombre,  f6.nombre  AS fuente6_nombre,
            f7.nombre  AS fuente7_nombre,  f8.nombre  AS fuente8_nombre,
            f9.nombre  AS fuente9_nombre,  f10.nombre AS fuente10_nombre,
            c1.logo  AS canal1_logo,  c2.logo  AS canal2_logo,
            c3.logo  AS canal3_logo,  c4.logo  AS canal4_logo,
            c5.logo  AS canal5_logo,  c6.logo  AS canal6_logo,
            c7.logo  AS canal7_logo,  c8.logo  AS canal8_logo,
            c9.logo  AS canal9_logo,  c10.logo AS canal10_logo
        FROM partidos p
        LEFT JOIN equipos l   ON p.`local`    = l.id
        LEFT JOIN equipos v   ON p.visitante  = v.id
        LEFT JOIN ligas   li  ON p.liga       = li.id
        LEFT JOIN fuentes f1  ON p.canal1  = f1.id
        LEFT JOIN fuentes f2  ON p.canal2  = f2.id
        LEFT JOIN fuentes f3  ON p.canal3  = f3.id
        LEFT JOIN fuentes f4  ON p.canal4  = f4.id
        LEFT JOIN fuentes f5  ON p.canal5  = f5.id
        LEFT JOIN fuentes f6  ON p.canal6  = f6.id
        LEFT JOIN fuentes f7  ON p.canal7  = f7.id
        LEFT JOIN fuentes f8  ON p.canal8  = f8.id
        LEFT JOIN fuentes f9  ON p.canal9  = f9.id
        LEFT JOIN fuentes f10 ON p.canal10 = f10.id
        LEFT JOIN canales c1  ON f1.canal  = c1.id
        LEFT JOIN canales c2  ON f2.canal  = c2.id
        LEFT JOIN canales c3  ON f3.canal  = c3.id
        LEFT JOIN canales c4  ON f4.canal  = c4.id
        LEFT JOIN canales c5  ON f5.canal  = c5.id
        LEFT JOIN canales c6  ON f6.canal  = c6.id
        LEFT JOIN canales c7  ON f7.canal  = c7.id
        LEFT JOIN canales c8  ON f8.canal  = c8.id
        LEFT JOIN canales c9  ON f9.canal  = c9.id
        LEFT JOIN canales c10 ON f10.canal = c10.id
        ORDER BY p.fecha_hora ASC
        LIMIT 500
    ")->fetch_all(MYSQLI_ASSOC);

    $now = time();
    $tz  = new DateTimeZone('America/Tegucigalpa');
    $jsonMatches = [];

    foreach ($partidos as $p) {
        $dt = !empty($p['fecha_hora']) ? new DateTime($p['fecha_hora'], $tz) : null;
        $ts = $dt ? $dt->getTimestamp() : 0;

        // Ventana "en vivo": 3h normales + minutos extra que el admin haya
        // agregado a este partido puntual (prórroga, retrasos — ver
        // admin/pages/partidos.php). extra_min también viaja en el JSON para
        // que el countdown client-side (assets/js/main.js) use la misma ventana.
        $extraMin      = (int)($p['extra_min'] ?? 0);
        $liveWindowSec = 10800 + ($extraMin * 60);

        if (!$ts || $ts > $now)         { $status = 'upcoming'; $timeTxt = $dt ? $dt->format('H:i') : '--:--'; }
        elseif ($ts > $now - $liveWindowSec) { $status = 'live';     $timeTxt = 'EN VIVO'; }
        else                             { $status = 'finished'; $timeTxt = 'Finalizó'; }

        $match = [
            'id'         => (int)$p['id'],
            'league'     => $p['liga'] ?? '',
            'leagueName' => $p['nombre_liga'] ?? '',
            'leagueLogo' => 'assets/img/ligas/' . logoFolder($p['liga'] ?? 0) . '/' . ($p['liga'] ?? '') . '.png',
            'status'     => $status,
            'time'       => $timeTxt,
            'fecha_hora' => $p['fecha_hora'] ?? '',
            'timestamp'  => $ts,
            'extraMin'   => $extraMin,
            'tipo'       => $p['tipo'] ?? '',
            'homeTeam'   => ['name' => $p['equipo_local'] ?? '',     'logo' => $p['id_local'] ?? '',     'score' => 0],
            'awayTeam'   => ['name' => $p['equipo_visitante'] ?? '', 'logo' => $p['id_visitante'] ?? '', 'score' => 0],
        ];

        for ($i = 1; $i <= 10; $i++) {
            $match["cnl{$i}"]     = $p["canal{$i}"] ?? '';
            $match["cnl{$i}Name"] = $p["fuente{$i}_nombre"] ?? '';
            $match["cnl{$i}Logo"] = $p["canal{$i}_logo"] ?? '';
        }

        $jsonMatches[] = $match;
    }

    usort($jsonMatches, function ($a, $b) use ($now) {
        $aUp = $a['timestamp'] >= $now;
        $bUp = $b['timestamp'] >= $now;
        if ($aUp !== $bUp) return $aUp ? -1 : 1;
        if ($aUp) return $a['timestamp'] - $b['timestamp'];
        return $b['timestamp'] - $a['timestamp'];
    });

    return _cacheWrite('matches.json', $jsonMatches);
}

/**
 * partidos_destacados (+ joins equipos/ligas) → data/destacados.json
 * Guarda los datos crudos (fecha_hora incluida); el status "live/upcoming/
 * finished" se calcula en api/destacados.php a partir de fecha_hora en cada
 * petición, así nunca queda desactualizado aunque el caché no se regenere.
 */
function regenerateDestacadosCache(): bool {
    $conn = getDBConnection();
    $rows = $conn->query("
        SELECT partido_id, posicion
        FROM partidos_destacados
        WHERE activo = 1
        ORDER BY posicion ASC, id ASC
    ")->fetch_all(MYSQLI_ASSOC);

    if (empty($rows)) {
        return _cacheWrite('destacados.json', []);
    }

    $ordenByPid = [];
    $ids = [];
    foreach ($rows as $r) {
        $pid = (int)$r['partido_id'];
        $ids[] = $pid;
        $ordenByPid[$pid] = (int)$r['posicion'];
    }

    $placeholders = implode(',', $ids); // ya son enteros (vienen de la BD), seguro para interpolar
    $partidos = $conn->query("
        SELECT
            p.id, p.fecha_hora, p.tipo, p.liga,
            loc.nombre  AS equipo_local,    loc.id AS id_local,
            vis.nombre  AS equipo_visitante, vis.id AS id_visitante,
            li.ligaNombre AS leagueName
        FROM partidos p
        LEFT JOIN equipos loc ON p.`local`    = loc.id
        LEFT JOIN equipos vis ON p.visitante  = vis.id
        LEFT JOIN ligas   li  ON p.liga       = li.id
        WHERE p.id IN ($placeholders)
    ")->fetch_all(MYSQLI_ASSOC);

    $result = [];
    foreach ($partidos as $p) {
        $pid = (int)$p['id'];
        $result[] = [
            'id'         => $pid,
            'league'     => $p['liga'] ?? '',
            'leagueName' => $p['leagueName'] ?? '',
            'fecha_hora' => $p['fecha_hora'] ?? '',
            'tipo'       => $p['tipo'] ?? '',
            'homeTeam'   => ['name' => $p['equipo_local']     ?? '', 'logo' => $p['id_local']     ?? '', 'score' => 0],
            'awayTeam'   => ['name' => $p['equipo_visitante'] ?? '', 'logo' => $p['id_visitante'] ?? '', 'score' => 0],
            '_posicion'  => $ordenByPid[$pid] ?? 99,
        ];
    }

    usort($result, fn($a, $b) => $a['_posicion'] <=> $b['_posicion']);
    foreach ($result as &$r) unset($r['_posicion']);
    unset($r);

    return _cacheWrite('destacados.json', $result);
}
