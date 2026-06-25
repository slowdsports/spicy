<?php
/**
 * Punto de entrada para el Cron Job en cPanel.
 * Ejecuta los scrapers en secuencia y luego genera all.json.
 *
 * Comando cPanel:
 *   /usr/bin/php /home/USUARIO/public_html/spicy/cron/run_epg.php
 *
 * Frecuencia recomendada: cada 30 minutos (configurar en cPanel > Cron Jobs)
 *
 * Si un canal queda más de 3 horas sin actualizarse (sitio fuente caído,
 * bloqueo, etc.), merge_programas.php lo detecta y deja de marcarlo como
 * "en vivo" — revisa la salida de este script (cPanel suele mandarla por
 * correo) si ves la advertencia "⚠ Datos obsoletos".
 */

set_time_limit(0);

$base = __DIR__;

$scripts = [
    'gato_individual.php',
    'mitv_individual.php',
    'tdt_epg.php',
    'merge_programas.php',
];

foreach ($scripts as $script) {
    echo "▶ $script\n";
    // Ejecutar en scope aislado para evitar colisión de variables entre scripts
    (static function (string $path): void {
        require $path;
    })("$base/$script");
    echo "\n";
}

echo "✔ EPG actualizado: " . date('Y-m-d H:i:s') . "\n";
