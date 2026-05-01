<?php
/**
 * StreamHub - Página de reproducción de canal (canal.php)
 */
$channelId  = (int) get('id', '0');
$iframeUrl  = '';
$fuenteData = null;
$fuentes    = [];
$canalViews = 0;

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

            // Incrementar vistas del canal padre
            $canalId = (int)$fuenteData['canal'];
            if ($canalId > 0) {
                $stmtV = $conn->prepare("UPDATE canales SET views = views + 1 WHERE id = ?");
                $stmtV->bind_param('i', $canalId);
                $stmtV->execute();
                $stmtV->close();
                $rowV = $conn->query("SELECT views FROM canales WHERE id = {$canalId} LIMIT 1")->fetch_assoc();
                $canalViews = (int)($rowV['views'] ?? 0);
            }
        }
    } catch (Throwable $e) {
        // Error de BD, el iframe quedará vacío
    }
}
$jsCanal = json_encode($fuenteData['canal'] ?? '');

// Estado de interacciones del usuario logueado
$isLoggedIn = isLoggedIn();
$initLike   = false;
$initSave   = false;

if ($isLoggedIn && $channelId > 0) {
    try {
        $uid    = userId();
        $connI  = getDBConnection();

        $chkL = $connI->prepare("SELECT id FROM canal_likes WHERE user_id = ? AND fuente_id = ? LIMIT 1");
        $chkL->bind_param('ii', $uid, $channelId);
        $chkL->execute();
        $initLike = $chkL->get_result()->num_rows > 0;
        $chkL->close();

        $chkS = $connI->prepare("SELECT id FROM canal_guardados WHERE user_id = ? AND fuente_id = ? LIMIT 1");
        $chkS->bind_param('ii', $uid, $channelId);
        $chkS->execute();
        $initSave = $chkS->get_result()->num_rows > 0;
        $chkS->close();
    } catch (Throwable $e) { /* ignore */ }
}

// Partido context
$partidoId      = (int) get('partido', '0');
$partidoData    = null;
$canalesPartido = [];

if ($partidoId > 0) {
    $jsonPath = __DIR__ . '/../data/matches.json';
    if (file_exists($jsonPath)) {
        $allMatches = json_decode(file_get_contents($jsonPath), true) ?? [];
        foreach ($allMatches as $m) {
            if ((int)($m['id'] ?? 0) === $partidoId) {
                $partidoData = $m;
                break;
            }
        }
    }
    if ($partidoData) {
        for ($x = 1; $x <= 10; $x++) {
            $cid = trim((string)($partidoData["cnl{$x}"] ?? ''));
            if ($cid === '') continue;
            $logo = !empty($partidoData["cnl{$x}Logo"])
                ? $partidoData["cnl{$x}Logo"]
                : BASE_URL . "assets/img/canales/{$cid}.png";
            $canalesPartido[] = [
                'id'     => (int)$cid,
                'nombre' => $partidoData["cnl{$x}Name"] ?? "Canal {$cid}",
                'logo'   => $logo,
            ];
        }
    }
}
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

/* Partido match card header */
.partido-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: .75rem 1rem;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  margin-bottom: .75rem;
}
.partido-meta {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .4rem;
  flex-shrink: 0;
}
.partido-league-img {
  width: 38px;
  height: 38px;
  object-fit: contain;
}
.partido-teams {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .8rem;
}
.partido-team {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .3rem;
  width: 90px;
  text-align: center;
}
.partido-team img {
  width: 38px;
  height: 38px;
  object-fit: contain;
}
.partido-team span {
  font-size: .74rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
}
.partido-vs {
  font-family: 'Space Mono', monospace;
  font-weight: 700;
  font-size: .88rem;
  color: var(--text-muted);
  flex-shrink: 0;
}

/* Inline status badges (shared style) */
.badge-live {
  font-size: .7rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 30px;
  background: rgba(239,68,68,.15);
  color: #ef4444;
  white-space: nowrap;
}
.dot-live {
  width: 6px;
  height: 6px;
  background: #ef4444;
  border-radius: 50%;
  display: inline-block;
  margin-right: 4px;
}
.badge-time {
  font-size: .7rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 30px;
  background: var(--accent-soft);
  color: var(--accent);
  white-space: nowrap;
}

