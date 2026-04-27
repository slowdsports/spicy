<?php
/**
 * StreamHub - Router principal
 * Todas las páginas pasan por aquí mediante ?p=
 */

$page = isset($_GET['p']) ? trim($_GET['p']) : 'home';

// Sanitizar: solo letras, números y guiones
$page = preg_replace('/[^a-z0-9\-]/', '', strtolower($page));

$allowed = ['home', 'tv', 'eventos', 'login', 'canal', 'liga'];

if (!in_array($page, $allowed)) {
    $page = 'home';
}

require_once 'includes/config.php';
require_once 'includes/db.php';

// Construir título dinámico
$titles = [
    'home'    => 'StreamHub - TV en Vivo & Deportes',
    'tv'      => 'Canales - StreamHub',
    'eventos' => 'Eventos - StreamHub',
    'login'   => 'Iniciar Sesión - StreamHub',
    'canal'   => 'Canal - StreamHub',
    'liga'    => 'Liga - StreamHub',
];
$pageTitle = $titles[$page] ?? 'StreamHub';
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php
// En la página de login no mostramos navbar completo
if ($page !== 'login') {
    require 'includes/navbar.php';
}

// Cargar la vista correspondiente
require "pages/{$page}.php";

if ($page !== 'login') {
    require 'includes/footer.php';
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme.js"></script>

<?php
// Scripts específicos por página
$scripts = [
    'home'    => 'assets/js/main.js',
    'tv'      => 'assets/js/channels.js',
    'canal'   => 'assets/js/channel.js',
    'eventos' => 'assets/js/eventos.js',
    'liga'    => 'assets/js/liga.js',
    'login'   => 'assets/js/auth.js',
];
if (isset($scripts[$page])) {
    echo '<script src="' . $scripts[$page] . '"></script>';
}
?>
</body>
</html>
