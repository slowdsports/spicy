<?php
/**
 * API pública — Partidos destacados del día.
 * Lee data/destacados.json (cacheado por includes/cache.php cuando un admin
 * guarda/activa un destacado); nunca consulta la base de datos. El status
 * (live/upcoming/finished) se recalcula aquí desde fecha_hora en cada
 * petición, así nunca queda desactualizado aunque el caché no se regenere.
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

try {
    $path   = __DIR__ . '/../data/destacados.json';
    $cached = file_exists($path) ? (json_decode(file_get_contents($path), true) ?? []) : [];

    $now = time();
    $tz  = new DateTimeZone('America/Tegucigalpa');
    $result = [];

    foreach ($cached as $p) {
        $dt = !empty($p['fecha_hora']) ? new DateTime($p['fecha_hora'], $tz) : null;
        $ts = $dt ? $dt->getTimestamp() : 0;

        if (!$ts || $ts > $now)     { $status = 'upcoming'; $time = $dt ? $dt->format('H:i') : '--:--'; }
        elseif ($ts > $now - 10800) { $status = 'live';     $time = 'EN VIVO'; }
        else                        { $status = 'finished'; $time = 'Finalizó'; }

        $result[] = [
            'id'         => $p['id'] ?? 0,
            'league'     => $p['league'] ?? '',
            'leagueName' => $p['leagueName'] ?? '',
            'leagueLogo' => BASE_URL . 'assets/img/ligas/' . logoFolder($p['league'] ?? 0) . '/' . ($p['league'] ?? '') . '.png',
            'status'     => $status,
            'time'       => $time,
            'fecha_hora' => $p['fecha_hora'] ?? '',
            'timestamp'  => $ts,
            'tipo'       => $p['tipo'] ?? '',
            'homeTeam'   => $p['homeTeam'] ?? ['name' => '', 'logo' => '', 'score' => 0],
            'awayTeam'   => $p['awayTeam'] ?? ['name' => '', 'logo' => '', 'score' => 0],
        ];
    }

    echo json_encode(['ok' => true, 'matches' => $result], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'matches' => [], 'error' => $e->getMessage()]);
}
