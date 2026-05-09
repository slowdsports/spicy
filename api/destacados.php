<?php
/**
 * API pública — Partidos destacados del día.
 * Devuelve los partidos marcados como activos en partidos_destacados,
 * enriquecidos con los datos de matches.json.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

try {
    $db = getDBConnection();

    $rows = $db->query("
        SELECT partido_id
        FROM partidos_destacados
        WHERE activo = 1
        ORDER BY posicion ASC, id ASC
    ");

    if (!$rows) {
        echo json_encode(['ok' => true, 'matches' => []]);
        exit;
    }

    $featuredIds = array_column($rows->fetch_all(MYSQLI_ASSOC), 'partido_id');

    if (empty($featuredIds)) {
        echo json_encode(['ok' => true, 'matches' => []]);
        exit;
    }

    $jsonPath = __DIR__ . '/../data/matches.json';
    $allMatches = file_exists($jsonPath)
        ? (json_decode(file_get_contents($jsonPath), true) ?? [])
        : [];

    // Indexar posición para mantener el orden definido en el admin
    $posById = array_flip($featuredIds);
    $featured = [];

    foreach ($allMatches as $m) {
        $mid = (int)($m['id'] ?? 0);
        if (isset($posById[$mid])) {
            $featured[$posById[$mid]] = $m;
        }
    }

    ksort($featured);

    echo json_encode(['ok' => true, 'matches' => array_values($featured)], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'matches' => []]);
}
