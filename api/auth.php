<?php
/**
 * StreamHub - API de Autenticación
 * Endpoint: /api/auth.php
 */

// Asegurar que errores PHP nunca contaminen la respuesta JSON
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

// Capturar CUALQUIER output (warnings, notices, HTML de error del hosting)
// antes de que contamine el JSON
while (ob_get_level() > 0) ob_end_clean();
ob_start();

// Capturar errores fatales que no puede atrapar try/catch
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        while (ob_get_level() > 0) ob_end_clean();
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['success' => false, 'message' => 'Error crítico del servidor']);
    }
});

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Descartar todo lo que los includes pudieran haber emitido
while (ob_get_level() > 0) ob_end_clean();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

if (session_status() === PHP_SESSION_NONE) session_start();

// Soportar logout por GET (desde enlace de navbar)
if (isset($_GET['action']) && $_GET['action'] === 'logout_redirect') {
    _destroyPersistentSession();
    $_SESSION = [];
    session_destroy();
    header('Location: ' . BASE_URL . '?p=home');
    exit();
}

try {
    // Leer input: JSON del cuerpo (fetch estándar) o $_POST como fallback
    $raw   = file_get_contents('php://input');
    $input = ($raw !== false && $raw !== '') ? json_decode($raw, true) : null;
    if (empty($input) && !empty($_POST)) {
        $input = $_POST; // fallback: form-encoded (útil si el WAF bloquea JSON)
    }
    if (!$input || !isset($input['action'])) { send(false, 'Petición inválida', null, 400); }

    switch ($input['action']) {
        case 'login':           handleLogin($input);          break;
        case 'register':        handleRegister($input);       break;
        case 'logout':           handleLogout();               break;
        case 'check':            handleCheck();                break;
        case 'forgot_password':  handleForgotPassword($input); break;
        case 'reset_password':   handleResetPassword($input);  break;
        default:                 send(false, 'Acción no reconocida', null, 400);
    }
} catch (Throwable $e) {
    send(false, 'Error interno del servidor', null, 500);
}

function handleLogin(array $d): void {
    if (empty($d['email']) || empty($d['password'])) { send(false, 'Email y contraseña requeridos'); return; }
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, nombre, email, password, rol, activo FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $d['email']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$user) { send(false, 'Credenciales incorrectas'); return; }
        if (!$user['activo']) { send(false, 'Cuenta suspendida'); return; }
        if (!password_verify($d['password'], $user['password'])) { send(false, 'Credenciales incorrectas'); return; }
        $_SESSION['user_id'] = $user['id']; $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email']; $_SESSION['user_rol'] = $user['rol'];
        _persistSession((int)$user['id']);
        send(true, 'Login exitoso', ['id' => $user['id'], 'name' => $user['nombre'], 'email' => $user['email'], 'rol' => $user['rol']]);
    } catch (Exception $e) { send(false, 'Error del servidor', null, 500); }
}

