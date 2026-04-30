<?php
/**
 * StreamHub - Reproductor Padre
 * ============================================================
 * Este archivo es el punto de entrada para todos los reproductores.
 * 
 * Funciones:
 * - Validar acceso desde iframe
 * - Bloquear acceso directo
 * - Obtener datos de la fuente desde BD
 * - Incluir el reproductor específico según el tipo
 * 
 * Tipos de reproductores soportados (tabla tipos_fuente):
 * 1 = m3u8      (JW Player/Clappr) - para streams M3U8
 * 2 = hls       (JW Player/Clappr) - para streams HLS
 * 3 = dash      (Bitmovin/JW Player) - para streams DASH
 * 4 = dash-drm  (Bitmovin con Widevine) - DASH con protección DRM
 * 5 = iframe    (HTML5 iframe directo) - embed de iframes
 * 6 = youtube   (YouTube embed) - videos de YouTube
 * 
 * PARA AGREGAR NUEVOS TIPOS:
 * 1. Agregar entrada en tabla tipos_fuente (id, nombre, icono)
 * 2. Crear archivo reproductor-{tipo_id}.php
 * 3. El reproductor padre incluirá automáticamente el archivo correcto
 * 4. Configurar las opciones del reproductor en ese archivo
 */

// ============================================================
// 1. VALIDAR ACCESO DESDE IFRAME
// ============================================================
$isIframe = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_SERVER['HTTP_REFERER']);
if (!$isIframe && php_sapi_name() !== 'cli') {
    if (empty($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
        http_response_code(403);
        die('Acceso denegado. Este contenido solo se puede reproducir desde la aplicación.');
    }
}

// ============================================================
// 2. CARGAR CONFIGURACIÓN Y BASE DE DATOS
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// ============================================================
// 3. OBTENER DATOS DE LA FUENTE
// ============================================================
$fuenteId = (int)($_GET['id'] ?? 0);
$fuenteData = null;

if ($fuenteId > 0) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT f.id, f.nombre, f.url, f.tipo, f.ck_key, f.ck_keyid, f.sandbox,
                   t.nombre as tipo_nombre
            FROM fuentes f
            LEFT JOIN tipos_fuente t ON f.tipo = t.id
            WHERE f.id = ? AND f.activo = 1 LIMIT 1
        ");
        $stmt->bind_param('i', $fuenteId);
        $stmt->execute();
        $result = $stmt->get_result();
        $fuenteData = $result->fetch_assoc();
        $stmt->close();
    } catch (Throwable $e) {
        $fuenteData = null;
    }
}

// Validar que la fuente exista y esté activa
if (!$fuenteData) {
    http_response_code(404);
    die('Fuente no encontrada o no activa.');
}

// ============================================================
// 4. INCLUIR REPRODUCTOR ESPECÍFICO SEGÚN TIPO
// ============================================================
$tipoId = (int)$fuenteData['tipo'];
$reproducotorFile = __DIR__ . "/reproductor-{$tipoId}.php";

// Validar que exista el reproductor para este tipo
if (!file_exists($reproducotorFile)) {
    http_response_code(500);
    die("Reproductor no soportado para tipo: {$fuenteData['tipo_nombre']} (ID: {$tipoId})");
}

// Incluir el reproductor específico
include $reproducotorFile;