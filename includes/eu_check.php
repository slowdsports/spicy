<?php
/**
 * EU Access Control Middleware
 *
 * Usuarios desde Europa deben registrarse e iniciar sesión.
 * Un administrador debe aprobar su acceso manualmente.
 * La IP aprobada se guarda en BD para acceso automático futuro.
 */

$_EU_COUNTRIES = [
    'Trx'
];

function _euGetIp(): string {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
       ?? $_SERVER['HTTP_X_FORWARDED_FOR']
       ?? $_SERVER['REMOTE_ADDR']
       ?? '';
    return trim(explode(',', $ip)[0]);
}

function _euDetectCountry(string $ip): string {
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return '';
    }
    $ctx  = stream_context_create(['http' => ['timeout' => 3]]);
    $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country", false, $ctx);
    if (!$json) return '';
    $data = json_decode($json, true);
    return $data['country'] ?? '';
}

function _euIsEuCountry(string $country): bool {
    global $_EU_COUNTRIES;
    return in_array($country, $_EU_COUNTRIES, true);
}

function _euEnsureTable(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    getDBConnection()->query("
        CREATE TABLE IF NOT EXISTS eu_access (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            user_id    INT NOT NULL,
            ip         VARCHAR(45) NOT NULL,
            pais       VARCHAR(100) NOT NULL DEFAULT '',
            estado     ENUM('pendiente','aprobado','denegado') NOT NULL DEFAULT 'pendiente',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_ip     (ip),
            INDEX idx_user   (user_id),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

/**
 * Middleware principal. Llamar desde index.php después de _autoLoginFromCookie().
 * Si el visitante es europeo y no está aprobado, redirige según corresponda.
 */
function checkEuAccess(string $page): void {
    if (in_array($page, ['login', 'eu_pendiente'], true)) return;
    if (isAdmin()) return; // los administradores siempre tienen acceso completo

    $ip = _euGetIp();

    try {
        $conn = getDBConnection();
        _euEnsureTable();

        // Camino rápido: esta IP ya está aprobada
        $s = $conn->prepare("SELECT id FROM eu_access WHERE ip = ? AND estado = 'aprobado' LIMIT 1");
        $s->bind_param('s', $ip);
        $s->execute();
        $ipAprobada = $s->get_result()->num_rows > 0;
        $s->close();
        if ($ipAprobada) return;

        // Detección de país (cacheada en sesión para no llamar la API en cada carga)
        if (!isset($_SESSION['_eu_country'])) {
            $_SESSION['_eu_country'] = _euDetectCountry($ip);
        }
        $country = $_SESSION['_eu_country'];

        if (!_euIsEuCountry($country)) return;

        // Usuario europeo. Verificar si la IP está denegada
        $s2 = $conn->prepare("SELECT id FROM eu_access WHERE ip = ? AND estado = 'denegado' LIMIT 1");
        $s2->bind_param('s', $ip);
        $s2->execute();
        $ipDenegada = $s2->get_result()->num_rows > 0;
        $s2->close();

        if ($ipDenegada) {
            _euRedirigirPendiente('denegado');
        }

        // Sin sesión → ir al login
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?p=login');
            exit();
        }

        $userId = userId();

        // Estado del usuario: el mejor estado activo (aprobado > pendiente > denegado)
        $s3 = $conn->prepare("
            SELECT estado FROM eu_access
            WHERE user_id = ?
            ORDER BY FIELD(estado, 'aprobado', 'pendiente', 'denegado'), updated_at DESC
            LIMIT 1
        ");
        $s3->bind_param('i', $userId);
        $s3->execute();
        $row = $s3->get_result()->fetch_assoc();
        $s3->close();

        if ($row) {
            if ($row['estado'] === 'aprobado') {
                // Registrar esta IP como aprobada para el camino rápido futuro
                $sck = $conn->prepare("SELECT id FROM eu_access WHERE ip = ? LIMIT 1");
                $sck->bind_param('s', $ip);
                $sck->execute();
                $existe = $sck->get_result()->num_rows > 0;
                $sck->close();
                if (!$existe) {
                    $si = $conn->prepare(
                        "INSERT INTO eu_access (user_id, ip, pais, estado) VALUES (?,?,?,'aprobado')"
                    );
                    $si->bind_param('iss', $userId, $ip, $country);
                    $si->execute();
                    $si->close();
                }
                return;
            }
            if ($row['estado'] === 'denegado') {
                _euRedirigirPendiente('denegado');
            }
            // pendiente
            _euRedirigirPendiente('pendiente');
        }

        // Sin registro previo → crear solicitud pendiente
        $ins = $conn->prepare(
            "INSERT INTO eu_access (user_id, ip, pais, estado) VALUES (?,?,?,'pendiente')"
        );
        $ins->bind_param('iss', $userId, $ip, $country);
        $ins->execute();
        $ins->close();

        _euRedirigirPendiente('pendiente');

    } catch (Throwable $e) {
        // No bloquear al usuario si falla la BD
    }
}

function _euRedirigirPendiente(string $estado): void {
    $_SESSION['_eu_status'] = $estado;
    header('Location: ' . BASE_URL . '?p=eu_pendiente');
    exit();
}
