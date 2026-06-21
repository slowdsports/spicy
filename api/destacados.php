<?php
/**
 * API pública — Partidos destacados del día.
 * Consulta la BD directamente: no depende del estado de matches.json.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

try {
    $db = getDBConnection();

    // 1. IDs activos ordenados por posición
    $rows = $db->query("
        SELECT partido_id, posicion
        FROM partidos_destacados
        WHERE activo = 1
        ORDER BY posicion ASC, id ASC
    ");

    if (!$rows || $rows->num_rows === 0) {
        echo json_encode(['ok' => true, 'matches' => []]);
        exit;
    }

    $featured   = $rows->fetch_all(MYSQLI_ASSOC);
    $ordenByPid = [];
    $ids        = [];
    foreach ($featured as $f) {
        $pid = (int)$f['partido_id'];
        $ids[] = $pid;
        $ordenByPid[$pid] = (int)$f['posicion'];
    }

    if (empty($ids)) {
        echo json_encode(['ok' => true, 'matches' => []]);
        exit;
    }

    // 2. Consultar partidos desde la BD (sin depends de matches.json)
    $placeholders = implode(',', $ids);   // ya son enteros, safe
    $partidos = $db->query("
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

    // 3. Calcular status y construir respuesta
    $now = time();
    $tz  = new DateTimeZone('America/Tegucigalpa');
    $result = [];

    foreach ($partidos as $p) {
        $dt = !empty($p['fecha_hora']) ? new DateTime($p['fecha_hora'], $tz) : null;
        $ts = $dt ? $dt->getTimestamp() : 0;

        if (!$ts || $ts > $now)     { $status = 'upcoming'; $time = $dt ? $dt->format('H:i') : '--:--'; }
        elseif ($ts > $now - 10800) { $status = 'live';     $time = 'EN VIVO'; }
        else                        { $status = 'finished'; $time = 'Finalizó'; }

        $pid = (int)$p['id'];
        $result[] = [
            'id'         => $pid,
            'league'     => $p['liga'] ?? '',
            'leagueName' => $p['leagueName'] ?? '',
            'leagueLogo' => BASE_URL . 'assets/img/ligas/' . logoFolder($p['liga'] ?? 0) . '/' . ($p['liga'] ?? '') . '.png',
            'status'     => $status,
            'time'       => $time,
            'fecha_hora' => $p['fecha_hora'] ?? '',
            'timestamp'  => $ts,
            'tipo'       => $p['tipo'] ?? '',
            'homeTeam'   => ['name' => $p['equipo_local']     ?? '', 'logo' => $p['id_local']     ?? '', 'score' => 0],
            'awayTeam'   => ['name' => $p['equipo_visitante'] ?? '', 'logo' => $p['id_visitante'] ?? '', 'score' => 0],
            '_posicion'  => $ordenByPid[$pid] ?? 99,
        ];
    }

    // Ordenar por posición definida en el admin
    usort($result, fn($a, $b) => $a['_posicion'] <=> $b['_posicion']);

    // Limpiar campo interno antes de enviar
    foreach ($result as &$r) unset($r['_posicion']);
    unset($r);

    echo json_encode(['ok' => true, 'matches' => $result], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'matches' => [], 'error' => $e->getMessage()]);
}
