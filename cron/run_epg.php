<?php
/**
 * Punto de entrada para el Cron Job en cPanel.
 * Ejecuta los scrapers en secuencia y luego genera all.json.
 *
 * Comando cPanel:
 *   /usr/bin/php /home/USUARIO/public_html/spicy/cron/run_epg.php
 *
 * Frecuencia recomendada: cada hora (los programas de TV cambian cada 30-120 min)
 */

set_time_limit(0);

$base = __DIR__;

$scripts = [
    'gato_individual.php',
    'mitv_individual.php',
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
