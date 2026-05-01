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

$php  = PHP_BINARY;
$base = __DIR__;

$scripts = [
    'gato_individual.php',
    'mitv_individual.php',
    'merge_programas.php',
];

foreach ($scripts as $script) {
    $path = "$base/$script";
    echo "▶ $script\n";
    passthru("\"$php\" \"$path\" 2>&1");
    echo "\n";
}

echo "✔ EPG actualizado: " . date('Y-m-d H:i:s') . "\n";