/* Match source pills bar */
.match-pills-bar {
  padding: .5rem 0 .6rem;
  border-bottom: 1px solid var(--border);
  margin-top: .5rem;
}
.pills-section-label {
  display: block;
  font-size: .72rem;
  font-weight: 700;
  color: var(--text-muted);
  margin-bottom: .4rem;
  text-transform: uppercase;
  letter-spacing: .3px;
}

@media (max-width: 576px) {
  .partido-header { flex-wrap: wrap; justify-content: center; }
  .partido-meta { flex-direction: row; width: 100%; justify-content: center; }
  .partido-teams { width: 100%; }
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
    <span style="color:var(--text-secondary); font-size:0.8rem;" id="breadcrumb-name">
      <?php if ($partidoData): ?>
        <?= htmlspecialchars(($partidoData['homeTeam']['name'] ?? '') . ' vs ' . ($partidoData['awayTeam']['name'] ?? '')) ?>
      <?php else: ?>Canal<?php endif; ?>
    </span>
  </nav>

  <div class="channel-page-layout">

    <!-- REPRODUCTOR -->
    <div class="player-column">
      <?php if ($partidoData):
        $pLocal     = htmlspecialchars($partidoData['homeTeam']['name'] ?? '');
        $pVisit     = htmlspecialchars($partidoData['awayTeam']['name'] ?? '');
        $pLocalLogo = BASE_URL . 'assets/img/equipos/sf/' . ($partidoData['homeTeam']['logo'] ?? '') . '.png';
        $pVisitLogo = BASE_URL . 'assets/img/equipos/sf/' . ($partidoData['awayTeam']['logo'] ?? '') . '.png';
        $pStatus    = $partidoData['status'] ?? 'upcoming';
        $pTime      = htmlspecialchars($partidoData['time'] ?? '--:--');
        $pLeague    = (string)($partidoData['league'] ?? '');
        $pLeagueLogo = BASE_URL . "assets/img/ligas/sf/{$pLeague}.png";
      ?>
      <div class="partido-header">
        <div class="partido-meta">
          <img src="<?= $pLeagueLogo ?>" class="partido-league-img" onerror="this.style.opacity='.2'">
          <?php if ($pStatus === 'live'): ?>
            <span class="badge-live"><span class="dot-live"></span> EN VIVO</span>
          <?php else: ?>
            <span class="badge-time"><i class="fas fa-clock"></i> <span class="match-countdown" data-time="<?= htmlspecialchars($partidoData['fecha_hora'] ?? '') ?>" data-ts="<?= (int)($partidoData['timestamp'] ?? 0) ?>"><?= $pTime ?></span></span>
          <?php endif; ?>
        </div>
        <div class="partido-teams">
          <div class="partido-team">
            <img src="<?= $pLocalLogo ?>" onerror="this.style.opacity='.2'">
            <span><?= $pLocal ?></span>
          </div>
          <div class="partido-vs">vs</div>
          <div class="partido-team">
            <img src="<?= $pVisitLogo ?>" onerror="this.style.opacity='.2'">
            <span><?= $pVisit ?></span>
          </div>
        </div>
      </div>
      <span id="player-channel-name" style="display:none">Cargando...</span>
      <?php else: ?>
      <div class="player-header">
        <span class="player-channel-name" id="player-channel-name">Cargando...</span>
        <div class="live-pill">EN VIVO</div>
      </div>
      <?php endif; ?>
      <div class="player-iframe-wrapper" style="position:relative;">
        <div class="player-placeholder" id="player-placeholder">
          <div class="player-placeholder-icon"><i class="fas fa-play-circle"></i></div>
          <p style="font-size:0.85rem; color:var(--text-muted);">Cargando stream...</p>
        </div>
        <?php if (!isAdmin()): ?>
        <script>(function(s){s.dataset.zone='10948835',s.src='https://nap5k.com/tag.min.js'})([document.documentElement, document.body].filter(Boolean).pop().appendChild(document.createElement('script')))</script>
        <?php $adUrl = 'https://brightcloudmeadow.com/uxhpnd3cub?key=72bd01849ba6d6f544a624b5a17ccad8'; ?>
        <a id="ad-fake-player" href="<?= htmlspecialchars($adUrl) ?>" target="_blank" rel="noopener noreferrer"
           style="position:absolute;inset:0;background:#000;display:flex;align-items:center;justify-content:center;z-index:10;cursor:pointer;">
          <i class="fas fa-play-circle" style="font-size:6rem;color:rgba(255,255,255,.9);pointer-events:none;"></i>
        </a>
        <script>
        document.getElementById('ad-fake-player').addEventListener('click', function() {
          this.remove();
        });
        </script>
        <?php endif; ?>
        <iframe id="player-iframe" src="<?= htmlspecialchars($iframeUrl) ?>" allowfullscreen
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          style="display:<?= !empty($iframeUrl) ? 'block' : 'none' ?>;"></iframe>
      </div>

      <?php if (!empty($canalesPartido)): ?>
      <div class="match-pills-bar">
        <span class="pills-section-label"><i class="fas fa-broadcast-tower me-1"></i>Fuentes del partido</span>
        <div class="source-pills-row">
          <?php foreach ($canalesPartido as $mc): ?>
          <a href="<?= url('canal', ['id' => $mc['id'], 'partido' => $partidoId]) ?>"
             class="source-pill<?= $mc['id'] === $channelId ? ' active' : '' ?>">
            <?= htmlspecialchars($mc['nombre']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

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
          <button class="btn-interact<?= $isLoggedIn && $initLike ? ' active' : '' ?>" data-action="love">
            <i class="fas fa-heart"></i><span>Me gusta</span>
          </button>
          <button class="btn-interact<?= $isLoggedIn && $initSave ? ' active' : '' ?>" data-action="save">
            <i class="fas fa-bookmark"></i><span>Guardar</span>
          </button>
          <button class="btn-interact" data-action="report">
            <i class="fas fa-flag"></i><span>Reportar</span>
          </button>
        </div>
        <?php if (count($fuentes) > 1): ?>
        <div class="source-pills-row">
        <?php if ($partidoId > 0): ?>
          <span class="pills-section-label" style="width:100%;"><i class="fas fa-satellite-dish me-1"></i>Otras fuentes del canal</span>
        <?php endif; ?>
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
const CHANNEL_ID   = <?= $channelId ?>;
const CANAL        = <?= $jsCanal ?>;
const PARTIDO_ID   = <?= $partidoId ?>;
const CANAL_VIEWS  = <?= $canalViews ?>;
const IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
const INIT_LIKE    = <?= $initLike   ? 'true' : 'false' ?>;
const INIT_SAVE    = <?= $initSave   ? 'true' : 'false' ?>;
</script>

<!-- Modal: Reportar canal -->
<div class="modal fade" id="modal-reportar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--bg-card); border:1px solid var(--border); border-radius:16px;">
      <div class="modal-header" style="border-bottom:1px solid var(--border); padding:1rem 1.25rem;">
        <h5 class="modal-title" style="font-family:'Space Mono',monospace; font-size:.9rem; color:var(--text-primary);">
          <i class="fas fa-flag me-2" style="color:#ef4444;"></i>Reportar canal
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:1.25rem;">
        <p style="font-size:.82rem; color:var(--text-muted); margin-bottom:1rem;">
          Cuéntanos qué está pasando. Tu reporte ayuda a mejorar el servicio.
        </p>
        <textarea id="report-comment" rows="3"
          style="width:100%; background:var(--bg-input); border:1px solid var(--border); color:var(--text-primary); border-radius:10px; padding:.65rem .9rem; font-size:.85rem; resize:vertical; outline:none;"
          placeholder="Describe el problema (opcional)..."></textarea>
        <div id="report-status" style="font-size:.78rem; margin-top:.5rem; color:var(--text-muted); display:none;"></div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--border); padding:.75rem 1.25rem; gap:.5rem;">
        <button type="button" data-bs-dismiss="modal"
          style="background:transparent; border:1px solid var(--border); color:var(--text-muted); padding:.4rem 1rem; border-radius:8px; font-size:.82rem; cursor:pointer;">
          Cancelar
        </button>
        <button type="button" id="btn-submit-report"
          style="background:rgba(239,68,68,.15); color:#ef4444; border:1px solid rgba(239,68,68,.3); padding:.4rem 1.2rem; border-radius:8px; font-size:.82rem; font-weight:600; cursor:pointer;">
          <i class="fas fa-flag me-1"></i>Enviar reporte
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Envío del reporte
document.addEventListener('DOMContentLoaded', function () {
  var btn = document.getElementById('btn-submit-report');
  if (!btn) return;

  btn.addEventListener('click', async function () {
    var comentario = (document.getElementById('report-comment').value || '').trim();
    var statusEl   = document.getElementById('report-status');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Enviando...';
    statusEl.style.display = 'none';

    var fd = new FormData();
    fd.append('action',    'report');
    fd.append('fuente_id', CHANNEL_ID);
    fd.append('comentario', comentario);

    try {
      var res  = await fetch('api/interacciones.php', { method: 'POST', body: fd });
      var data = await res.json();
      if (data.ok) {
        bootstrap.Modal.getInstance(document.getElementById('modal-reportar')).hide();
        document.getElementById('report-comment').value = '';
        document.querySelector('.btn-interact[data-action="report"]').classList.add('active');
        showToast('⚠️ Reporte enviado. ¡Gracias!');
      } else {
        statusEl.textContent = data.msg || 'Error al enviar el reporte.';
        statusEl.style.display = 'block';
      }
    } catch (e) {
      statusEl.textContent = 'Error de conexión. Intenta de nuevo.';
      statusEl.style.display = 'block';
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-flag me-1"></i>Enviar reporte';
  });
});
</script>

<script>
document.querySelectorAll('.source-pill[data-id]').forEach(function(pill) {
  pill.addEventListener('click', function() {
    var id   = this.dataset.id;
    var tipo = this.dataset.tipo;

    // Activar pill seleccionado (solo entre canal pills)
    document.querySelectorAll('.source-pill[data-id]').forEach(function(p) {
      p.classList.remove('active');
    });
    this.classList.add('active');

    // Actualizar iframe sin recargar la página
    var iframe = document.getElementById('player-iframe');
    iframe.src = 'pages/reproductor.php?id=' + id +
                 '&canal=' + encodeURIComponent(CANAL) +
                 '&tipo=' + tipo;

    // Actualizar ?id= en la URL sin navegación (preservar partido si existe)
    var url = new URL(window.location.href);
    url.searchParams.set('id', id);
    history.replaceState(null, '', url.toString());
  });
});
</script>

<script>
// Countdown del partido (solo se ejecuta cuando hay partido activo)
if (PARTIDO_ID) {
  (function () {
    function updateCountdown(el) {
      const ts = parseInt(el.dataset.ts, 10);
      let distance;
      if (ts > 0) {
        distance = ts * 1000 - Date.now();
      } else {
        const timeStr = el.dataset.time;
        if (!timeStr) return;
        const target = new Date(timeStr.replace(' ', 'T'));
        if (isNaN(target)) return;
        distance = target - Date.now();
      }
      const badge = el.closest('.badge-time');
      if (distance < 0) {
        if (distance > -10800000) {
          el.textContent = '● EN VIVO';
          if (badge) {
            badge.classList.remove('badge-time');
            badge.classList.add('badge-live');
            const icon = badge.querySelector('i');
            if (icon) icon.remove();
          }
        } else {
          el.textContent = 'Finalizó';
        }
        return;
      }
      const d = Math.floor(distance / 86400000);
      const h = Math.floor((distance % 86400000) / 3600000);
      const m = Math.floor((distance % 3600000) / 60000);
      const s = Math.floor((distance % 60000) / 1000);
      if      (d === 1)             el.textContent = 'Mañana';
      else if (d > 1  && d < 7)    el.textContent = `${d}d ${h}h`;
      else if (d >= 7  && d < 14)  el.textContent = 'Próx. Semana';
      else if (d >= 14 && d < 21)  el.textContent = '2 Semanas';
      else if (d >= 21 && d < 28)  el.textContent = '3 Semanas';
      else if (d >= 28 && d < 60)  el.textContent = 'Próx. Mes';
      else if (d >= 60 && d < 90)  el.textContent = '2 Meses';
      else if (d >= 90 && d < 120) el.textContent = '3 Meses';
      else if (d === 0 && h > 0)   el.textContent = `${h}h ${m}m ${s}s`;
      else if (h === 0 && m > 0)   el.textContent = `${m}m ${s}s`;
      else                          el.textContent = `${s}s`;
    }
    document.querySelectorAll('.match-countdown').forEach(el => {
      updateCountdown(el);
      setInterval(() => updateCountdown(el), 1000);
    });
  })();
}

document.addEventListener('DOMContentLoaded', () => {
  if (PARTIDO_ID) return; // breadcrumb already set server-side
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
