<?php
/**
 * Admin API — Configuración del chat (lectura y escritura).
 * GET  → devuelve la config actual
 * POST → guarda nueva config
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'Sin permisos']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

$configFile = __DIR__ . '/../../data/chat-config.json';

$defaults = ['mode' => 'custom', 'twitch_channel' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input         = json_decode(file_get_contents('php://input'), true) ?? [];
    $mode          = in_array($input['mode'] ?? '', ['custom', 'twitch']) ? $input['mode'] : 'custom';
    $twitchChannel = preg_replace('/[^a-zA-Z0-9_]/', '', $input['twitch_channel'] ?? '');

    $config = ['mode' => $mode, 'twitch_channel' => $twitchChannel];
    $ok     = file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT)) !== false;

    echo json_encode(['ok' => $ok, 'msg' => $ok ? 'Guardado correctamente' : 'Error al guardar el archivo']);
    exit();
}

// GET
$config = file_exists($configFile)
    ? (json_decode(file_get_contents($configFile), true) ?? $defaults)
    : $defaults;

echo json_encode(['ok' => true, 'config' => $config]);
