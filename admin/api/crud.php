<?php
/**
 * StreamHub Admin - API CRUD
 * Maneja todas las operaciones de creación, edición y borrado
 * via peticiones AJAX (JSON) desde el panel.
 *
 * GET  ?action=get_partido&id=X      → Devuelve un partido
 * POST { action, entity, data }      → Crear o actualizar
 * POST { action:'delete', entity, id }→ Borrar
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

// ── Guardia: solo admins ──────────────────────────────────────
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

// ── Helpers para importación manual de JSON de Sofascore ──────
function sofaDownloadFile(string $url, string $dest): void
{
    if (file_exists($dest)) return;
    $bin = @file_get_contents($url);
    if ($bin) @file_put_contents($dest, $bin);
}

function sofaAsignarCanalesDefecto(int $ligaId, string $sport): array
{
    $c = [
        'starp' => null, 'vix' => null,
        'canal1'=> null, 'canal2'=> null, 'canal3'=> null, 'canal4'=> null,
        'canal5'=> null, 'canal6'=> null, 'canal7'=> null
    ];

    if ($sport === 'tennis') {
        $c['canal1']=49; $c['canal2']=90; $c['canal3']=314; $c['canal4']=315;
        $c['canal5']=144; $c['canal6']=145; $c['canal7']=146; $c['starp']=1;
        return $c;
    }

    switch ($ligaId) {
        case 8:
            $c['canal3']=314;
            break;
        case 54:
            $c['canal1']=32; $c['canal2']=33; $c['canal3']=34; $c['canal4']=35; $c['canal5']=36;
            break;
        case 7:
        case 679:
            $c['starp']=1; $c['vix']=1;
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

// ── GET: consultas de lectura ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'get_partido') {
        $id   = (int)($_GET['id'] ?? 0);
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM partidos WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        echo json_encode(['success' => (bool)$row, 'data' => $row]);
        exit();
    }

    // ── Partidos destacados: lista con datos de matches.json ──────
    if ($action === 'get_destacados') {
        $conn = getDBConnection();
        $rows = $conn->query("
            SELECT id, partido_id, posicion, activo
            FROM partidos_destacados
            ORDER BY posicion ASC, id ASC
        ")->fetch_all(MYSQLI_ASSOC);

        $jsonPath = __DIR__ . '/../../data/matches.json';
        $matches  = file_exists($jsonPath)
            ? (json_decode(file_get_contents($jsonPath), true) ?? [])
            : [];

        $byId = [];
        foreach ($matches as $m) { $byId[(int)$m['id']] = $m; }

        foreach ($rows as &$row) {
            $m = $byId[(int)$row['partido_id']] ?? [];
            $row['homeTeam']   = $m['homeTeam']['name'] ?? '—';
            $row['awayTeam']   = $m['awayTeam']['name'] ?? '—';
            $row['leagueName'] = $m['leagueName'] ?? '—';
            $row['fecha_hora'] = $m['fecha_hora'] ?? '';
            $row['tipo']       = $m['tipo'] ?? '';
            $row['liga']       = $m['league'] ?? '';
        }
        unset($row);

        echo json_encode(['success' => true, 'data' => $rows]);
        exit();
    }

    // ── Selector de partidos (para el modal de agregar) ───────────
    if ($action === 'get_partidos_select') {
        $jsonPath = __DIR__ . '/../../data/matches.json';
        $matches  = file_exists($jsonPath)
            ? (json_decode(file_get_contents($jsonPath), true) ?? [])
            : [];

        $list = [];
        foreach ($matches as $m) {
            $list[] = [
                'id'          => (int)$m['id'],
                'homeTeam'    => $m['homeTeam']['name'] ?? '',
                'awayTeam'    => $m['awayTeam']['name'] ?? '',
                'leagueName'  => $m['leagueName'] ?? '',
                'fecha_hora'  => $m['fecha_hora'] ?? '',
                'tipo'        => $m['tipo'] ?? '',
                'liga'        => $m['league'] ?? '',
            ];
        }

        echo json_encode(['success' => true, 'data' => $list]);
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Acción GET desconocida']);
    exit();
}

// ── POST: crear, editar, borrar ───────────────────────────────
$input  = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$entity = $input['entity'] ?? '';

try {
    $conn = getDBConnection();

    // ── BORRAR ────────────────────────────────────────────────
    if ($action === 'delete') {
        $id    = (int)($input['id'] ?? 0);
        $table = match ($entity) {
            'canal'      => 'canales',
            'fuente'     => 'fuentes',
            'liga'       => 'ligas',
            'partido'    => 'partidos',
            'destacado'  => 'partidos_destacados',
            'proxy'      => 'proxies',
            default      => null,
        };

        if (!$table || !$id) { resp(false, 'Entidad o ID inválido'); }

        $stmt = $conn->prepare("DELETE FROM `{$table}` WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();

        resp($ok, $ok ? 'Eliminado correctamente' : 'Error al eliminar');
    }

    // ── GUARDAR CANAL (crear o editar) ────────────────────────
    if ($action === 'save' && $entity === 'canal') {
        $d = $input['data'] ?? [];

        $id        = (int)($d['id']        ?? 0);
        $nombre    = trim($d['nombre']     ?? '');
        $imagen    = trim($d['imagen']     ?? '');
        $categoria = (int)($d['categoria'] ?? 0);
        $activo    = (int)($d['activo']    ?? 1);

        if (!$nombre || !$categoria) { resp(false, 'Nombre y categoría son obligatorios'); }

        if ($id) {
            $stmt = $conn->prepare("UPDATE canales SET nombre=?, logo=?, category=?, activo=? WHERE id=?");
            $stmt->bind_param('ssiii', $nombre, $imagen, $categoria, $activo, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO canales (nombre, logo, category, activo) VALUES (?,?,?,?)");
            $stmt->bind_param('ssii', $nombre, $imagen, $categoria, $activo);
        }

        $ok = $stmt->execute();
        $stmt->close();
        resp($ok, $ok ? 'Canal guardado' : 'Error al guardar');
    }

    // ── GUARDAR FUENTE (crear o editar) ───────────────────────
    if ($action === 'save' && $entity === 'fuente') {
        $d = $input['data'] ?? [];

        $id          = (int)($d['id']          ?? 0);
        $nombre      = trim($d['nombre']       ?? '');
        $canal       = (int)($d['canal']       ?? 0);
        $url         = trim($d['url']          ?? '');
        $urlIos      = trim($d['url_ios']      ?? '') ?: null;
        $allowed_ios = ['hls', 'iframe'];
        $tipoIos     = in_array($d['tipo_ios'] ?? '', $allowed_ios) ? $d['tipo_ios'] : 'hls';
        $ckKey       = trim($d['ck_key']       ?? '') ?: null;
        $ckKeyId     = trim($d['ck_keyid']     ?? '') ?: null;
        $pais        = $d['pais'] ? (int)$d['pais'] : null;
        $tipo        = (int)($d['tipo']        ?? 0);
        $epg         = trim($d['epg']          ?? '') ?: null;
        $activo      = (int)($d['activo']      ?? 1);
        $sandbox     = (int)($d['sandbox']     ?? 1);
        $mostrar_tv  = (int)($d['mostrar_tv']  ?? 1);
        $usar_proxy  = (int)($d['usar_proxy']  ?? 0);
        $solo_spicy  = (int)($d['solo_spicy']  ?? 0);
        $allowed     = ['bitmovin', 'clappr', 'jwplayer'];
        $reproductor = in_array($d['reproductor'] ?? '', $allowed) ? $d['reproductor'] : 'bitmovin';

        if (!$nombre || !$canal || !$url || !$tipo) { resp(false, 'Nombre, canal, URL y tipo son obligatorios'); }

        if ($id) {
            $stmt = $conn->prepare("UPDATE fuentes SET nombre=?, canal=?, url=?, url_ios=?, tipo_ios=?, ck_key=?, ck_keyid=?, pais=?, tipo=?, epg=?, activo=?, sandbox=?, mostrar_tv=?, reproductor=?, usar_proxy=?, solo_spicy=? WHERE id=?");
            $stmt->bind_param('sissssssissiisiii', $nombre, $canal, $url, $urlIos, $tipoIos, $ckKey, $ckKeyId, $pais, $tipo, $epg, $activo, $sandbox, $mostrar_tv, $reproductor, $usar_proxy, $solo_spicy, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO fuentes (nombre, canal, url, url_ios, tipo_ios, ck_key, ck_keyid, pais, tipo, epg, activo, sandbox, mostrar_tv, reproductor, usar_proxy, solo_spicy) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sissssssissiisii', $nombre, $canal, $url, $urlIos, $tipoIos, $ckKey, $ckKeyId, $pais, $tipo, $epg, $activo, $sandbox, $mostrar_tv, $reproductor, $usar_proxy, $solo_spicy);
        }

        $ok = $stmt->execute();
        $stmt->close();
        resp($ok, $ok ? 'Fuente guardada' : 'Error al guardar');
    }

    // ── GUARDAR LIGA (manual, sin sofascore) ──────────────────
    if ($action === 'save' && $entity === 'liga') {
        $d = $input['data'] ?? [];

        $id     = (int)($d['id']     ?? 0);  // ID manual (se permite editar el existente)
        $sfId   = (int)($d['sf_id']  ?? $id); // ID Sofascore = PK
        $nombre = trim($d['nombre']  ?? '');
        $pais   = trim($d['pais']    ?? '');
        $tipo   = preg_replace('/[^a-z]/', '', $d['tipo'] ?? 'soccer');

        if (!$sfId || !$nombre) { resp(false, 'ID y nombre son obligatorios'); }

        // Upsert: INSERT si no existe, UPDATE si ya existe
        $stmt = $conn->prepare("
            INSERT INTO ligas (id, ligaNombre, ligaPais, tipo)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE ligaNombre=VALUES(ligaNombre), ligaPais=VALUES(ligaPais), tipo=VALUES(tipo)
        ");
        $stmt->bind_param('isss', $sfId, $nombre, $pais, $tipo);
        $ok = $stmt->execute();
        $stmt->close();
        resp($ok, $ok ? 'Liga guardada' : 'Error al guardar');
    }

    // ── GUARDAR PARTIDO DESTACADO ─────────────────────────────
    if ($action === 'save' && $entity === 'destacado') {
        $d          = $input['data'] ?? [];
        $partido_id = (int)($d['partido_id'] ?? 0);
        $posicion   = (int)($d['posicion']   ?? 0);

        if (!$partido_id) { resp(false, 'ID de partido inválido'); }

        $stmt = $conn->prepare("
            INSERT INTO partidos_destacados (partido_id, posicion, activo)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE posicion = VALUES(posicion), activo = 1
        ");
        $stmt->bind_param('ii', $partido_id, $posicion);
        $ok = $stmt->execute();
        $stmt->close();
        resp($ok, $ok ? 'Partido destacado guardado' : 'Error al guardar');
    }

    // ── TOGGLE ACTIVO DESTACADO ───────────────────────────────
    if ($action === 'toggle' && $entity === 'destacado') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) { resp(false, 'ID inválido'); }

        $stmt = $conn->prepare("UPDATE partidos_destacados SET activo = IF(activo=1,0,1) WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        resp($ok, $ok ? 'Estado actualizado' : 'Error al actualizar');
    }

    // ── IMPORTAR PARTIDOS DESDE JSON PEGADO MANUALMENTE ───────
    // (workaround mientras Sofascore bloquee las peticiones del servidor)
    if ($action === 'importar_json_partidos') {
        $rawJson = trim($input['json'] ?? '');

        if ($rawJson === '') {
            resp(false, 'Pega el JSON de Sofascore primero');
        }

        $data = json_decode($rawJson, true);

        if ($data === null) {
            resp(false, 'JSON inválido: ' . json_last_error_msg());
        }

        // Acepta {"events":[...]} o un arreglo de eventos suelto
        $events = $data['events'] ?? (array_is_list($data ?? []) ? $data : null);

        if (!is_array($events) || empty($events)) {
            resp(false, "El JSON no contiene un arreglo 'events' con partidos");
        }

        $ligaImgDir   = __DIR__ . '/../../assets/img/ligas/sf/';
        $ligaDarkDir  = __DIR__ . '/../../assets/img/ligas/sf/dark/';
        $equipoImgDir = __DIR__ . '/../../assets/img/equipos/sf/';

        foreach ([$ligaImgDir, $ligaDarkDir, $equipoImgDir] as $dir) {
            if (!is_dir($dir)) mkdir($dir, 0755, true);
        }

        $fuentesValidas = array_flip(array_column(
            $conn->query("SELECT id FROM fuentes")->fetch_all(MYSQLI_ASSOC),
            'id'
        ));

        $agregados   = 0;
        $omitidos    = 0;
        $ligasVistas = [];

        date_default_timezone_set('America/Tegucigalpa');

        foreach ($events as $event) {

            $ligaId = (int)($event['tournament']['uniqueTournament']['id'] ?? 0);

            if (!$ligaId || empty($event['id']) || empty($event['homeTeam']['id']) || empty($event['awayTeam']['id'])) {
                $omitidos++;
                continue;
            }

            $countryCode = $event['tournament']['uniqueTournament']['category']['slug'] ?? 'international';
            $countryName = $event['tournament']['uniqueTournament']['category']['name'] ?? 'International';

            $stmt = $conn->prepare("INSERT IGNORE INTO paises (paisCodigo, paisNombre) VALUES (?, ?)");
            $stmt->bind_param("ss", $countryCode, $countryName);
            $stmt->execute();
            $stmt->close();

            $ligaName = $event['tournament']['uniqueTournament']['name'] ?? ($event['tournament']['name'] ?? '');
            $ligaSlug = $event['tournament']['slug'] ?? '';
            $sport    = $event['tournament']['uniqueTournament']['category']['sport']['slug'] ?? 'football';
            $seasonId = (int)($event['season']['id'] ?? 0);

            $ligasVistas[$ligaId] = $ligaName;

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
                $stmt->bind_param("issssi", $ligaId, $ligaName, $ligaSlug, $countryCode, $sport, $seasonId);
                $stmt->execute();
                $stmt->close();
            }

            sofaDownloadFile("https://api.sofascore.app/api/v1/unique-tournament/{$ligaId}/image", $ligaImgDir . $ligaId . ".png");
            sofaDownloadFile("https://api.sofascore.app/api/v1/unique-tournament/{$ligaId}/image/dark", $ligaDarkDir . $ligaId . ".png");

            $homeId   = (int)$event['homeTeam']['id'];
            $homeName = $event['homeTeam']['name'] ?? '';
            $awayId   = (int)$event['awayTeam']['id'];
            $awayName = $event['awayTeam']['name'] ?? '';

            foreach ([[$homeId, $homeName], [$awayId, $awayName]] as [$tId, $tName]) {
                $stmt = $conn->prepare("SELECT id FROM equipos WHERE id=?");
                $stmt->bind_param("i", $tId);
                $stmt->execute();
                $existeEquipo = $stmt->get_result()->num_rows > 0;
                $stmt->close();

                if (!$existeEquipo) {
                    $logo = "assets/img/equipos/sf/{$tId}.png";
                    $stmt = $conn->prepare("INSERT INTO equipos (id, nombre, logo, pais) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $tId, $tName, $logo, $countryCode);
                    $stmt->execute();
                    $stmt->close();
                }

                sofaDownloadFile("https://api.sofascore.app/api/v1/team/{$tId}/image", $equipoImgDir . $tId . ".png");
            }

            $gameId = (int)$event['id'];

            $stmt = $conn->prepare("SELECT id FROM partidos WHERE id=?");
            $stmt->bind_param("i", $gameId);
            $stmt->execute();
            $existePartido = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if (!$existePartido) {
                $fecha   = date('Y-m-d H:i:s', $event['startTimestamp']);
                $canales = sofaAsignarCanalesDefecto($ligaId, $sport);

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
                    $gameId, $homeId, $awayId, $ligaId, $fecha, $sport,
                    $canales['starp'], $canales['vix'],
                    $canales['canal1'], $canales['canal2'], $canales['canal3'],
                    $canales['canal4'], $canales['canal5'], $canales['canal6'], $canales['canal7']
                );
                $stmt->execute();
                $stmt->close();

                $agregados++;
            }
        }

        $nombres = implode(', ', array_filter($ligasVistas));
        $msg = $agregados > 0
            ? "Se agregaron {$agregados} partidos" . ($nombres ? " ({$nombres})" : '')
            : "No se agregaron partidos nuevos" . ($nombres ? " ({$nombres})" : '');
        if ($omitidos > 0) $msg .= " — {$omitidos} eventos omitidos por datos incompletos";

        resp(true, $msg);
    }

    // ── BORRAR PARTIDOS ANTIGUOS (fecha_hora a más de N días) ─
    if ($action === 'delete_old_partidos') {
        $dias = max(1, (int)($input['dias'] ?? 5));

        // Primero los destacados que apunten a esos partidos (sin FK, hay que limpiarlos a mano)
        $stmt = $conn->prepare("
            DELETE FROM partidos_destacados
            WHERE partido_id IN (
                SELECT id FROM partidos
                WHERE fecha_hora IS NOT NULL AND fecha_hora < (NOW() - INTERVAL ? DAY)
            )
        ");
        $stmt->bind_param('i', $dias);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("
            DELETE FROM partidos
            WHERE fecha_hora IS NOT NULL AND fecha_hora < (NOW() - INTERVAL ? DAY)
        ");
        $stmt->bind_param('i', $dias);
        $ok       = $stmt->execute();
        $borrados = $stmt->affected_rows;
        $stmt->close();

        resp($ok, $ok ? "Se eliminaron {$borrados} partidos con más de {$dias} días" : 'Error al eliminar');
    }

    // ── GUARDAR CANALES DE UN PARTIDO ─────────────────────────
    if ($action === 'save' && $entity === 'partido') {
        $d   = $input['data'] ?? [];
        $id  = (int)($d['id'] ?? 0);

        if (!$id) { resp(false, 'ID de partido inválido'); }

        // Construir update de canal1..canal10
        $sets   = [];
        $params = [];
        $types  = '';

        for ($i = 1; $i <= 10; $i++) {
            $sets[]   = "canal{$i} = ?";
            $val      = isset($d["canal{$i}"]) && $d["canal{$i}"] !== '' ? (int)$d["canal{$i}"] : null;
            $params[] = $val;
            $types   .= 'i';
        }

        $params[] = $id;
        $types   .= 'i';

        $stmt = $conn->prepare("UPDATE partidos SET " . implode(', ', $sets) . " WHERE id = ?");
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $stmt->close();
        resp($ok, $ok ? 'Partido actualizado' : 'Error al actualizar');
    }

    // ── GUARDAR PROXY (crear o editar) ───────────────────────────
    if ($action === 'save' && $entity === 'proxy') {
        $d      = $input['data'] ?? [];
        $id     = (int)($d['id']     ?? 0);
        $nombre = trim($d['nombre']  ?? '');
        $url    = trim($d['url']     ?? '');
        $activo = (int)($d['activo'] ?? 1);

        if (!$url) { resp(false, 'La URL del proxy es obligatoria'); }

        // Normalizar: asegurar barra final
        $url = rtrim($url, '/') . '/';

        if ($id) {
            $stmt = $conn->prepare("UPDATE proxies SET nombre=?, url=?, activo=? WHERE id=?");
            $stmt->bind_param('ssii', $nombre, $url, $activo, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO proxies (nombre, url, activo) VALUES (?,?,?)");
            $stmt->bind_param('ssi', $nombre, $url, $activo);
        }

        $ok = $stmt->execute();
        $stmt->close();
        resp($ok, $ok ? 'Proxy guardado' : 'Error al guardar (¿URL duplicada?)');
    }

    // ── TOGGLE ACTIVO PROXY ───────────────────────────────────────
    if ($action === 'toggle' && $entity === 'proxy') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) { resp(false, 'ID inválido'); }

        $stmt = $conn->prepare("UPDATE proxies SET activo = IF(activo=1,0,1) WHERE id=?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        resp($ok, $ok ? 'Estado actualizado' : 'Error al actualizar');
    }

    resp(false, 'Acción o entidad no reconocida');

} catch (Exception $e) {
    resp(false, 'Error del servidor: ' . $e->getMessage());
}

// ── Helper de respuesta ───────────────────────────────────────
function resp(bool $ok, string $msg, $data = null): void {
    $r = ['success' => $ok, 'message' => $msg];
    if ($data !== null) $r['data'] = $data;
    echo json_encode($r, JSON_UNESCAPED_UNICODE);
    exit();
}
