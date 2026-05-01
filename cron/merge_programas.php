<?php
/**
 * Combina todos los JSON de data/programas/ en all.json.
 * Normaliza los horarios de cada canal a Honduras (UTC-6) usando su tz_source.
 *
 * Formatos aceptados por archivo:
 *   - Objeto único:      {canal, tz_source, programas:[...]}
 *   - Array de canales:  [{canal, programas}, ...]
 *
 * Archivos excluidos: all.json, tdt.json, tdt_epg.json
 */

$dir  = __DIR__ . '/../data/programas';
$out  = "$dir/all.json";
$skip = ['all.json', 'tdt.json', 'tdt_epg.json'];
$all  = [];

/**
 * Convierte una hora HH:MM desde un timezone fuente a Honduras (UTC-6).
 * $tz_src: offset UTC en minutos (ej. -180 para Argentina, -360 para Honduras)
 */
function normalizeToHonduras(string $time, int $tz_src): string {
    if ($time === '' || $tz_src === -360) return $time;
    $parts = explode(':', $time);
    if (count($parts) !== 2) return $time;
    $minutes  = (int)$parts[0] * 60 + (int)$parts[1];
    $minutes += (-360 - $tz_src); // diferencia hacia Honduras
    $minutes  = (($minutes % 1440) + 1440) % 1440;
    return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
}

/**
 * Normaliza los horarios del canal y elimina tz_source del output.
 */
function normalizeCanal(array $canal): array {
    $tz_src = (int)($canal['tz_source'] ?? -360);
    unset($canal['tz_source']);

    if ($tz_src !== -360) {
        foreach ($canal['programas'] as &$prog) {
            $prog['hora_inicio'] = normalizeToHonduras((string)($prog['hora_inicio'] ?? ''), $tz_src);
            $prog['hora_fin']    = normalizeToHonduras((string)($prog['hora_fin']    ?? ''), $tz_src);
        }
        unset($prog);
    }

    return $canal;
}

foreach (glob("$dir/*.json") as $file) {
    if (in_array(basename($file), $skip)) continue;

    $data = json_decode(file_get_contents($file), true);
    if (!$data) continue;

    // Array de canales (ej. tdt_epg.json — reservado para futura reintegración)
    if (isset($data[0]) && is_array($data[0])) {
        foreach ($data as $item) {
            if (isset($item['canal'], $item['programas'])) {
                $all[] = normalizeCanal($item);
            }
        }
        continue;
    }

    // Objeto canal individual
    if (isset($data['canal'], $data['programas'])) {
        $all[] = normalizeCanal($data);
    }
}

file_put_contents($out, json_encode($all, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo 'Merged ' . count($all) . ' canal(es) → all.json' . PHP_EOL;
?>
