<?php
/**
 * StreamHub - API de Autenticación
 * Endpoint: /api/auth.php
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

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

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) { send(false, 'Petición inválida', null, 400); }

switch ($input['action']) {
    case 'login':    handleLogin($input);    break;
    case 'register': handleRegister($input); break;
    case 'logout':   handleLogout();         break;
    case 'check':    handleCheck();          break;
    default:         send(false, 'Acción no reconocida', null, 400);
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
    http_response_code($code);
    $r = ['success' => $ok, 'message' => $msg];
    if ($data !== null) $r['data'] = $data;
    echo json_encode($r, JSON_UNESCAPED_UNICODE);
    exit();
}
