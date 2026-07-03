<?php
/**
 * StreamHub - Configuración global
 * Incluido en todas las páginas antes de cualquier output.
 */

// includes/.env.php nunca se versiona (ver .gitignore) ni lo toca el deploy
// (.cpanel.yml hace "cp -Rf *" desde el checkout de git, que no lo incluye),
// así que sirve para tener credenciales distintas en local y en el servidor
// sin tener que editar este archivo a mano en cada deploy. Ver
// includes/.env.example.php para la plantilla.
$_env = file_exists(__DIR__ . '/.env.php') ? (require __DIR__ . '/.env.php') : [];

define('DB_HOST', $_env['DB_HOST'] ?? 'localhost');
define('DB_USER', $_env['DB_USER'] ?? 'root');
define('DB_PASS', $_env['DB_PASS'] ?? '');
define('DB_NAME', $_env['DB_NAME'] ?? 'streamhub');
define('BASE_URL', $_env['BASE_URL'] ?? '/spicy/');

unset($_env);

// Secreto para firmar tokens de stream — derivado de credenciales de BD (nunca cambia entre requests)
define('APP_SECRET', hash('sha256', DB_HOST . DB_USER . DB_PASS . DB_NAME . 'sh_stream_v1'));

// Sesiones persistentes: cookie dura 1 año en el navegador
// (el remember-me token en BD cubre el caso de que la sesión
//  del servidor expire por inactividad, igual que Facebook/Twitter)
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 365);
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 365,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Impedir que Nginx / proxies compartan la respuesta PHP entre usuarios.
// Cada respuesta PHP puede contener datos de sesión específicos del usuario.
// Los navegadores individuales todavía pueden cachear localmente (Back button, etc.).
if (!headers_sent()) {
    header('Cache-Control: private, no-store');
    header('X-Accel-Expires: 0'); // directiva específica de Nginx
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
 * Igual que url(), pero con esquema + dominio. url() es correcta para los
 * <a href> internos del sitio (el navegador la resuelve contra la página
 * actual), pero no sirve para links que se comparten fuera del sitio
 * (mensajes de Telegram/WhatsApp, etc.): ahí una ruta relativa como
 * "/?p=canal&id=1" no es un link funcional, hace falta el dominio.
 */
function urlAbsolute(string $page, array $params = []): string {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host  = $_SERVER['HTTP_HOST'] ?? 'teledeportes.online';
    return $proto . '://' . $host . url($page, $params);
}

/**
 * Lee un parámetro GET sanitizado
 */
function get(string $key, string $default = ''): string {
    return isset($_GET[$key]) ? htmlspecialchars(trim($_GET[$key])) : $default;
}

/**
 * Carpeta de logos (ligas/equipos) según el proveedor que originó el ID.
 * Los IDs de FotMob se guardan con un offset de +900000000 para no
 * colisionar con los IDs de Sofascore en las mismas tablas (ver admin/fotmob.php).
 */
function logoFolder($id): string {
    return ((int)$id >= 900000000) ? 'fm' : 'sf';
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

/**
 * Devuelve true si el usuario es spicy (premium) y su acceso no ha expirado.
 * El resultado se cachea en $_SESSION durante SPICY_CHECK_TTL: evita repetir
 * la consulta a `usuarios` en cada página vista por el mismo usuario.
 */
function isSpicy(): bool {
    static $checked = false, $result = false;
    if ($checked) return $result;
    $checked = true;

    if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'spicy') {
        return $result = false;
    }

    $spicyCheckTtl = 3600; // revalidar contra la BD como máximo 1 vez por hora
    if (
        isset($_SESSION['spicy_valid'], $_SESSION['spicy_checked_at'])
        && (time() - $_SESSION['spicy_checked_at']) < $spicyCheckTtl
    ) {
        return $result = $_SESSION['spicy_valid'];
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
            $_SESSION['user_rol']       = 'usuario';
            $_SESSION['spicy_valid']      = false;
            $_SESSION['spicy_checked_at'] = time();
            return $result = false;
        }

        if ($row['spicy_hasta'] && strtotime($row['spicy_hasta']) < time()) {
            // Acceso expirado: bajar a usuario
            $u = $conn->prepare("UPDATE usuarios SET rol = 'usuario', spicy_hasta = NULL WHERE id = ?");
            $u->bind_param('i', $id);
            $u->execute();
            $u->close();
            $_SESSION['user_rol']        = 'usuario';
            $_SESSION['spicy_valid']      = false;
            $_SESSION['spicy_checked_at'] = time();
            return $result = false;
        }

        $_SESSION['spicy_valid']      = true;
        $_SESSION['spicy_checked_at'] = time();
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

/**
 * Devuelve true si el User-Agent corresponde a una Smart TV / Smart Box
 * (compartido entre pages/login.php — modo QR —, pages/canal.php — vista
 * simplificada a pantalla completa — y pages/reproductor-3.php — controles
 * extra de sonido/pantalla completa, ver attachFullBitmovinUi).
 *
 * Admite el override "?ua=smart" para poder probar la vista de Smart TV
 * desde un navegador de escritorio normal sin cambiar el User-Agent real.
 */
function isSmartTvDevice(): bool {
    static $result = null;
    if ($result !== null) return $result;

    if (($_GET['ua'] ?? '') === 'smart') return $result = true;

    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    $patterns = [
        'smart-tv', 'smarttv', 'netcast', 'webos', 'tizen', 'hbbtv',
        'firetv', 'fire tv', 'appletv', 'apple tv', 'roku',
        'androidtv', 'android tv', 'bravia', 'viera',
        'googletv', 'google tv', 'aftb', 'aftt', 'aftm', 'afts', 'aftr', 'aftn',
        'crkey', 'nettv', 'netrange', 'philips tv',
    ];
    foreach ($patterns as $p) {
        if (strpos($ua, $p) !== false) return $result = true;
    }
    return $result = false;
}

// ============================================================
// HELPERS DE ESTADO DEL SITIO
// ============================================================

/**
 * Lee data/config.json (caché de la tabla config_sitio). Nunca consulta la
 * base de datos: ese archivo solo lo regenera includes/cache.php cuando un
 * admin guarda cambios en admin/pages/config.php.
 */
function siteConfig(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;

    $path = __DIR__ . '/../data/config.json';
    $cfg  = file_exists($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
    return $cfg;
}

/** Devuelve true si el sitio está en modo mantenimiento (config_sitio.mantenimiento cacheado) */
function isMaintenanceMode(): bool {
    return (int)(siteConfig()['mantenimiento'] ?? 0) === 1;
}
