<?php
/**
 * StreamHub Admin - Router principal
 * Todas las páginas del panel pasan aquí: admin/?p=home
 *
 * PROTECCIÓN: Solo usuarios con rol "admin" pueden acceder.
 */

// Cargar configuración global (define BASE_URL, helpers, inicia sesión)
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// ── Guardia de autenticación ──────────────────────────────────
// Si no es admin, redirigir al login del admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit();
}

// ── Página solicitada ─────────────────────────────────────────
$page = isset($_GET['p']) ? preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['p'])) : 'home';

$allowed = ['home', 'canales', 'fuentes', 'ligas', 'partidos', 'config', 'reportes', 'usuarios'];
if (!in_array($page, $allowed)) {
    $page = 'home';
}

// Título de cada sección
$titles = [
    'home'     => 'Dashboard',
    'canales'  => 'Canales',
    'fuentes'  => 'Fuentes',
    'ligas'    => 'Ligas',
    'partidos' => 'Partidos',
    'config'   => 'Configuración',
    'reportes'  => 'Reportes',
    'usuarios'  => 'Usuarios',
];
$pageTitle = $titles[$page] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> · Tele Deportes Admin</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Estilos del sitio principal (variables de color, etc.) -->
  <link rel="stylesheet" href="../assets/css/style.css">
  <!-- Estilos específicos del admin -->
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">

<!-- ============================================================
     LAYOUT: Sidebar + Main
     ============================================================ -->
<div class="admin-layout">

  <!-- ── SIDEBAR ─────────────────────────────────────────────── -->
  <?php require __DIR__ . '/includes/sidebar.php'; ?>

  <!-- ── CONTENIDO PRINCIPAL ──────────────────────────────────── -->
  <div class="admin-main">

    <!-- Topbar -->
    <?php require __DIR__ . '/includes/topbar.php'; ?>

    <!-- Área de contenido -->
    <div class="admin-content">
      <?php require __DIR__ . '/pages/' . $page . '.php'; ?>
    </div>

  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Tema -->
<script src="../assets/js/theme.js"></script>
<!-- JS del admin (funciones CRUD compartidas) -->
<script src="assets/js/admin.js"></script>

</body>
</html>
