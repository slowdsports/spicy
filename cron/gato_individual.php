<?php
require __DIR__ . '/scraper.php';

$canales = [
    'hbo_argentina',
    'hbo_2_latinoamerica',
    //'hbo_plus',
    //'hbo_family_latinoamerica',
    //'hbo_mundi',
    //'hbo_signature_latinoamerica',
];

$url_base = 'https://www.gatotv.com/canal/';

foreach ($canales as $canal_id) {
    $url  = $url_base . $canal_id;
    $html = file_get_html($url);

    if (!$html) {
        echo "Error al cargar: $url\n";
        continue;
    }

    $programas = [];
    $table = $html->find('table.tbl_EPG', 0);

    if (!$table) {
        echo "No se encontró la tabla EPG para: $canal_id\n";
        continue;
    }

    $current_period = '';

    foreach ($table->find('tr') as $row) {
        // Encabezado de período (Madrugada, Mañana, Tarde, Noche)
        $th = $row->find('th.tbl_EPG_th', 0);
        if ($th) {
            $text = trim($th->plaintext);
            if (in_array(mb_strtolower($text), ['madrugada', 'mañana', 'tarde', 'noche'])) {
                $current_period = $text;
            }
            continue;
        }

        // Filas de datos
        $cells = $row->find('td');
        $count = count($cells);
        if ($count < 3) continue;

        // Hora inicio / fin
        $hora_inicio = trim($cells[0]->find('time', 0)->plaintext ?? '');
        $hora_fin    = trim($cells[1]->find('time', 0)->plaintext ?? '');

        // Si hay 4 celdas: [start][end][imagen][contenido]
        // Si hay 3 celdas: [start][end][contenido] (sin imagen)
        $content_cell = ($count >= 4) ? $cells[3] : $cells[2];
        $image_cell   = ($count >= 4) ? $cells[2] : null;

        $program_div = $content_cell->find('div.tbl_EPG_ProgramsColumn', 0);
        if (!$program_div) continue;

        $title_div  = $program_div->find('div.div_program_title_on_channel', 0);
        $title_span = $title_div ? $title_div->find('span', 0) : null;
        $title_link = $title_div ? $title_div->find('a', 0)    : null;

        $titulo = $title_span
            ? trim(html_entity_decode($title_span->plaintext, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            : '';

        $enlace = $title_link ? trim($title_link->href) : '';

        // Descripción: texto del div de contenido excluyendo el bloque del título
        $inner = $program_div->innertext;
        if ($title_div) {
            $inner = str_replace($title_div->outertext, '', $inner);
        }
        $descripcion = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        // Imagen
        $imagen = '';
        if ($image_cell) {
            $img = $image_cell->find('img', 0);
            if ($img) $imagen = $img->src;
        }

        // Tipo de contenido (pelicula / programa / etc.)
        $tipo = trim((string)$content_cell->class);

        // En vivo
        $en_vivo = strpos((string)$row->class, 'tbl_EPG_row_selected') !== false;

        $programas[] = [
            'hora_inicio' => $hora_inicio,
            'hora_fin'    => $hora_fin,
            'titulo'      => $titulo,
            'descripcion' => $descripcion,
            'enlace'      => $enlace,
            'imagen'      => $imagen,
            'tipo'        => $tipo,
            'periodo'     => $current_period,
            'en_vivo'     => $en_vivo,
        ];
    }

    // GatoTV es un sitio argentino: sus horarios están en UTC-3
    $data = ['canal' => $canal_id, 'tz_source' => -180, 'programas' => $programas];
    $out  = __DIR__ . '/../data/programas/' . $canal_id . '.json';
    file_put_contents($out, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    echo "Guardado: $out (" . count($programas) . " programas)\n";
}
?>
