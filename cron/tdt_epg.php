<?php
/**
 * Descarga el EPG de TDTChannels y genera data/programas/tdt_epg.json
 * Formato de salida: [{canal: "La1.TV", programas: [...]}, ...]
 *
 * Los timestamps hi/hf son UTC. en_vivo se calcula aquí con time() (UTC).
 * Los timestamps se guardan también para que el cliente calcule el progreso exacto.
 */

$epg_url = 'https://www.tdtchannels.com/epg/TV.json';
$out     = __DIR__ . '/../data/programas/tdt_epg.json';

$json = @file_get_contents($epg_url);
if (!$json) {
    echo "Error al obtener el EPG de TDT: $epg_url\n";
    exit(1);
}

$channels = json_decode($json, true);
if (!is_array($channels)) {
    echo "Error al parsear el JSON del EPG de TDT.\n";
    exit(1);
}

$now    = time();
$result = [];

foreach ($channels as $channel) {
    $epg_id = $channel['name'] ?? '';
    if (!$epg_id || empty($channel['events'])) continue;

    $programas = [];
    foreach ($channel['events'] as $ev) {
        $hi = (int)($ev['hi'] ?? 0);
        $hf = (int)($ev['hf'] ?? 0);
        if (!$hi || !$hf) continue;

        $en_vivo = ($now >= $hi && $now < $hf);

        $programas[] = [
            'hora_inicio' => gmdate('H:i', $hi),
            'hora_fin'    => gmdate('H:i', $hf),
            'ts_inicio'   => $hi,
            'ts_fin'      => $hf,
            'titulo'      => $ev['t'] ?? '',
            'descripcion' => $ev['d'] ?? '',
            'enlace'      => '',
            'imagen'      => $ev['c'] ?? '',
            'tipo'        => strtolower($ev['g'] ?? ''),
            'periodo'     => '',
            'en_vivo'     => $en_vivo,
        ];
    }

    $result[] = [
        'canal'     => $epg_id,
        'programas' => $programas,
    ];
}

file_put_contents($out, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo 'Guardado: ' . $out . ' (' . count($result) . " canales)\n";
?>
