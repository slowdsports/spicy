<?php
/**
 * Admin API — Generación manual de JSON estático
 * POST { entity: 'canales'|'fuentes'|'partidos' }
 */
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/cache.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true);
$entity = $input['entity'] ?? '';

try {
    switch ($entity) {

        case 'canales':
            $ok  = regenerateCanalesCache();
            $cnt = count(json_decode(file_get_contents(CACHE_DIR . '/channels.json'), true) ?? []);
            echo json_encode(['success' => $ok, 'message' => 'channels.json actualizado (' . $cnt . ' canales)']);
            break;

        case 'fuentes':
            $ok  = regenerateFuentesCache();
            $cnt = count(json_decode(file_get_contents(CACHE_DIR . '/fuentes.json'), true) ?? []);
            echo json_encode(['success' => $ok, 'message' => 'fuentes.json actualizado (' . $cnt . ' fuentes)']);
            break;

        case 'partidos':
            $ok  = regenerateMatchesCache();
            $cnt = count(json_decode(file_get_contents(CACHE_DIR . '/matches.json'), true) ?? []);
            echo json_encode(['success' => $ok, 'message' => 'matches.json actualizado (' . $cnt . ' partidos)']);
            break;

        case 'config':
            $ok = regenerateSiteConfigCache();
            echo json_encode(['success' => $ok, 'message' => 'config.json actualizado']);
            break;

        case 'destacados':
            $ok = regenerateDestacadosCache();
            echo json_encode(['success' => $ok, 'message' => 'destacados.json actualizado']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Entidad no válida: ' . htmlspecialchars($entity)]);
    }

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
