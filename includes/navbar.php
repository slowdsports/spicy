<?php
/**
 * StreamHub - Navbar compartida
 * Detecta sesión para mostrar: "Iniciar sesión" / nombre de usuario + opciones
 */
$currentPage = $_GET['p'] ?? 'home';
$currentType = $_GET['type'] ?? '';

// Deportes en el menú (añadir aquí para ampliar)
$sports = [
    'soccer'     => ['icon' => 'fa-futbol',                   'label' => 'Fútbol'],
    'basketball' => ['icon' => 'fa-basketball',               'label' => 'Básquet'],
    'tennis'     => ['icon' => 'fa-table-tennis-paddle-ball', 'label' => 'Tenis'],
    'baseball'   => ['icon' => 'fa-baseball',                 'label' => 'Béisbol'],
];

$loggedIn = isLoggedIn();
$admin    = isAdmin();
$uName    = userName();
?>
<nav class="streamhub-navbar navbar navbar-expand-lg">
  <div class="container">

    <a href="<?= url('home') ?>" class="navbar-logo">
      <div class="logo-icon">
        <i class="fas fa-play" style="color:white; font-size:12px; margin-left:2px;"></i>
      </div>
      Tele<span class="logo-dot">Deportes</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
      <i class="fas fa-bars" style="color: var(--text-primary);"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav mx-auto gap-1">

        <li class="nav-item">
          <a href="<?= url('home') ?>" class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>">
            <i class="fas fa-home me-1"></i> Inicio
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= url('eventos') ?>" class="nav-link <?= $currentPage === 'eventos' ? 'active' : '' ?>">
            <i class="fas fa-trophy me-1"></i> Eventos
          </a>
        </li>

        <li style="display: none;" class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $currentPage === 'eventos' ? 'active' : '' ?>"
             href="#" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-trophy me-1"></i> Deportes
          </a>
          <ul class="dropdown-menu sh-dropdown">
            <?php foreach ($sports as $type => $sport): ?>
            <li>
              <a class="dropdown-item <?= ($currentPage === 'eventos' && $currentType === $type) ? 'active' : '' ?>"
                 href="<?= url('eventos', ['type' => $type]) ?>">
                <i class="fas <?= $sport['icon'] ?> me-2" style="color:var(--accent); width:16px;"></i>
                <?= $sport['label'] ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </li>

        <li class="nav-item">
          <a href="<?= url('tv') ?>" class="nav-link <?= $currentPage === 'tv' ? 'active' : '' ?>">
            <i class="fas fa-tv me-1"></i> Canales
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= url('mundial2026') ?>"
             class="nav-link nav-mundial <?= $currentPage === 'mundial2026' ? 'active-mundial' : '' ?>">
            <i class="fas fa-trophy me-1"></i> Mundial 2026
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= url('donaciones') ?>" class="nav-link <?= $currentPage === 'donaciones' ? 'active' : '' ?>">
            <i class="fas fa-coffee me-1"></i> Donaciones
          </a>
        </li>

      </ul>

      <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
        <?php if ($loggedIn): ?>
        <button class="btn-theme-toggle" id="btn-push-toggle" title="Activar notificaciones de tus equipos favoritos" style="display:none;">
          <i class="fas fa-bell" id="push-bell-icon"></i>
        </button>
        <?php endif; ?>

        <button class="btn-theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
          <i class="fas fa-sun" id="theme-icon"></i>
        </button>

        <?php if ($loggedIn): ?>
          <div class="dropdown">
            <button class="btn-login d-flex align-items-center gap-2 dropdown-toggle"
                    data-bs-toggle="dropdown" style="border:none; cursor:pointer;">
              <i class="fas fa-user-circle"></i>
              <span style="max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                <?= htmlspecialchars($uName) ?>
              </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end sh-dropdown">
              <?php if ($admin): ?>
              <li>
                <a class="dropdown-item" href="<?= BASE_URL ?>admin/">
                  <i class="fas fa-shield-alt me-2" style="color:var(--accent);"></i>
                  Panel Admin
                </a>
              </li>
              <li><hr class="dropdown-divider" style="border-color:var(--border);"></li>
              <?php endif; ?>
              <li>
                <a class="dropdown-item" href="<?= BASE_URL ?>api/auth.php?action=logout_redirect">
                  <i class="fas fa-sign-out-alt me-2" style="color:#ef4444;"></i>
                  Cerrar sesión
                </a>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <?php
            $currentPage = $_GET['p'] ?? 'home';
            $navLoginHref = url('login');
            if ($currentPage !== 'login' && !empty($_SERVER['QUERY_STRING'])) {
                $navLoginHref .= '&redirect=' . urlencode('?' . $_SERVER['QUERY_STRING']);
            }
          ?>
          <a href="<?= $navLoginHref ?>" class="nav-link btn-login px-3">
            <i class="fas fa-user me-1"></i> Iniciar sesión
          </a>
        <?php endif; ?>
      </div>
    </div>

  </div>
</nav>

<style>
.sh-dropdown {
  background: var(--bg-card) !important;
  border: 1px solid var(--border) !important;
  border-radius: 12px !important;
  padding: 0.5rem !important;
  box-shadow: var(--shadow) !important;
  margin-top: 0.35rem !important;
}
.sh-dropdown .dropdown-item {
  border-radius: 8px; font-size: 0.85rem;
  color: var(--text-secondary); padding: 0.45rem 1rem;
  transition: var(--transition);
}
.sh-dropdown .dropdown-item:hover { background: var(--accent-soft); color: var(--accent); }
.sh-dropdown .dropdown-item.active { background: var(--accent); color: white; }

/* Link dorado del Mundial */
.nav-mundial {
  color: #d97706 !important;
  font-weight: 700 !important;
  position: relative;
}
.nav-mundial i { color: #f59e0b; }
.nav-mundial:hover {
  color: #f59e0b !important;
  background: rgba(245,158,11,.12) !important;
}
.nav-mundial.active-mundial {
  color: #f59e0b !important;
  background: rgba(245,158,11,.15) !important;
}
</style>
