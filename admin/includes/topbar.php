<?php
/**
 * StreamHub Admin - Topbar
 */
$titles = [
    'home'     => ['icon' => 'fa-gauge',        'label' => 'Dashboard'],
    'canales'  => ['icon' => 'fa-tv',           'label' => 'Canales'],
    'fuentes'  => ['icon' => 'fa-broadcast-tower', 'label' => 'Fuentes'],
    'ligas'    => ['icon' => 'fa-trophy',       'label' => 'Ligas'],
    'partidos' => ['icon' => 'fa-futbol',       'label' => 'Partidos'],
    'config'   => ['icon' => 'fa-gear',         'label' => 'Configuración'],
];
$cp = $_GET['p'] ?? 'home';
$t  = $titles[$cp] ?? $titles['home'];
?>
<header class="admin-topbar">

  <!-- Título de la sección actual -->
  <div class="admin-topbar-title">
    <i class="fas <?= $t['icon'] ?>" style="color:var(--accent);"></i>
    <span><?= $t['label'] ?></span>
  </div>

  <!-- Lado derecho: tema + usuario -->
  <div class="d-flex align-items-center gap-3">

    <!-- Toggle tema -->
    <button class="btn-theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
      <i class="fas fa-sun" id="theme-icon"></i>
    </button>

    <!-- Usuario actual -->
    <div class="d-flex align-items-center gap-2"
         style="font-size:0.85rem; color:var(--text-secondary);">
      <i class="fas fa-user-shield" style="color:var(--accent);"></i>
      <span><?= htmlspecialchars(userName()) ?></span>
    </div>

    <!-- Cerrar sesión -->
    <a href="<?= BASE_URL ?>api/auth.php?action=logout_redirect"
       class="btn-interact" style="font-size:0.78rem; padding:0.3rem 0.75rem; text-decoration:none;">
      <i class="fas fa-sign-out-alt"></i>
    </a>

  </div>
</header>
