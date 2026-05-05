<?php
require __DIR__ . '/scraper.php';

$canales = [
    'hbo-2',
    'hbo-oeste',
    // Agrega más IDs aquí...
];

$url_base  = 'https://mi.tv/mx/async/channel/';
$tz_offset = '-360'; // Honduras UTC-6

foreach ($canales as $canal_id) {
    $url  = "{$url_base}{$canal_id}/{$tz_offset}/";
    $html = file_get_html($url);

    if (!$html) {
        echo "Error al cargar: $url\n";
        continue;
    }

    // Primera pasada: recoger todos los items con sus datos
    $rows = [];

    foreach ($html->find('ul.broadcasts li') as $li) {
        $cls = (string)$li->class;

        // Saltar anuncios nativos
        if (strpos($cls, 'native') !== false) continue;

        $timeEl = $li->find('span.time', 0);
        if (!$timeEl) continue;

        $hora_inicio = trim($timeEl->plaintext);

        // Título: eliminar el ícono <img class="peli"> antes de leer el texto
        $titulo = '';
        $h2 = $li->find('h2', 0);
        if ($h2) {
            foreach ($h2->find('img') as $img) $img->outertext = '';
            $titulo = trim(html_entity_decode($h2->plaintext, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        // Tipo/género (segundo span.sub-title: "Comedia / 2018 / 6.2")
        $tipo = '';
        $subTitles = $li->find('span.sub-title');
        if (count($subTitles) >= 2) {
            $parts = explode('/', $subTitles[1]->plaintext);
            $tipo  = trim($parts[0]);
        }

        // Descripción
        $synEl = $li->find('p.synopsis', 0);
        $descripcion = $synEl
            ? trim(html_entity_decode($synEl->plaintext, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            : '';

        // Enlace
        $linkEl = $li->find('a.program-link', 0);
        $enlace = $linkEl ? "https://mi.tv{$linkEl->href}" : '';

        // Imagen desde background-image: url(...)
        $imagen = '';
        $imgDiv = $li->find('div.image', 0);
        if ($imgDiv) {
            if (preg_match("/url\(['\"]?([^'\")\s]+)['\"]?\)/", (string)$imgDiv->style, $m)) {
                $imagen = $m[1];
            }
        }

        $rows[] = [
            'hora_inicio' => $hora_inicio,
            'titulo'      => $titulo,
            'descripcion' => $descripcion,
            'enlace'      => $enlace,
            'imagen'      => $imagen,
            'tipo'        => $tipo,
            'en_vivo'     => strpos($cls, 'ongoing') !== false,
        ];
    }

    // Segunda pasada: hora_fin = hora_inicio del siguiente programa
    $programas = [];
    $total = count($rows);

    for ($i = 0; $i < $total; $i++) {
        $r = $rows[$i];
        $programas[] = [
            'hora_inicio' => $r['hora_inicio'],
            'hora_fin'    => $rows[$i + 1]['hora_inicio'] ?? '',
            'titulo'      => $r['titulo'],
            'descripcion' => $r['descripcion'],
            'enlace'      => $r['enlace'],
            'imagen'      => $r['imagen'],
            'tipo'        => $r['tipo'],
            'periodo'     => '',
            'en_vivo'     => $r['en_vivo'],
        ];
    }

    // mi.tv recibe el offset -360 en la URL, devuelve tiempos en UTC-6 (Honduras)
    $data = ['canal' => $canal_id, 'tz_source' => -360, 'programas' => $programas];
    $out  = __DIR__ . '/../data/programas/' . $canal_id . '.json';
    file_put_contents($out, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    echo "Guardado: $out (" . count($programas) . " programas)\n";
}
