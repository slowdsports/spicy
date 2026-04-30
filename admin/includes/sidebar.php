<?php
/**
 * StreamHub Admin - Sidebar
 */
$currentAdminPage = $_GET['p'] ?? 'home';

$menu = [
    'home'     => ['icon' => 'fa-gauge',        'label' => 'Dashboard'],
    'canales'  => ['icon' => 'fa-tv',           'label' => 'Canales'],
    'fuentes'  => ['icon' => 'fa-broadcast-tower', 'label' => 'Fuentes'],
    'ligas'    => ['icon' => 'fa-trophy',       'label' => 'Ligas'],
    'partidos' => ['icon' => 'fa-futbol',       'label' => 'Partidos'],
    'config'   => ['icon' => 'fa-gear',         'label' => 'Configuración'],
    'reportes' => ['icon' => 'fa-flag',         'label' => 'Reportes'],
];
?>
<aside class="admin-sidebar">

  <!-- Logo del admin -->
  <div class="admin-sidebar-logo">
    <a href="<?= BASE_URL ?>admin/" style="text-decoration:none; display:flex; align-items:center; gap:8px;">
      <div class="logo-icon" style="width:28px; height:28px; font-size:10px;">
        <i class="fas fa-play" style="color:white; font-size:10px; margin-left:1px;"></i>
      </div>
      <span style="font-family:'Space Mono',monospace; font-weight:700; font-size:1rem; color:var(--text-primary);">
        Tele<span style="color:var(--accent);"> Gratuita</span>
      </span>
    </a>
    <!-- Badge Admin -->
    <span class="admin-badge">ADMIN</span>
  </div>

  <!-- Navegación -->
  <nav class="admin-nav">
    <?php foreach ($menu as $key => $item): ?>
    <a href="<?= BASE_URL ?>admin/?p=<?= $key ?>"
       class="admin-nav-item <?= $currentAdminPage === $key ? 'active' : '' ?>">
      <i class="fas <?= $item['icon'] ?>"></i>
      <span><?= $item['label'] ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Pie del sidebar: volver al sitio -->
  <div class="admin-sidebar-footer">
    <a href="<?= BASE_URL ?>?p=home" class="admin-nav-item" style="opacity:0.6;">
      <i class="fas fa-arrow-left"></i>
      <span>Ver sitio</span>
    </a>
  </div>

</aside>
