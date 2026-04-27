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
            'canal'   => 'canales',
            'fuente'  => 'fuentes',
            'liga'    => 'ligas',
            'partido' => 'partidos',
            default   => null,
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
            // Actualizar
            $stmt = $conn->prepare("UPDATE canales SET nombre=?, imagen=?, categoria=?, activo=? WHERE id=?");
            $stmt->bind_param('ssiii', $nombre, $imagen, $categoria, $activo, $id);
        } else {
            // Insertar
            $stmt = $conn->prepare("INSERT INTO canales (nombre, imagen, categoria, activo) VALUES (?,?,?,?)");
            $stmt->bind_param('ssii', $nombre, $imagen, $categoria, $activo);
        }

        $ok = $stmt->execute();
        $stmt->close();
        resp($ok, $ok ? 'Canal guardado' : 'Error al guardar');
    }

    // ── GUARDAR FUENTE (crear o editar) ───────────────────────
    if ($action === 'save' && $entity === 'fuente') {
        $d = $input['data'] ?? [];

        $id       = (int)($d['id']      ?? 0);
        $nombre   = trim($d['nombre']   ?? '');
        $canal    = (int)($d['canal']   ?? 0);
        $url      = trim($d['url']      ?? '');
        $ckKey    = trim($d['ck_key']   ?? '') ?: null;
        $ckKeyId  = trim($d['ck_keyid'] ?? '') ?: null;
        $pais     = $d['pais']   ? (int)$d['pais']   : null;
        $tipo     = (int)($d['tipo']    ?? 0);
        $epg      = trim($d['epg']      ?? '') ?: null;
        $activo   = (int)($d['activo']  ?? 1);

        if (!$nombre || !$canal || !$url || !$tipo) { resp(false, 'Nombre, canal, URL y tipo son obligatorios'); }

        if ($id) {
            $stmt = $conn->prepare("UPDATE fuentes SET nombre=?, canal=?, url=?, ck_key=?, ck_keyid=?, pais=?, tipo=?, epg=?, activo=? WHERE id=?");
            $stmt->bind_param('sissssiisi', $nombre, $canal, $url, $ckKey, $ckKeyId, $pais, $tipo, $epg, $activo, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO fuentes (nombre, canal, url, ck_key, ck_keyid, pais, tipo, epg, activo) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sissssiis', $nombre, $canal, $url, $ckKey, $ckKeyId, $pais, $tipo, $epg, $activo);
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
