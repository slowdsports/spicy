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
        case 'login':    handleLogin($input);    break;
        case 'register': handleRegister($input); break;
        case 'logout':   handleLogout();         break;
        case 'check':    handleCheck();          break;
        default:         send(false, 'Acción no reconocida', null, 400);
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

function handleLogout(): void { $_SESSION = []; session_destroy(); send(true, 'Sesión cerrada'); }

function handleCheck(): void {
    if (isset($_SESSION['user_id'])) {
        send(true, 'Sesión activa', ['id' => $_SESSION['user_id'], 'name' => $_SESSION['user_name'], 'email' => $_SESSION['user_email'], 'rol' => $_SESSION['user_rol']]);
    } else { send(false, 'No hay sesión activa'); }
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
