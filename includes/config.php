<?php
/**
 * StreamHub - Configuración global
 * Incluido en todas las páginas antes de cualquier output.
 */

// ---- Base de datos ----
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'streamhub');

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

/** Devuelve el nombre del usuario o cadena vacía */
function userName(): string {
    return $_SESSION['user_name'] ?? '';
}

/** Devuelve el ID del usuario o 0 */
function userId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}
