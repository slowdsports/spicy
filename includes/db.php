<?php
/**
 * StreamHub - Conexión a base de datos (singleton simple)
 */
function getDBConnection(): mysqli {
    static $conn = null;
    if ($conn !== null) return $conn;

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

/**
 * Si hay cookie sh_rem y no hay sesión activa, intenta re-autenticar
 * al usuario silenciosamente (remember-me token).
 */
function _autoLoginFromCookie(): void {
    if (isset($_SESSION['user_id'])) return;
    $raw = $_COOKIE['sh_rem'] ?? '';
    if (strlen($raw) !== 64) return;

    try {
        $conn = getDBConnection();

        // Crear tabla solo si aún no se verificó en esta sesión
        if (empty($_SESSION['_sp_tables_ok'])) {
            $conn->query("CREATE TABLE IF NOT EXISTS sesiones_persistentes (
                id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                usuario_id  INT UNSIGNED NOT NULL,
                token_hash  CHAR(64) NOT NULL,
                expira_en   DATETIME NOT NULL,
                creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_token (token_hash),
                KEY idx_usuario (usuario_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $_SESSION['_sp_tables_ok'] = 1;
        }

        $hash = hash('sha256', $raw);
        $now  = date('Y-m-d H:i:s');

        $stmt = $conn->prepare(
            "SELECT sp.id, sp.usuario_id, u.nombre, u.email, u.rol
             FROM sesiones_persistentes sp
             JOIN usuarios u ON u.id = sp.usuario_id
             WHERE sp.token_hash = ? AND sp.expira_en > ? AND u.activo = 1
             LIMIT 1"
        );
        $stmt->bind_param('ss', $hash, $now);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) return;

        // Restaurar sesión PHP
        $_SESSION['user_id']    = $row['usuario_id'];
        $_SESSION['user_name']  = $row['nombre'];
        $_SESSION['user_email'] = $row['email'];
        $_SESSION['user_rol']   = $row['rol'];

        // Extender vigencia del token 1 año más
        $newExpiry = date('Y-m-d H:i:s', strtotime('+1 year'));
        $upd = $conn->prepare("UPDATE sesiones_persistentes SET expira_en = ? WHERE id = ?");
        $upd->bind_param('si', $newExpiry, $row['id']);
        $upd->execute();
        $upd->close();

        // Refrescar cookie en el navegador (path restringido al directorio del sitio)
        $cookieOpts = [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => BASE_URL,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        setcookie('sh_rem', $raw, $cookieOpts);

    } catch (Throwable $e) {
        // No interrumpir la carga de página si la BD falla
    }
}

// Nota: _autoLoginFromCookie() debe llamarse explícitamente desde index.php,
// NO aquí, para evitar que corra en APIs, admin y otros contextos.
