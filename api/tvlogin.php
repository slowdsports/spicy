<?php
/**
 * StreamHub – TV Login API
 * Acciones GET: create | poll | approve | auth
 */

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

while (ob_get_level() > 0) ob_end_clean();
ob_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

while (ob_get_level() > 0) ob_end_clean();

$action = $_GET['action'] ?? '';

// 'auth' redirige (no JSON): manejarlo antes de enviar cabeceras JSON
if ($action === 'auth') {
    handleTvAuth();
    exit();
}

header('Cache-Control: no-store, no-cache');
header('Content-Type: application/json; charset=utf-8');

try {
    switch ($action) {
        case 'create':  handleCreate();  break;
        case 'poll':    handlePoll();    break;
        case 'approve': handleApprove(); break;
        default:        send(false, 'Acción no válida', null, 400);
    }
} catch (Throwable $e) {
    send(false, 'Error del servidor', null, 500);
}

// ──────────────────────────────────────────────────────────────

function ensureTable(): void {
    getDBConnection()->query("CREATE TABLE IF NOT EXISTS tv_login_tokens (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        token      CHAR(32)  NOT NULL,
        usuario_id INT UNSIGNED NULL,
        status     ENUM('pending','approved') NOT NULL DEFAULT 'pending',
        expires_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_token (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function handleCreate(): void {
    ensureTable();
    $conn = getDBConnection();
    // Limpiar tokens viejos
    $conn->query("DELETE FROM tv_login_tokens WHERE expires_at < NOW()");

    $token   = bin2hex(random_bytes(16)); // 32 hex chars
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $ins = $conn->prepare("INSERT INTO tv_login_tokens (token, expires_at) VALUES (?, ?)");
    $ins->bind_param('ss', $token, $expires);
    $ins->execute();
    $ins->close();

    send(true, 'Token creado', ['token' => $token, 'expires_in' => 600]);
}

function handlePoll(): void {
    $token = preg_replace('/[^a-f0-9]/', '', $_GET['token'] ?? '');
    if (strlen($token) !== 32) { send(false, 'Token inválido'); return; }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT status, expires_at FROM tv_login_tokens WHERE token = ? LIMIT 1");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) { send(true, 'OK', ['status' => 'not_found']); return; }

    $status = (strtotime($row['expires_at']) < time()) ? 'expired' : $row['status'];
    send(true, 'OK', ['status' => $status]);
}

function handleApprove(): void {
    if (!isset($_SESSION['user_id'])) { send(false, 'Sesión requerida', null, 401); return; }

    $token = preg_replace('/[^a-f0-9]/', '', $_GET['token'] ?? ($_POST['token'] ?? ''));
    if (strlen($token) !== 32) { send(false, 'Token inválido'); return; }

    $conn  = getDBConnection();
    $check = $conn->prepare("SELECT id, status FROM tv_login_tokens WHERE token = ? AND expires_at > NOW() LIMIT 1");
    $check->bind_param('s', $token);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$row)                        { send(false, 'Token inválido o expirado'); return; }
    if ($row['status'] !== 'pending') { send(false, 'Token ya utilizado');        return; }

    $userId = (int)$_SESSION['user_id'];
    $upd    = $conn->prepare("UPDATE tv_login_tokens SET status = 'approved', usuario_id = ? WHERE token = ?");
    $upd->bind_param('is', $userId, $token);
    $upd->execute();
    $upd->close();

    send(true, 'TV autorizada');
}

function handleTvAuth(): void {
    $token  = preg_replace('/[^a-f0-9]/', '', $_GET['token'] ?? '');
    $errUrl = BASE_URL . '?p=login&tvlogin&tv_error=1';

    if (strlen($token) !== 32) { header('Location: ' . $errUrl); exit(); }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT usuario_id, status FROM tv_login_tokens WHERE token = ? LIMIT 1");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || $row['status'] !== 'approved' || !$row['usuario_id']) {
        header('Location: ' . $errUrl);
        exit();
    }

    $userId = (int)$row['usuario_id'];
    $uStmt  = $conn->prepare("SELECT id, nombre, email, rol, activo FROM usuarios WHERE id = ? LIMIT 1");
    $uStmt->bind_param('i', $userId);
    $uStmt->execute();
    $user = $uStmt->get_result()->fetch_assoc();
    $uStmt->close();

    if (!$user || !$user['activo']) { header('Location: ' . $errUrl); exit(); }

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['nombre'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_rol']   = $user['rol'];

    tvPersistSession((int)$user['id'], $conn);

    $del = $conn->prepare("DELETE FROM tv_login_tokens WHERE token = ?");
    $del->bind_param('s', $token);
    $del->execute();
    $del->close();

    header('Location: ' . BASE_URL . '?p=home');
    exit();
}

function tvPersistSession(int $userId, $conn): void {
    try {
        $conn->query("CREATE TABLE IF NOT EXISTS sesiones_persistentes (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            usuario_id  INT UNSIGNED NOT NULL,
            token_hash  CHAR(64) NOT NULL,
            expira_en   DATETIME NOT NULL,
            creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_token (token_hash),
            KEY idx_usuario (usuario_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $raw    = bin2hex(random_bytes(32));
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
    } catch (Throwable $e) {}
}

function send(bool $ok, string $msg, $data = null, int $code = 200): void {
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
