<?php
/**
 * StreamHub - API para obtener partidos desde BD o JSON
 * GET /api/partidos.php?tipo=soccer
 * GET /api/partidos.php?tipo=soccer&liga=17
 *
 * En producción: lee de MySQL y une con tablas de equipos, ligas y canales.
 * En modo demo:  lee del JSON en /data/soccer.json
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$tipo  = isset($_GET['tipo'])  ? preg_replace('/[^a-z]/', '', strtolower($_GET['tipo'])) : 'soccer';
$liga  = isset($_GET['liga'])  ? (int)$_GET['liga']  : null;

// ----------------------------------------------------------------
// Intentar leer desde MySQL
// En producción esta sección reemplaza completamente al fallback JSON
// ----------------------------------------------------------------
/*
try {
    $conn = getDBConnection();

    $sql = "
        SELECT
            p.*,
            e_local.nombre    AS local_nombre,
            e_local.logo      AS local_logo,
            e_visit.nombre    AS visitante_nombre,
            e_visit.logo      AS visitante_logo,
            l.ligaNombre      AS liga_nombre,
            l.ligaImg         AS liga_logo,
            l.ligaPais        AS liga_pais
        FROM partidos p
        LEFT JOIN equipos  e_local ON p.local     = e_local.id
        LEFT JOIN equipos  e_visit ON p.visitante  = e_visit.id
        LEFT JOIN ligas    l       ON p.liga        = l.id
        WHERE p.tipo = ?
    ";
    $params = [$tipo];
    $types  = 's';

    if ($liga) {
        $sql    .= " AND p.liga = ?";
        $params[] = $liga;
        $types   .= 'i';
    }

    $sql .= " ORDER BY p.fecha_hora ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Transformar al formato esperado por el frontend
    $result = array_map(function($row) {
        // Recuperar canales disponibles
        $canalesInfo = [];
        for ($i = 1; $i <= 10; $i++) {
            $cid = $row["canal{$i}"] ?? null;
            if ($cid) {
                // En producción: hacer JOIN con tabla canales
                $canalesInfo[$cid] = ['id' => $cid, 'nombre' => "Canal {$cid}", 'logo' => ''];
            }
        }
        return array_merge($row, [
            'liga_info'       => ['id' => $row['liga'], 'nombre' => $row['liga_nombre'], 'logo' => $row['liga_logo'], 'pais' => $row['liga_pais']],
            'local_info'      => ['id' => $row['local'],    'nombre' => $row['local_nombre'],    'logo' => $row['local_logo']],
            'visitante_info'  => ['id' => $row['visitante'],'nombre' => $row['visitante_nombre'],'logo' => $row['visitante_logo']],
            'canales_info'    => $canalesInfo,
        ]);
    }, $rows);

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();

} catch (Exception $e) {
    // Si falla la BD, caer al JSON de demo
}
*/

// ----------------------------------------------------------------
// FALLBACK: leer del JSON estático
// ----------------------------------------------------------------
$jsonFile = __DIR__ . "/../data/{$tipo}.json";

if (!file_exists($jsonFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'Tipo de deporte no encontrado']);
    exit();
}

$data = json_decode(file_get_contents($jsonFile), true) ?? [];

// Filtrar por tipo
$data = array_values(array_filter($data, fn($p) => ($p['tipo'] ?? '') === $tipo));

// Filtrar por liga si se especificó
if ($liga) {
    $data = array_values(array_filter($data, fn($p) => (int)($p['liga'] ?? 0) === $liga));
}

// Ordenar por fecha
usort($data, fn($a, $b) => strtotime($a['fecha_hora']) - strtotime($b['fecha_hora']));

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
