<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require 'scraper.php'; // Ruta al archivo simple_html_dom.php
require '../inc/conn.php';

// Array de IDs de canales
$query = "SELECT fuentes.fuenteId, fuentes.fuenteNombre, fuentes.epgCanal, fuentes.pais, paises.paisId, canales.canalId, canales.canalImg, paises.paisNombre, paises.paisCodigo, categorias.categoriaId, categorias.categoriaNombre FROM fuentes
INNER JOIN paises ON fuentes.pais = paises.paisId
INNER JOIN canales ON fuentes.canal = canales.canalId
INNER JOIN categorias ON canales.canalCategoria = categorias.categoriaId
WHERE epgCanal IS NOT NULL
AND pais IN(12, 48, 437)";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conn));
}

$canales = [];

while ($row = mysqli_fetch_assoc($result)) {
    $canalId = $row['canalId'];
    $fuenteId = $row['fuenteId'];
    $fuenteNombre = $row['fuenteNombre'];
    $fuenteLogo = $row['canalImg'];
    $fuenteCategoria = $row['categoriaNombre'];
    $epgCanal = $row['epgCanal'];
    $paisNombre = strtolower($row['paisCodigo']);
    $paisFlag = strtolower($row['paisNombre']);
    $url_base = 'https://mi.tv/' . $paisFlag . '/async/channel/';
    $url = $url_base . $epgCanal . '/-360/';
    $canales[] = [
        'id' => $fuenteId,
        'canal' => $canalId,
        'nombre' => $fuenteNombre,
        'logo' => $fuenteLogo,
        'categoria' => $fuenteCategoria,
        'epgCanal' => $epgCanal,
        'pais' => $paisNombre,
        'flag' => $paisFlag,
        'url' => $url
    ];
}
var_dump($canales);
//exit();

// Array para almacenar los datos de todos los canales
$canales_data = [];

// Recorremos cada canal
foreach ($canales as $canal) {
    $url = $canal['url'];
    $html = file_get_html($url);

    if (!$html) {
        echo "Error al obtener HTML de: " . $url . "\n";
        continue;
    }

    // Array para almacenar los datos de programación de un canal
    $programas = [];

    // Buscar el elemento <li> con la clase "ongoing"
    $ongoing_program = $html->find('li.ongoing', 0);
    
    if ($ongoing_program) {
        $titulo = trim($ongoing_program->find('h2', 0)->plaintext ?? '');
        $descripcion = trim($ongoing_program->find('p.synopsis', 0)->plaintext ?? '');
        $imagen = $ongoing_program->find('div.image', 0)->style ?? '';
        preg_match("/url\('(.+?)'\)/", $imagen, $matches);
        $imagen = $matches[1] ?? '';

        // Añadir los datos al array de programas
        $programas[] = [
            'id' => $canal['id'],
            'canal' => $canalId,
            'nombre' => $canal['nombre'],
            'logo' => $canal['logo'],
            'categoria' => $canal['categoria'],
            'epgCanal' => $canal['epgCanal'],
            'pais' => $canal['pais'],
            'flag' => $canal['flag'],
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'imagen' => $imagen
        ];
    }

    // Guardar los datos del canal en el array de todos los canales
    $canales_data[$canal['epgCanal']] = $programas;
}

// Guardar los datos en un archivo JSON único
file_put_contents('../inc/componentes/guia/json/programacion.json', json_encode($canales_data, JSON_PRETTY_PRINT));

echo "Datos guardados en programacion.json\n";
