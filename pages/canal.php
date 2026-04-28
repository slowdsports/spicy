<?php
/**
 * StreamHub - Página de reproducción de canal (canal.php)
 */
$channelId = (int) get('id', '0');
$iframeUrl = '';
$fuenteData = null;
$fuentes = [];

if ($channelId > 0) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, nombre, canal, tipo FROM fuentes WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $channelId);
        $stmt->execute();
        $result = $stmt->get_result();
        $fuenteData = $result->fetch_assoc();
        $stmt->close();

        if ($fuenteData) {
            $iframeUrl = 'pages/reproductor.php?' . http_build_query([
                'id' => $fuenteData['id'],
                'canal' => $fuenteData['canal'],
                'tipo' => $fuenteData['tipo']
            ]);

            $stmt2 = $conn->prepare("SELECT id, nombre, tipo FROM fuentes WHERE canal = ? ORDER BY id");
            $stmt2->bind_param('s', $fuenteData['canal']);
            $stmt2->execute();
            $fuentes = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt2->close();
        }
    } catch (Throwable $e) {
        // Error de BD, el iframe quedará vacío
    }
}
$jsCanal = json_encode($fuenteData['canal'] ?? '');
?>

<style>
.source-pills-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  flex-basis: 100%;
  width: 100%;
  padding-top: 4px;
}

.source-pill {
  padding: 4px 14px;
  border-radius: 20px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  font-size: 0.78em;
  font-weight: 500;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s, background 0.15s;
  white-space: nowrap;
}

.source-pill:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.source-pill.active {
  background: var(--accent);
  border-color: var(--accent);
  color: #fff;
}
</style>

<div class="container" style="padding-top:1.5rem;">
  <!-- Breadcrumb -->
  <nav style="margin-bottom:1rem;">
    <a href="<?= url('home') ?>" style="color:var(--text-muted); font-size:0.8rem; text-decoration:none;">
      <i class="fas fa-home me-1"></i> Inicio
    </a>
    <span style="color:var(--text-muted); margin:0 0.5rem; font-size:0.8rem;">/</span>
    <a href="<?= url('tv') ?>" style="color:var(--text-muted); font-size:0.8rem; text-decoration:none;">Canales</a>
    <span style="color:var(--text-muted); margin:0 0.5rem; font-size:0.8rem;">/</span>
    <span style="color:var(--text-secondary); font-size:0.8rem;" id="breadcrumb-name">Canal</span>
  </nav>

  <div class="channel-page-layout">

    <!-- REPRODUCTOR -->
    <div class="player-column">
      <div class="player-header">
        <span class="player-channel-name" id="player-channel-name">Cargando...</span>
        <div class="live-pill">EN VIVO</div>
      </div>
      <div class="player-iframe-wrapper">
        <div class="player-placeholder" id="player-placeholder">
          <div class="player-placeholder-icon"><i class="fas fa-play-circle"></i></div>
          <p style="font-size:0.85rem; color:var(--text-muted);">Cargando stream...</p>
        </div>
        <iframe id="player-iframe" src="<?= htmlspecialchars($iframeUrl) ?>" allowfullscreen
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          style="display:<?= !empty($iframeUrl) ? 'block' : 'none' ?>;"></iframe>
      </div>

      <div class="channel-info-bar">
        <div class="channel-info-left">
          <div class="channel-avatar">
            <img src="" alt="Canal" id="channel-avatar-img">
          </div>
          <div class="channel-title-group">
            <h2 id="channel-title">Canal</h2>
            <div class="channel-views" id="channel-views">
              <i class="fas fa-eye me-1" style="font-size:0.75rem;"></i> Cargando...
            </div>
          </div>
        </div>
        <div class="interaction-buttons">
          <button class="btn-interact" data-action="love">
            <i class="fas fa-heart"></i><span>Me gusta</span>
          </button>
          <button class="btn-interact" data-action="save">
            <i class="fas fa-bookmark"></i><span>Guardar</span>
          </button>
          <button class="btn-interact" data-action="report">
            <i class="fas fa-flag"></i><span>Reportar</span>
          </button>
        </div>
        <?php if (count($fuentes) > 1): ?>
        <div class="source-pills-row">
          <?php foreach ($fuentes as $i => $f): ?>
          <button
            class="source-pill<?= $f['id'] == $channelId ? ' active' : '' ?>"
            data-id="<?= (int) $f['id'] ?>"
            data-tipo="<?= (int) $f['tipo'] ?>">
            <?= htmlspecialchars($f['nombre'] ?: 'Fuente ' . ($i + 1)) ?>
          </button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- CHAT -->
    <div class="chat-column">
      <div class="chat-header">
        <div class="chat-title">
          <i class="fas fa-comments" style="color:var(--accent);"></i> Chat en vivo
        </div>
        <span class="chat-users-count" id="chat-users">
          <i class="fas fa-users" style="font-size:0.65rem; margin-right:3px;"></i> %users% viendo
        </span>
      </div>
      <div class="chat-messages" id="chat-messages"></div>
      <div class="chat-input-area">
        <input type="text" class="chat-input" placeholder="Inicia sesión para chatear..." disabled>
      </div>
    </div>

  </div>
</div>

<!-- CANALES RECOMENDADOS -->
<section class="recommended-section" style="border-top:1px solid var(--border); margin-top:1rem; background:var(--bg-secondary);">
  <div class="container">
    <div class="section-title">
      <span>Canales Recomendados</span>
      <span class="section-subtitle">También en vivo</span>
    </div>
    <div style="position:relative; padding:0 10px;">
      <button class="slider-arrow slider-arrow-left" onclick="scrollRecommended('left')">
        <i class="fas fa-chevron-left"></i>
      </button>
      <div class="matches-slider" id="recommended-slider"></div>
      <button class="slider-arrow slider-arrow-right" onclick="scrollRecommended('right')">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</section>

<script>
const CHANNEL_ID = <?= $channelId ?>;
const CANAL      = <?= $jsCanal ?>;
</script>

<script>
document.querySelectorAll('.source-pill').forEach(function(pill) {
  pill.addEventListener('click', function() {
    var id   = this.dataset.id;
    var tipo = this.dataset.tipo;

    // Activar pill seleccionado
    document.querySelectorAll('.source-pill').forEach(function(p) {
      p.classList.remove('active');
    });
    this.classList.add('active');

    // Actualizar iframe sin recargar la página
    var iframe = document.getElementById('player-iframe');
    iframe.src = 'pages/reproductor.php?id=' + id +
                 '&canal=' + encodeURIComponent(CANAL) +
                 '&tipo=' + tipo;

    // Actualizar ?id= en la URL sin navegación
    var url = new URL(window.location.href);
    url.searchParams.set('id', id);
    history.replaceState(null, '', url.toString());
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const observer = new MutationObserver(() => {
    const name = document.getElementById('player-channel-name').textContent;
    if (name !== 'Cargando...') {
      document.getElementById('breadcrumb-name').textContent = name;
      observer.disconnect();
    }
  });
  observer.observe(document.getElementById('player-channel-name'), { childList: true });
});
</script>
