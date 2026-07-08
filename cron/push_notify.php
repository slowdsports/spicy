<?php
/**
 * StreamHub - Avisos push de "tu equipo juega pronto"
 *
 * Recorre data/matches.json buscando partidos "upcoming" que arrancan en los
 * próximos PUSH_WINDOW_MIN minutos, cruza los equipos de cada uno contra
 * equipo_guardados + push_subscriptions, y manda un push a cada suscripción
 * encontrada. Cada partido se marca en data/push_sent/{id}.flag apenas se
 * evalúa (no solo si el envío tuvo éxito) para no reintentar de más si el
 * cron corre cada pocos minutos — peor caso: un usuario se pierde 1 aviso,
 * nunca le llegan diez duplicados.
 *
 * Cron recomendado (cada 5 minutos):
 *   php /ruta/al/sitio/cron/push_notify.php >> /ruta/a/logs/push.log 2>&1
 *   (minuto: "0-59/5" o el selector "Cada 5 minutos" del desplegable de cPanel)
 */

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

const PUSH_WINDOW_MIN = 20; // avisa a partidos que arrancan dentro de esta ventana

if (!VAPID_PUBLIC_KEY || !VAPID_PRIVATE_KEY) {
    fwrite(STDERR, "push_notify.php: faltan VAPID_PUBLIC_KEY/VAPID_PRIVATE_KEY en .env.php — abortando.\n");
    exit(1);
}

$matchesFile = __DIR__ . '/../data/matches.json';
$matches = file_exists($matchesFile) ? (json_decode(file_get_contents($matchesFile), true) ?? []) : [];

$sentDir = __DIR__ . '/../data/push_sent';
if (!is_dir($sentDir)) mkdir($sentDir, 0755, true);

$now = time();
$windowSec = PUSH_WINDOW_MIN * 60;

$candidates = [];
foreach ($matches as $m) {
    if (($m['status'] ?? '') !== 'upcoming') continue;
    $ts = (int)($m['timestamp'] ?? 0);
    if ($ts <= 0) continue;

    $delta = $ts - $now;
    if ($delta <= 0 || $delta > $windowSec) continue;

    $matchId = (int)($m['id'] ?? 0);
    if (!$matchId || file_exists("$sentDir/$matchId.flag")) continue;

    $candidates[] = $m;
}

if (!$candidates) {
    echo "push_notify.php: sin partidos en ventana de aviso (" . date('Y-m-d H:i:s') . ")\n";
    exit(0);
}

$conn = getDBConnection();
$conn->query("
    CREATE TABLE IF NOT EXISTS push_subscriptions (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        endpoint VARCHAR(500) NOT NULL,
        p256dh VARCHAR(255) NOT NULL,
        auth VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_endpoint (endpoint(255)),
        KEY idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");

$webPush = new WebPush([
    'VAPID' => [
        'subject'    => VAPID_SUBJECT,
        'publicKey'  => VAPID_PUBLIC_KEY,
        'privateKey' => VAPID_PRIVATE_KEY,
    ],
]);

$totalSent = 0;
$totalExpired = 0;

foreach ($candidates as $m) {
    $matchId = (int)$m['id'];
    $homeId  = (int)($m['homeTeam']['logo'] ?? 0);
    $awayId  = (int)($m['awayTeam']['logo'] ?? 0);

    // Se marca ANTES de intentar el envío — ver nota arriba sobre por qué.
    file_put_contents("$sentDir/$matchId.flag", date('c'));

    if (!$homeId && !$awayId) continue;

    $stmt = $conn->prepare("
        SELECT DISTINCT ps.id, ps.endpoint, ps.p256dh, ps.auth
        FROM equipo_guardados eg
        JOIN push_subscriptions ps ON ps.user_id = eg.user_id
        WHERE eg.equipo_id IN (?, ?)
    ");
    $stmt->bind_param('ii', $homeId, $awayId);
    $stmt->execute();
    $subs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (!$subs) continue;

    $minutesLeft = max(1, (int)round(($m['timestamp'] - $now) / 60));
    $homeName = $m['homeTeam']['name'] ?? '';
    $awayName = $m['awayTeam']['name'] ?? '';
    $title    = "{$homeName} vs {$awayName}";
    $body     = "Empieza en {$minutesLeft} min" . (!empty($m['leagueName']) ? " — {$m['leagueName']}" : '');
    $url      = rtrim(SITE_URL, '/') . BASE_URL . '?p=partido&id=' . $matchId;

    $payload = json_encode([
        'title' => $title,
        'body'  => $body,
        'url'   => $url,
        'tag'   => "partido-{$matchId}",
    ], JSON_UNESCAPED_UNICODE);

    foreach ($subs as $s) {
        $webPush->queueNotification(
            Subscription::create([
                'endpoint' => $s['endpoint'],
                'keys'     => ['p256dh' => $s['p256dh'], 'auth' => $s['auth']],
            ]),
            $payload
        );
    }
}

try {
    foreach ($webPush->flush() as $report) {
        $endpoint = $report->getEndpoint();
        if ($report->isSuccess()) {
            $totalSent++;
            continue;
        }

        // 404/410 = el navegador invalidó la suscripción (desinstaló, limpió
        // datos, etc.) — se borra para no seguir intentando enviarle para siempre.
        if ($report->isSubscriptionExpired()) {
            $del = $conn->prepare("DELETE FROM push_subscriptions WHERE endpoint = ?");
            $del->bind_param('s', $endpoint);
            $del->execute();
            $del->close();
            $totalExpired++;
        } else {
            fwrite(STDERR, "push_notify.php: fallo enviando a {$endpoint}: {$report->getReason()}\n");
        }
    }
} catch (Throwable $e) {
    // Defensa en profundidad: push_subscribe.php ya valida el formato de la
    // clave antes de guardarla, pero si igual algo corrupto llega hasta acá,
    // que no tumbe el resto del batch de esta corrida sin dejar rastro.
    fwrite(STDERR, "push_notify.php: excepción en flush(): {$e->getMessage()}\n");
}

echo "push_notify.php: " . count($candidates) . " partido(s) evaluado(s), {$totalSent} notificación(es) enviada(s), {$totalExpired} suscripción(es) expirada(s) limpiada(s). " . date('Y-m-d H:i:s') . "\n";
