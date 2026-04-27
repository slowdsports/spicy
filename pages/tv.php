<?php
/**
 * StreamHub - Página de canales (tv.php)
 */
?>
<section style="background: var(--bg-secondary); padding: 2rem 0; border-bottom: 1px solid var(--border);">
  <div class="container">
    <h1 style="font-family:'Space Mono',monospace; font-size:1.4rem; font-weight:700; color:var(--text-primary); margin:0 0 0.25rem;">
      <i class="fas fa-tv me-2" style="color:var(--accent);"></i> Canales en Vivo
    </h1>
    <p style="color:var(--text-muted); font-size:0.88rem; margin:0;">Todos los canales disponibles en este momento</p>
  </div>
</section>

<section class="channels-section" style="padding-top: 2rem;">
  <div class="container">
    <div class="search-wrapper">
      <i class="fas fa-search search-icon"></i>
      <input type="text" id="channel-search" class="search-input" placeholder="Buscar canal..." autocomplete="off">
    </div>
    <div class="category-pills" id="category-pills"></div>
    <div class="channels-grid" id="channels-grid"></div>
  </div>
</section>
