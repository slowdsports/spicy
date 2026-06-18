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
        $allowed     = ['bitmovin', 'clappr', 'jwplayer'];
        $reproductor = in_array($d['reproductor'] ?? '', $allowed) ? $d['reproductor'] : 'bitmovin';

        if (!$nombre || !$canal || !$url || !$tipo) { resp(false, 'Nombre, canal, URL y tipo son obligatorios'); }

        if ($id) {
            $stmt = $conn->prepare("UPDATE fuentes SET nombre=?, canal=?, url=?, url_ios=?, tipo_ios=?, ck_key=?, ck_keyid=?, pais=?, tipo=?, epg=?, activo=?, sandbox=?, mostrar_tv=?, reproductor=?, usar_proxy=? WHERE id=?");
            $stmt->bind_param('sissssssissiisii', $nombre, $canal, $url, $urlIos, $tipoIos, $ckKey, $ckKeyId, $pais, $tipo, $epg, $activo, $sandbox, $mostrar_tv, $reproductor, $usar_proxy, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO fuentes (nombre, canal, url, url_ios, tipo_ios, ck_key, ck_keyid, pais, tipo, epg, activo, sandbox, mostrar_tv, reproductor, usar_proxy) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sissssssissiisi', $nombre, $canal, $url, $urlIos, $tipoIos, $ckKey, $ckKeyId, $pais, $tipo, $epg, $activo, $sandbox, $mostrar_tv, $reproductor, $usar_proxy);
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
