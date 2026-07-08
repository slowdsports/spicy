<?php
/**
 * StreamHub - Detalle de partido (?p=partido&id=X)
 *
 * Reemplaza el acordeón de liga.php (que solo mostraba canales) por una
 * página completa por partido: marcador en vivo, estadio, árbitro, goleadores,
 * MVP, estadísticas, timeline, tabla de posiciones, head-to-head y
 * alineaciones — todo desde FotMob (único proveedor en uso, ver
 * admin/fotmob.php) — y al final los canales de transmisión disponibles.
 *
 * Solo lo "instantáneo" (header, canales — todo desde el caché JSON local)
 * se renderiza en el servidor. El resto depende de FotMob (1-3s de espera)
 * así que se pide aparte vía assets/js/partido.js, con skeletons de por
 * medio — la página nunca deja al usuario mirando una pantalla en blanco.
 * Ver api/partido_extra.php (también descarga y cachea localmente las fotos
 * de jugadores del MVP/goleadores, para no pegarle a la CDN de FotMob en
 * cada visita).
 */

$partidoId = (int) get('id', '0');

if ($partidoId <= 0) {
    header('Location: ' . url('home'));
    exit;
}

/* ─────────────────────────────────────────────
   Datos básicos — desde el caché JSON, nunca la BD
   (mismo patrón que canal.php / liga.php)
───────────────────────────────────────────── */
$jsonPath    = __DIR__ . '/../data/matches.json';
$allMatches  = file_exists($jsonPath) ? (json_decode(file_get_contents($jsonPath), true) ?? []) : [];
$partidoData = null;
foreach ($allMatches as $m) {
    if ((int)($m['id'] ?? 0) === $partidoId) { $partidoData = $m; break; }
}

if (!$partidoData) {
    header('Location: ' . url('home'));
    exit;
}

$local     = htmlspecialchars($partidoData['homeTeam']['name'] ?? '');
$visit     = htmlspecialchars($partidoData['awayTeam']['name'] ?? '');
$localId   = (int)($partidoData['homeTeam']['logo'] ?? 0);
$visitId   = (int)($partidoData['awayTeam']['logo'] ?? 0);
$localLogo = BASE_URL . 'assets/img/equipos/' . logoFolder($localId) . "/{$localId}.png";
$visitLogo = BASE_URL . 'assets/img/equipos/' . logoFolder($visitId) . "/{$visitId}.png";
$ligaId    = (string)($partidoData['league'] ?? '');
$ligaNom   = $partidoData['leagueName'] ?? "Liga {$ligaId}";
$ligaLogo  = BASE_URL . 'assets/img/ligas/' . logoFolder($ligaId) . "/{$ligaId}.png";
$status    = $partidoData['status'] ?? 'upcoming';
$time      = htmlspecialchars($partidoData['time'] ?? '--:--');
$isLive    = $status === 'live';
$isFinished = $status === 'finished';

/* Canales de este partido */
$fuentesPath = __DIR__ . '/../data/fuentes.json';
$fuenteIosMap = [];
if (file_exists($fuentesPath)) {
    foreach (json_decode(file_get_contents($fuentesPath), true) ?? [] as $f) {
        $fuenteIosMap[(int)$f['id']] = !empty($f['ios']);
    }
}
$canales = [];
for ($x = 1; $x <= 10; $x++) {
    $cid = trim((string)($partidoData["cnl{$x}"] ?? ''));
    if ($cid === '') continue;
    $canales[] = [
        'id'     => (int)$cid,
        'nombre' => $partidoData["cnl{$x}Name"] ?? "Canal {$cid}",
        'logo'   => $partidoData["cnl{$x}Logo"] ?: (BASE_URL . "assets/img/canales/{$cid}.png"),
        'ios'    => $fuenteIosMap[(int)$cid] ?? false,
    ];
}

/* Equipos favoritos del usuario — solo para pintar la estrella activa al
   cargar; togglear se hace vía api/interacciones.php (action=save_equipo) */
$equiposFavoritos = [];
if (isLoggedIn() && ($localId || $visitId)) {
    try {
        $conn = getDBConnection();
        $uid  = userId();
        $stmt = $conn->prepare("SELECT equipo_id FROM equipo_guardados WHERE user_id = ? AND equipo_id IN (?, ?)");
        $stmt->bind_param('iii', $uid, $localId, $visitId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) { $equiposFavoritos[] = (int)$row['equipo_id']; }
        $stmt->close();
    } catch (Throwable $e) {
        // Tabla puede no existir todavía en un servidor nuevo — se auto-crea
        // en el primer POST a save_equipo; hasta entonces, sin favoritos.
    }
}
?>
<style>
.partido-page { max-width: 1200px; margin: 0 auto; padding: 1.25rem 1rem 3rem; }