function handleRegister(array $d): void {
    if (empty($d['name']) || empty($d['email']) || empty($d['password'])) { send(false, 'Todos los campos son requeridos'); return; }
    $email = strtolower(trim($d['email']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { send(false, 'Email no válido'); return; }
    if (strlen($d['password']) < 6) { send(false, 'Contraseña mínimo 6 caracteres'); return; }
    try {
        $conn = getDBConnection();
        $chk = $conn->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $chk->bind_param('s', $email); $chk->execute();
        if ($chk->get_result()->num_rows > 0) { send(false, 'Email ya registrado'); return; }
        $hash = password_hash($d['password'], PASSWORD_DEFAULT);
        $ins = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'usuario')");
        $ins->bind_param('sss', $d['name'], $email, $hash); $ins->execute();
        $newId = $conn->insert_id;
        $_SESSION['user_id'] = $newId; $_SESSION['user_name'] = $d['name'];
        $_SESSION['user_email'] = $email; $_SESSION['user_rol'] = 'usuario';
        send(true, 'Cuenta creada', ['id' => $newId, 'name' => $d['name'], 'email' => $email, 'rol' => 'usuario']);
    } catch (Exception $e) { send(false, 'Error del servidor', null, 500); }
}

/**
 * Solicitud de reseteo de contraseña.
 *
 * Reglas de seguridad clave:
 *  - SIEMPRE responde con el mismo mensaje genérico, exista o no la cuenta
 *    (evita "enumeración de usuarios" — que alguien descubra qué correos
 *    están registrados probando este endpoint).
 *  - Rate-limit por IP y por usuario (ventana de 1 hora) para evitar spam
 *    de correos hacia la víctima o fuerza bruta de solicitudes.
 *  - El token nunca se guarda en texto plano, solo su hash SHA-256 (igual
 *    que el "remember me" de _persistSession más abajo).
 *  - Cualquier token previo sin usar de ese usuario se invalida al pedir uno
 *    nuevo, así un enlace viejo filtrado deja de servir.
 */
function handleForgotPassword(array $d): void {
    $generic = 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña.';
    $email   = strtolower(trim((string)($d['email'] ?? '')));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send(false, 'Ingresa un correo electrónico válido.');
        return;
    }

    try {
        $conn = getDBConnection();
        _ensurePasswordResetsTable($conn);

        $ip = trim(explode(',', $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '')[0]);

        // Tope por IP (independiente de qué correo se pida): evita que alguien
        // recorra muchos correos distintos pidiendo resets desde la misma IP.
        $rlIp = $conn->prepare("SELECT COUNT(*) c FROM password_resets WHERE ip = ? AND creado_en > (NOW() - INTERVAL 1 HOUR)");
        $rlIp->bind_param('s', $ip);
        $rlIp->execute();
        $ipCount = (int)($rlIp->get_result()->fetch_assoc()['c'] ?? 0);
        $rlIp->close();

        if ($ipCount < 6) {
            $stmt = $conn->prepare("SELECT id, nombre, activo FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && $user['activo']) {
                // Tope por usuario: máximo 3 solicitudes por hora
                $rlUser = $conn->prepare("SELECT COUNT(*) c FROM password_resets WHERE usuario_id = ? AND creado_en > (NOW() - INTERVAL 1 HOUR)");
                $rlUser->bind_param('i', $user['id']);
                $rlUser->execute();
                $userCount = (int)($rlUser->get_result()->fetch_assoc()['c'] ?? 0);
                $rlUser->close();

                if ($userCount < 3) {
                    $inv = $conn->prepare("UPDATE password_resets SET usado = 1 WHERE usuario_id = ? AND usado = 0");
                    $inv->bind_param('i', $user['id']);
                    $inv->execute();
                    $inv->close();

                    $raw    = bin2hex(random_bytes(32)); // 256 bits — token crudo, solo va por correo
                    $hash   = hash('sha256', $raw);       // solo el hash se guarda en BD
                    $expiry = date('Y-m-d H:i:s', strtotime('+60 minutes'));

                    $ins = $conn->prepare("INSERT INTO password_resets (usuario_id, token_hash, expira_en, ip) VALUES (?, ?, ?, ?)");
                    $ins->bind_param('isss', $user['id'], $hash, $expiry, $ip);
                    $ins->execute();
                    $ins->close();

                    $link = urlAbsolute('reset_password', ['token' => $raw]);
                    _sendPasswordResetEmail($email, $user['nombre'], $link);
                }
            }
        }

        // Misma respuesta sin importar si el correo existe, si está activo,
        // si se llegó al límite, o si el envío del correo falló.
        send(true, $generic);
    } catch (Throwable $e) {
        send(true, $generic);
    }
}

/**
 * Aplica el token recibido por correo: valida (existe, sin usar, sin
 * expirar) y actualiza la contraseña. De un solo uso — se marca "usado"
 * apenas se consume. También cierra cualquier sesión "recordada" (cookie
 * persistente) de ese usuario, por si la contraseña anterior fue
 * comprometida — fuerza a re-loguearse en todos los dispositivos.
 */
function handleResetPassword(array $d): void {
    $token    = trim((string)($d['token'] ?? ''));
    $password = (string)($d['password'] ?? '');

    if ($token === '' || strlen($token) !== 64 || !ctype_xdigit($token)) {
        send(false, 'Enlace inválido o expirado. Solicita uno nuevo.');
        return;
    }
    if (strlen($password) < 6) {
        send(false, 'La contraseña debe tener al menos 6 caracteres.');
        return;
    }

    try {
        $conn = getDBConnection();
        _ensurePasswordResetsTable($conn);

        $hash = hash('sha256', $token);
        $stmt = $conn->prepare("
            SELECT pr.id AS reset_id, u.id AS user_id, u.activo
            FROM password_resets pr
            JOIN usuarios u ON u.id = pr.usuario_id
            WHERE pr.token_hash = ? AND pr.usado = 0 AND pr.expira_en > NOW()
            LIMIT 1
        ");
        $stmt->bind_param('s', $hash);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || !$row['activo']) {
            send(false, 'Enlace inválido o expirado. Solicita uno nuevo.');
            return;
        }

        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $upd->bind_param('si', $newHash, $row['user_id']);
        $upd->execute();
        $upd->close();

        $mark = $conn->prepare("UPDATE password_resets SET usado = 1 WHERE id = ?");
        $mark->bind_param('i', $row['reset_id']);
        $mark->execute();
        $mark->close();

        $sess = $conn->prepare("DELETE FROM sesiones_persistentes WHERE usuario_id = ?");
        $sess->bind_param('i', $row['user_id']);
        $sess->execute();
        $sess->close();

        send(true, 'Contraseña actualizada. Ya puedes iniciar sesión.');
    } catch (Throwable $e) {
        send(false, 'Error del servidor', null, 500);
    }
}

