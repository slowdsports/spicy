<?php
/**
 * Admin API — Generación manual de JSON estático
 * POST { entity: 'canales'|'fuentes'|'partidos' }
 */
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true);
$entity = $input['entity'] ?? '';

$dir = __DIR__ . '/../../data';
if (!is_dir($dir)) mkdir($dir, 0755, true);

try {
    $conn = getDBConnection();
    $conn->set_charset('utf8mb4');

    switch ($entity) {

        // ── CANALES → channels.json ────────────────────────────
        case 'canales':
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

            file_put_contents(
                $dir . '/channels.json',
                json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
            echo json_encode(['success' => true, 'message' => 'channels.json actualizado (' . count($json) . ' canales)']);
            break;

        // ── FUENTES → fuentes.json ─────────────────────────────
        case 'fuentes':
            $fuentes = $conn->query("
                SELECT id, nombre, canal, tipo, epg, activo, sandbox, mostrar_tv
                FROM fuentes ORDER BY nombre ASC
            ")->fetch_all(MYSQLI_ASSOC);

            $json = array_map(fn($f) => [
                'id'         => (int)$f['id'],
                'nombre'     => $f['nombre'] ?? '',
                'canal'      => (int)$f['canal'] ?: null,
                'tipo'       => (int)$f['tipo'] ?: null,
                'epg'        => $f['epg'] ?? '',
                'activo'     => (int)$f['activo'],
                'sandbox'    => (int)($f['sandbox'] ?? 1),
                'mostrar_tv' => (int)($f['mostrar_tv'] ?? 1),
            ], $fuentes);

            file_put_contents(
                $dir . '/fuentes.json',
                json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
            echo json_encode(['success' => true, 'message' => 'fuentes.json actualizado (' . count($json) . ' fuentes)']);
            break;

        // ── PARTIDOS → matches.json ────────────────────────────
        case 'partidos':
            $partidos = $conn->query("
                SELECT
                    p.id, p.fecha_hora, p.tipo, p.liga,
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

                if (!$ts || $ts > $now)         { $status = 'upcoming'; $timeTxt = $dt ? $dt->format('H:i') : '--:--'; }
                elseif ($ts > $now - 10800)     { $status = 'live';     $timeTxt = 'EN VIVO'; }
                else                            { $status = 'finished'; $timeTxt = 'Finalizó'; }

                $match = [
                    'id'         => (int)$p['id'],
                    'league'     => $p['liga'] ?? '',
                    'leagueName' => $p['nombre_liga'] ?? '',
                    'leagueLogo' => BASE_URL . 'assets/img/ligas/sf/' . ($p['liga'] ?? '') . '.png',
                    'status'     => $status,
                    'time'       => $timeTxt,
                    'fecha_hora' => $p['fecha_hora'] ?? '',
                    'timestamp'  => $ts,
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

            file_put_contents(
                $dir . '/matches.json',
                json_encode($jsonMatches, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
            echo json_encode(['success' => true, 'message' => 'matches.json actualizado (' . count($jsonMatches) . ' partidos)']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Entidad no válida: ' . htmlspecialchars($entity)]);
    }

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