.pp-header {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px;
  padding: 1.25rem; margin-bottom: 1.25rem;
}
.pp-header-top { display: flex; align-items: center; justify-content: center; gap: .6rem; margin-bottom: 1rem; }
.pp-header-top img { width: 22px; height: 22px; object-fit: contain; }
.pp-header-top .pp-league-name { font-size: .78rem; color: var(--text-muted); font-weight: 600; }

.pp-status-badge {
  font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
  padding: 3px 10px; border-radius: 100px;
}
.pp-status-live { background: rgba(239,68,68,.15); color: #ef4444; }
.pp-status-live::before { content: '●'; margin-right: 4px; animation: pulse-badge 1.4s infinite; }
.pp-status-finished { background: var(--bg-secondary); color: var(--text-muted); }
.pp-status-upcoming { background: var(--accent-soft); color: var(--accent); }

.pp-body-row { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 1.5rem; }
.pp-team { display: flex; flex-direction: column; align-items: center; gap: .6rem; width: 160px; text-align: center; }
.pp-team img { width: 56px; height: 56px; object-fit: contain; transition: opacity .2s ease; }
.pp-team-img-wrap { position: relative; display: inline-block; }
.pp-fav-star {
  position: absolute; top: -6px; right: -10px;
  width: 22px; height: 22px; border-radius: 50%;
  background: var(--bg-secondary); border: 1px solid var(--border);
  color: var(--text-muted); font-size: .7rem;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: var(--transition); padding: 0;
}
.pp-fav-star:hover { border-color: #eab308; color: #eab308; }
.pp-fav-star.active { background: #eab308; border-color: #eab308; color: #1a1500; }
.pp-team span.pp-team-name { font-weight: 700; font-size: .92rem; color: var(--text-primary); }
.pp-team.pp-loser img { opacity: .55; }
.pp-team.pp-loser span.pp-team-name { color: var(--text-muted); }
.pp-team.pp-winner span.pp-team-name { color: #22c55e; }

.pp-goals-list { display: flex; flex-direction: column; gap: 2px; font-size: .68rem; color: var(--text-muted); min-height: 1px; }
.pp-goals-list .pp-goal-row { display: flex; align-items: center; gap: 4px; justify-content: center; }
.pp-goals-list .pp-goal-row i { color: var(--accent); font-size: .62rem; }

.pp-score-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; font-family: 'Space Mono', monospace; min-width: 100px; }
.pp-score { font-size: 2rem; font-weight: 700; color: var(--text-primary); line-height: 1; }
.pp-minute { font-size: .8rem; font-weight: 700; color: #ef4444; }
.pp-time { font-size: .82rem; color: var(--text-muted); }

.pp-footer {
  display: flex; flex-wrap: wrap; justify-content: center; gap: .5rem 1.5rem;
  padding-top: .9rem; margin-top: 1rem; border-top: 1px solid var(--border);
  font-size: .8rem; color: var(--text-muted);
}
.pp-footer span i { color: var(--accent); margin-right: .35rem; }

/* Dos columnas en pantallas grandes: izquierda = tabla+h2h+stats+timeline,
   derecha = alineaciones+MVP. Canales siempre arriba a todo el ancho. */
.pp-columns { display: grid; grid-template-columns: 1fr; gap: 1.25rem; align-items: start; }
@media (min-width: 992px) {
  .pp-columns { grid-template-columns: 1.3fr 1fr; }
}
.pp-col { display: flex; flex-direction: column; gap: 1.25rem; min-width: 0; }

.pp-section {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px;
  padding: 1.1rem; margin-bottom: 1.25rem;
}
.pp-section-title {
  font-size: .85rem; font-weight: 700; color: var(--text-primary);
  margin: 0 0 .9rem; display: flex; align-items: center; gap: .5rem;
}
.pp-section-title i { color: var(--accent); }

/* MVP */
.pp-mvp { display: flex; align-items: center; gap: 1rem; }
.pp-mvp img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; background: var(--bg-secondary); flex-shrink: 0; }
.pp-mvp-photo-fallback {
  width: 64px; height: 64px; border-radius: 50%; background: var(--bg-secondary);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  color: var(--text-muted); font-size: 1.4rem;
}
.pp-mvp-info { flex: 1; min-width: 0; }
.pp-mvp-name { font-weight: 700; font-size: .95rem; color: var(--text-primary); }
.pp-mvp-team { font-size: .76rem; color: var(--text-muted); margin-bottom: .5rem; }
.pp-mvp-rating {
  display: inline-flex; align-items: center; justify-content: center;
  background: #22c55e; color: #04150a; font-weight: 700; font-family: 'Space Mono', monospace;
  font-size: .8rem; border-radius: 6px; padding: 2px 8px; flex-shrink: 0;
}
.pp-mvp-stats { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .6rem; }
.pp-mvp-stat {
  font-size: .68rem; color: var(--text-secondary); background: var(--bg-secondary);
  border: 1px solid var(--border); border-radius: 100px; padding: 2px 9px;
}
.pp-mvp-stat b { color: var(--text-primary); }

/* Estadísticas comparativas */
.pp-stat-row { margin-bottom: .9rem; }
.pp-stat-row:last-child { margin-bottom: 0; }
.pp-stat-labels { display: flex; justify-content: space-between; font-size: .76rem; margin-bottom: .35rem; }
.pp-stat-labels .pp-stat-title { color: var(--text-muted); font-size: .72rem; }
.pp-stat-home { color: var(--text-primary); font-weight: 700; }
.pp-stat-away { color: var(--text-primary); font-weight: 700; }
.pp-stat-bar { display: flex; height: 6px; border-radius: 100px; overflow: hidden; background: var(--bg-secondary); }
.pp-stat-bar-home { background: var(--accent); }
.pp-stat-bar-away { background: var(--border); }

/* Timeline */
.pp-timeline { position: relative; padding-left: 0; }
.pp-tl-item { display: flex; align-items: center; gap: .7rem; padding: .5rem 0; font-size: .8rem; border-bottom: 1px solid var(--border); }
.pp-tl-item:last-child { border-bottom: none; }
.pp-tl-minute { font-family: 'Space Mono', monospace; font-size: .72rem; color: var(--text-muted); width: 34px; flex-shrink: 0; text-align: right; }
.pp-tl-icon { width: 20px; text-align: center; flex-shrink: 0; }
.pp-tl-icon.goal { color: var(--accent); }
.pp-tl-icon.own-goal { color: #ef4444; }
.pp-tl-icon.sub { color: #22c55e; }
.pp-tl-icon.yellow { color: #eab308; }
.pp-tl-icon.red { color: #ef4444; }
.pp-tl-text { color: var(--text-primary); flex: 1; }
.pp-tl-text small { color: var(--text-muted); display: block; font-size: .7rem; }
.pp-tl-side { font-size: .65rem; color: var(--text-muted); flex-shrink: 0; text-transform: uppercase; }

/* Tabla de posiciones */
.pp-table { width: 100%; border-collapse: collapse; font-size: .78rem; }
.pp-table th { text-align: left; color: var(--text-muted); font-weight: 600; padding: .4rem .5rem; border-bottom: 1px solid var(--border); }
.pp-table td { padding: .45rem .5rem; border-bottom: 1px solid var(--border); color: var(--text-primary); }
.pp-table tr:last-child td { border-bottom: none; }
.pp-table tr.pp-row-highlight td { background: var(--accent-soft); font-weight: 700; }
.pp-table .pp-team-cell { display: flex; align-items: center; gap: .5rem; }
.pp-table .pp-team-cell img { width: 18px; height: 18px; object-fit: contain; }
.pp-table td.pp-num { text-align: center; font-family: 'Space Mono', monospace; }

/* Head to head */
.pp-h2h-item {
  display: flex; align-items: center; justify-content: space-between; gap: 1rem;
  padding: .55rem 0; border-bottom: 1px solid var(--border); font-size: .8rem;
}
.pp-h2h-item:last-child { border-bottom: none; }
.pp-h2h-teams { color: var(--text-primary); }
.pp-h2h-score { font-family: 'Space Mono', monospace; font-weight: 700; color: var(--accent); flex-shrink: 0; }
.pp-h2h-date { color: var(--text-muted); font-size: .72rem; flex-shrink: 0; }

/* Alineaciones */
.pp-lineups { display: flex; gap: 1.5rem; flex-wrap: wrap; }
.pp-lineup-col { flex: 1; min-width: 220px; }
.pp-lineup-meta { font-size: .72rem; color: var(--text-muted); margin-bottom: .6rem; }
.pp-player-row { display: flex; align-items: center; gap: .5rem; padding: .3rem 0; font-size: .8rem; color: var(--text-primary); }
.pp-player-num { font-family: 'Space Mono', monospace; color: var(--text-muted); width: 22px; flex-shrink: 0; }
.pp-subs-title { font-size: .72rem; color: var(--text-muted); margin: .7rem 0 .3rem; text-transform: uppercase; letter-spacing: .03em; }

/* Canales */
.pp-channels { display: flex; flex-wrap: wrap; gap: .6rem; }
.pp-channel-pill {
  display: flex; align-items: center; gap: .5rem; padding: .5rem .9rem;
  background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 100px;
  color: var(--text-primary); text-decoration: none; font-size: .82rem; font-weight: 600;
  transition: var(--transition);
}
.pp-channel-pill:hover { border-color: var(--accent); background: var(--accent-soft); }
.pp-channel-pill img { width: 22px; height: 22px; object-fit: contain; border-radius: 4px; }

.pp-empty { color: var(--text-muted); font-size: .82rem; text-align: center; padding: 1rem; }

/* Skeletons — mismo shimmer que .skeleton-block (assets/css/style.css),
   reemplazados por assets/js/partido.js en cuanto llega api/partido_extra.php */
.pp-skel-row { height: 28px; border-radius: 6px; margin-bottom: 6px; }
.pp-skel-row:last-child { margin-bottom: 0; }
</style>

<div class="partido-page">

  <nav style="font-size:.8rem; color:var(--text-muted); margin-bottom:1rem;">
    <a href="<?= url('home') ?>" style="color:var(--text-muted); text-decoration:none;"><i class="fas fa-home"></i> Inicio</a>
    <span style="margin:0 .4rem;">›</span>
    <a href="<?= url('liga', ['id' => $ligaId, 'type' => $partidoData['tipo'] ?? 'soccer']) ?>" style="color:var(--text-muted); text-decoration:none;"><?= htmlspecialchars($ligaNom) ?></a>
    <span style="margin:0 .4rem;">›</span>
    <span style="color:var(--text-secondary);"><?= $local ?> vs <?= $visit ?></span>
  </nav>

  <div class="pp-header">
    <div class="pp-header-top">
      <img src="<?= $ligaLogo ?>" data-logo-base="<?= $ligaLogo ?>" data-fallback-icon="league" class="lazy-img" loading="lazy">
      <span class="pp-league-name"><?= htmlspecialchars($ligaNom) ?></span>
      <?php if ($isLive): ?>
      <span class="pp-status-badge pp-status-live" id="pp-status-badge">En vivo</span>
      <?php elseif ($isFinished): ?>
      <span class="pp-status-badge pp-status-finished" id="pp-status-badge">Finalizado</span>
      <?php else: ?>
      <span class="pp-status-badge pp-status-upcoming" id="pp-status-badge"><?= $time ?></span>
      <?php endif; ?>
    </div>

    <div class="pp-body-row" style="justify-content:center;">
      <div class="pp-team" id="pp-team-home" data-team-side="home">
        <div class="pp-team-img-wrap">
          <img src="<?= $localLogo ?>" data-logo-base="<?= $localLogo ?>" data-fallback-icon="team" class="lazy-img" loading="lazy">
          <?php if ($localId): ?>
          <button type="button" class="pp-fav-star<?= in_array($localId, $equiposFavoritos, true) ? ' active' : '' ?>" data-equipo-id="<?= $localId ?>" title="Marcar como favorito">
            <i class="fas fa-star"></i>
          </button>
          <?php endif; ?>
        </div>
        <span class="pp-team-name"><?= $local ?></span>
        <div class="pp-goals-list" id="pp-home-goals"></div>
      </div>

      <div class="pp-score-col">
        <?php if ($status === 'live' || $status === 'finished'): ?>
        <div class="pp-score" data-live-score="<?= $partidoId ?>">vs</div>
        <?php if ($isLive): ?>
        <div class="pp-minute" data-live-minute="<?= $partidoId ?>"></div>
        <?php else: ?>
        <div class="pp-time">Finalizó</div>
        <?php endif; ?>
        <?php else: ?>
        <div class="pp-score">vs</div>
        <div class="pp-time"><?= $time ?></div>
        <?php endif; ?>
      </div>

      <div class="pp-team" id="pp-team-away" data-team-side="away">
        <div class="pp-team-img-wrap">
          <img src="<?= $visitLogo ?>" data-logo-base="<?= $visitLogo ?>" data-fallback-icon="team" class="lazy-img" loading="lazy">
          <?php if ($visitId): ?>
          <button type="button" class="pp-fav-star<?= in_array($visitId, $equiposFavoritos, true) ? ' active' : '' ?>" data-equipo-id="<?= $visitId ?>" title="Marcar como favorito">
            <i class="fas fa-star"></i>
          </button>
          <?php endif; ?>
        </div>
        <span class="pp-team-name"><?= $visit ?></span>
        <div class="pp-goals-list" id="pp-away-goals"></div>
      </div>
    </div>

    <?php if ($status === 'live' || $status === 'finished'): ?>
    <div class="pp-footer">
      <span><i class="fas fa-map-marker-alt"></i><span data-live-venue="<?= $partidoId ?>"></span></span>
      <span><i class="fas fa-user-tie"></i><span data-live-referee="<?= $partidoId ?>"></span></span>
    </div>
    <?php endif; ?>
  </div>

  <!-- Canales — arriba de todo, es la acción principal -->
  <div class="pp-section">
    <div class="pp-section-title"><i class="fas fa-broadcast-tower"></i> Canales de transmisión</div>
    <?php if (empty($canales)): ?>
    <div class="pp-empty">No hay canales asignados a este partido todavía.</div>
    <?php else: ?>
    <div class="pp-channels">
      <?php foreach ($canales as $c): ?>
      <a href="<?= url('canal', ['id' => $c['id'], 'partido' => $partidoId]) ?>" class="pp-channel-pill">
        <img src="<?= htmlspecialchars($c['logo']) ?>" loading="lazy">
        <?= htmlspecialchars($c['nombre']) ?>
        <?php if ($c['ios']): ?><i class="fab fa-apple" style="color:var(--accent);" title="iOS disponible"></i><?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="pp-columns">
    <div class="pp-col">

      <?php if ($isFinished || $isLive): ?>
      <!-- MVP -->
      <div class="pp-section" id="pp-mvp-section" style="display:none;">
        <div class="pp-section-title"><i class="fas fa-star"></i> Jugador del partido</div>
        <div id="pp-mvp-body"></div>
      </div>

      <!-- Estadísticas -->
      <div class="pp-section" id="pp-stats-section" style="display:none;">
        <div class="pp-section-title"><i class="fas fa-chart-bar"></i> Estadísticas</div>
        <div id="pp-stats-body"></div>
      </div>

      <!-- Timeline -->
      <div class="pp-section" id="pp-timeline-section" style="display:none;">
        <div class="pp-section-title"><i class="fas fa-stream"></i> Minuto a minuto</div>
        <div id="pp-timeline-body"></div>
      </div>
      <?php endif; ?>

      <div class="pp-section" id="pp-standings-section">
        <div class="pp-section-title"><i class="fas fa-list-ol"></i> Tabla de posiciones — <?= htmlspecialchars($ligaNom) ?></div>
        <div id="pp-standings-body">
          <?php for ($i = 0; $i < 8; $i++): ?>
          <div class="pp-skel-row skeleton-block"></div>
          <?php endfor; ?>
        </div>
      </div>

      <div class="pp-section" id="pp-h2h-section">
        <div class="pp-section-title"><i class="fas fa-history"></i> Enfrentamientos anteriores</div>
        <div id="pp-h2h-body">
          <?php for ($i = 0; $i < 4; $i++): ?>
          <div class="pp-skel-row skeleton-block"></div>
          <?php endfor; ?>
        </div>
      </div>
    </div>

    <div class="pp-col">
      <div class="pp-section" id="pp-lineup-section">
        <div class="pp-section-title"><i class="fas fa-users"></i> Alineaciones</div>
        <div id="pp-lineup-body">
          <?php for ($i = 0; $i < 11; $i++): ?>
          <div class="pp-skel-row skeleton-block"></div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
const PP_PARTIDO_ID = <?= $partidoId ?>;
const PP_LIGA_ID     = <?= (int)$ligaId ?>;
const PP_LOCAL_ID    = <?= $localId ?>;
const PP_VISIT_ID    = <?= $visitId ?>;
const PP_IS_FINISHED = <?= $isFinished ? 'true' : 'false' ?>;
const PP_IS_LOGGED_IN = <?= isLoggedIn() ? 'true' : 'false' ?>;
const PP_LOGIN_URL    = <?= json_encode(url('login')) ?>;
</script>
