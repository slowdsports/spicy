<?php
/**
 * StreamHub - Página de inicio (home.php)
 */

// Canales guardados del usuario (JSON por usuario para evitar query en cada carga)
$savedJsonUrl = null;
if (isLoggedIn()) {
    $uid       = userId();
    $savedFile = __DIR__ . '/../data/guardados/' . $uid . '.json';
    if (file_exists($savedFile)) {
        $savedJsonUrl = BASE_URL . 'data/guardados/' . $uid . '.json';
    }
}

$maintenance = 0;
$maintenanceLabel = 'Sistema operativo';
$maintenanceStyle = 'background: var(--accent-soft); border: 1px solid var(--border-accent); color: var(--text-secondary);';

if (function_exists('getDBConnection')) {
    $conn = getDBConnection();
    $result = $conn->query("SELECT valor FROM config_sitio WHERE clave = 'mantenimiento' LIMIT 1");
    if ($result && ($row = $result->fetch_assoc())) {
        $maintenance = (int) $row['valor'];
    }
}

if ($maintenance === 1) {
    $maintenanceLabel = 'Sistema en mantenimiento';
    $maintenanceStyle = 'background: #fef3c7; border: 1px solid #fde68a; color: #92400e;';
}
?>

<!-- HERO BANNER -->
<section style="background: var(--bg-secondary); padding: 2.5rem 0; border-bottom: 1px solid var(--border);">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <h1 style="font-family: 'Space Mono', monospace; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.3rem;">
          TV en Vivo <span style="color: var(--accent);">& Deportes</span>
        </h1>
        <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0;">
          Transmisión en alta calidad · Eventos en vivo · Sin interrupciones
        </p>
      </div>
      <div style="display:flex; align-items:center; gap:8px; padding: 0.5rem 1rem; border-radius: 100px; <?= $maintenanceStyle ?>">
        <span style="width:8px; height:8px; background:<?= $maintenance === 1 ? '#d97706' : '#22c55e' ?>; border-radius:50%; animation: pulse-badge 2s infinite; flex-shrink:0;"></span>
        <span style="font-size:0.8rem; font-weight:600;"><?= htmlspecialchars($maintenanceLabel) ?></span>
      </div>
    </div>
  </div>
</section>

<!-- SECCIÓN 1: PARTIDOS EN VIVO -->
<section class="matches-section" id="matches-section">
  <div class="container">
    <div class="section-title">
      <span>Partidos de Hoy</span>
      <span class="section-subtitle">
        <i class="fas fa-circle" style="color:#ef4444; font-size:0.5rem; margin-right:4px;"></i>
        En vivo y próximos
      </span>
    </div>
    <div class="matches-slider-wrapper" style="position:relative; padding:0 10px;">
      <button class="slider-arrow slider-arrow-left" onclick="scrollSlider('left')">
        <i class="fas fa-chevron-left"></i>
      </button>
      <div class="matches-slider" id="matches-slider">
        <!-- Skeleton de carga -->
        <div style="display:flex; gap:1rem;">
          <div class="match-card" style="opacity:0.4;">
            <div style="height:12px; background:var(--border); border-radius:4px; margin-bottom:10px;"></div>
            <div style="height:60px; background:var(--border); border-radius:8px;"></div>
          </div>
        </div>
      </div>
      <button class="slider-arrow slider-arrow-right" onclick="scrollSlider('right')">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</section>

<?php if (isLoggedIn()): ?>
<script>const SAVED_JSON_URL = <?= $savedJsonUrl ? json_encode($savedJsonUrl) : 'null' ?>;</script>
<!-- SECCIÓN 1.5: MIS GUARDADOS (visible solo si el usuario tiene guardados) -->
<section id="saved-section" style="display:none; padding:1.5rem 0; border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="section-title">
      <span>Mis Guardados</span>
      <span class="section-subtitle">
        <i class="fas fa-bookmark" style="color:var(--accent); margin-right:4px;"></i>Quick Access
      </span>
    </div>
    <div class="channels-grid" id="saved-channels-grid"></div>
  </div>
</section>
<?php endif; ?>

<!-- SECCIÓN 2: CANALES -->
<section class="channels-section" id="channels-section">
  <div class="container">
    <div class="section-title">
      <span>Canales</span>
      <span class="section-subtitle"><i class="fas fa-bolt" style="color:#fbbf24; margin-right:4px;"></i>Top Channels</span>
    </div>
    <div style="display: none" class="search-wrapper">
      <i class="fas fa-search search-icon"></i>
      <input type="text" id="channel-search" class="search-input" placeholder="Buscar canal..." autocomplete="off">
    </div>
    <div style="display: none;" class="category-pills" id="category-pills"></div>
    <div class="channels-grid" id="channels-grid"></div>
  </div>
</section>

<!-- SECCIÓN 3: PROGRAMAS EN VIVO -->
<section class="section-programs" id="programs-section" style="padding:2rem 0;">
  <div class="container">
    <div class="section-title">
      <span>Programas en vivo ahora</span>
      <span class="section-subtitle">
        <i class="fas fa-circle" style="color:#ef4444; font-size:0.5rem; margin-right:4px;"></i>
        Guía de TV
      </span>
    </div>
    <div id="programs-list"></div>
  </div>
</section>