function _ensurePasswordResetsTable(mysqli $conn): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        usuario_id  INT NOT NULL,
        token_hash  CHAR(64) NOT NULL,
        expira_en   DATETIME NOT NULL,
        usado       TINYINT(1) NOT NULL DEFAULT 0,
        ip          VARCHAR(45) DEFAULT NULL,
        creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_token (token_hash),
        KEY idx_usuario (usuario_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/**
 * $toEmail llega ya validado con FILTER_VALIDATE_EMAIL por el caller —
 * eso descarta saltos de línea, así que no hay riesgo de inyección de
 * cabeceras de correo a través de ese parámetro. $name solo se usa dentro
 * del cuerpo HTML (con htmlspecialchars), nunca en una cabecera.
 *
 * Usa mail() nativo de PHP (sin SMTP) — funciona en la mayoría de hosting
 * compartido tipo cPanel sin configuración extra. Si la entrega a bandeja
 * de entrada no es confiable, esta es la única función que habría que
 * cambiar por un envío vía SMTP/PHPMailer.
 */
function _sendPasswordResetEmail(string $toEmail, string $name, string $link): bool {
    $host = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'teledeportes.online');
    $host = preg_replace('/^www\./', '', $host);
    $from = 'no-reply@' . $host;

    $subject  = 'Recupera tu contraseña - Tele Deportes';
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

    $body = "
    <div style=\"font-family:Arial,sans-serif;max-width:480px;margin:0 auto;color:#1f1f1f;\">
      <h2 style=\"color:#8b5cf6;\">Tele Deportes</h2>
      <p>Hola {$safeName},</p>
      <p>Recibimos una solicitud para restablecer tu contraseña. Si fuiste tú, hacé clic en el siguiente botón (válido por 60 minutos):</p>
      <p style=\"text-align:center;margin:28px 0;\">
        <a href=\"{$safeLink}\" style=\"background:#8b5cf6;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block;\">Restablecer contraseña</a>
      </p>
      <p style=\"font-size:13px;color:#666;\">Si no solicitaste esto, ignora este correo — tu contraseña actual sigue funcionando.</p>
      <p style=\"font-size:12px;color:#999;\">Si el botón no funciona, copia y pega este enlace en tu navegador:<br>{$safeLink}</p>
    </div>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Tele Deportes <{$from}>\r\n";

    try {
        return @mail($toEmail, $subject, $body, $headers);
    } catch (Throwable $e) {
        return false;
    }
}

function handleLogout(): void { _destroyPersistentSession(); $_SESSION = []; session_destroy(); send(true, 'Sesión cerrada'); }

function handleCheck(): void {
    if (isset($_SESSION['user_id'])) {
        send(true, 'Sesión activa', ['id' => $_SESSION['user_id'], 'name' => $_SESSION['user_name'], 'email' => $_SESSION['user_email'], 'rol' => $_SESSION['user_rol']]);
    } else { send(false, 'No hay sesión activa'); }
}

function _persistSession(int $userId): void {
    try {
        $conn = getDBConnection();

        $conn->query("CREATE TABLE IF NOT EXISTS sesiones_persistentes (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            usuario_id  INT UNSIGNED NOT NULL,
            token_hash  CHAR(64) NOT NULL,
            expira_en   DATETIME NOT NULL,
            creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_token (token_hash),
            KEY idx_usuario (usuario_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $raw    = bin2hex(random_bytes(32)); // 64 hex chars
        $hash   = hash('sha256', $raw);
        $expiry = date('Y-m-d H:i:s', strtotime('+1 year'));

        $ins = $conn->prepare("INSERT INTO sesiones_persistentes (usuario_id, token_hash, expira_en) VALUES (?, ?, ?)");
        $ins->bind_param('iss', $userId, $hash, $expiry);
        $ins->execute();
        $ins->close();

        setcookie('sh_rem', $raw, [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => BASE_URL,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } catch (Throwable $e) {
        // Cookie persistence is best-effort; don't break login on failure
    }
}

function _destroyPersistentSession(): void {
    $raw = $_COOKIE['sh_rem'] ?? '';
    if (strlen($raw) !== 64) return;
    try {
        $conn = getDBConnection();
        $hash = hash('sha256', $raw);
        $del  = $conn->prepare("DELETE FROM sesiones_persistentes WHERE token_hash = ?");
        $del->bind_param('s', $hash);
        $del->execute();
        $del->close();
    } catch (Throwable $e) {}
    setcookie('sh_rem', '', ['expires' => 1, 'path' => BASE_URL, 'httponly' => true, 'samesite' => 'Lax']);
}

function send(bool $ok, string $msg, $data = null, int $code = 200): void {
    // Vaciar cualquier output acumulado (warnings, notices, HTML del hosting)
    while (ob_get_level() > 0) ob_end_clean();
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
    }
    $r = ['success' => $ok, 'message' => $msg];
    if ($data !== null) $r['data'] = $data;
    echo json_encode($r, JSON_UNESCAPED_UNICODE);
    exit();
}
