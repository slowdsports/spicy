<?php
/**
 * StreamHub - Configuración global
 * Incluido en todas las páginas antes de cualquier output.
 */

// ---- Base de datos ----
define('DB_HOST', 'localhost');
define('DB_USER', 'u5869826_root');
define('DB_PASS', 'OF0wh^]#kK9C+U1W');
define('DB_NAME', 'u5869826_streamhub');

// ---- URL base (ajustar según carpeta del proyecto) ----
define('BASE_URL', '/spicy/');

// Iniciar sesión PHP si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// HELPERS DE URL
// ============================================================

/**
 * Construye una URL amigable sin extensión .php
 * Ej: url('eventos', ['type' => 'soccer']) → /streamhub/?p=eventos&type=soccer
 */
function url(string $page, array $params = []): string {
    $query = array_merge(['p' => $page], $params);
    return BASE_URL . '?' . http_build_query($query);
}

/**
 * Lee un parámetro GET sanitizado
 */
function get(string $key, string $default = ''): string {
    return isset($_GET[$key]) ? htmlspecialchars(trim($_GET[$key])) : $default;
}

// ============================================================
// HELPERS DE SESIÓN
// ============================================================

/** Devuelve true si el usuario está logueado */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/** Devuelve true si el usuario es administrador */
function isAdmin(): bool {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
}

/** Devuelve true si el usuario es spicy (premium) y su acceso no ha expirado */
function isSpicy(): bool {
    static $checked = false, $result = false;
    if ($checked) return $result;
    $checked = true;

    if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'spicy') {
        return $result = false;
    }

    // Verificar expiración en BD (solo si db.php ya fue incluido)
    if (!function_exists('getDBConnection')) return $result = true;

    try {
        $conn = getDBConnection();
        $id   = (int)($_SESSION['user_id'] ?? 0);
        if (!$id) return $result = false;

        $stmt = $conn->prepare("SELECT spicy_hasta FROM usuarios WHERE id = ? AND rol = 'spicy'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $_SESSION['user_rol'] = 'usuario';
            return $result = false;
        }

        if ($row['spicy_hasta'] && strtotime($row['spicy_hasta']) < time()) {
            // Acceso expirado: bajar a usuario
            $u = $conn->prepare("UPDATE usuarios SET rol = 'usuario', spicy_hasta = NULL WHERE id = ?");
            $u->bind_param('i', $id);
            $u->execute();
            $u->close();
            $_SESSION['user_rol'] = 'usuario';
            return $result = false;
        }

        return $result = true;
    } catch (Throwable $e) {
        return $result = true; // no penalizar al usuario si la BD falla
    }
}

/** Devuelve true si el usuario es admin o spicy (ambos roles privilegiados) */
function isPrivileged(): bool {
    return isAdmin() || isSpicy();
}

/** Devuelve el nombre del usuario o cadena vacía */
function userName(): string {
    return $_SESSION['user_name'] ?? '';
}

/** Devuelve el ID del usuario o 0 */
function userId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}
