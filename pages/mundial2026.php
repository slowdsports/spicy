<?php
/**
 * StreamHub - Mundial FIFA 2026
 * Página exclusiva para el Mundial de la FIFA 2026 (liga ID: 16)
 */

define('MUNDIAL_LEAGUE_ID', '16');

/* ==========================================================
   JSON
========================================================== */
$jsonPath   = __DIR__ . '/../data/matches.json';
$allPartidos = [];

if (file_exists($jsonPath)) {
    $allPartidos = json_decode(file_get_contents($jsonPath), true) ?? [];
}

/* ==========================================================
   FILTRAR liga 16
========================================================== */
$partidos = array_values(array_filter($allPartidos, function($p) {
    return (string)($p['league'] ?? '') === MUNDIAL_LEAGUE_ID;
}));

/* ==========================================================
   ORDENAR — live → upcoming → finished
========================================================== */
usort($partidos, function($a, $b) {
    $now = time();

    $getTs = function($item) {
        if (!empty($item['timestamp'])) {
            return (int)$item['timestamp'];
        }
        $raw = $item['fecha_hora'] ?? '';
        if ($raw !== '') {
            try {
                $dt = new DateTime($raw, new DateTimeZone('America/Tegucigalpa'));
                return $dt->getTimestamp();
            } catch (Exception $e) {}
        }
        return PHP_INT_MAX;
    };

    $getPeso = function($item) use ($now, $getTs) {
        $ts = $getTs($item);
        if ($ts <= $now && $ts > $now - 10800) return 0;
        return $ts >= $now ? 1 : 2;
    };

    $pa = $getPeso($a);
    $pb = $getPeso($b);

    if ($pa !== $pb) return $pa <=> $pb;

    $ta = $getTs($a);
    $tb = $getTs($b);

    return $pa === 2 ? $tb <=> $ta : $ta <=> $tb;
});

/* ==========================================================
   META
========================================================== */
$ligaNombre = $partidos[0]['leagueName'] ?? 'FIFA World Cup 2026';
$ligaLogo   = BASE_URL . 'assets/img/ligas/sf/16.png';

/* ==========================================================
   HELPERS
========================================================== */
function mTeamLogo($id) {
    return BASE_URL . "assets/img/equipos/sf/{$id}.png";
}

function mCanalLogo($id) {
    return BASE_URL . "assets/img/canales/{$id}.png";
}
?>

<div class="mundial-wrapper">

<!-- ══════════════════════════════════════════════════════════
     HERO MUNDIAL
═══════════════════════════════════════════════════════════ -->
<section class="mundial-hero">
  <div class="mundial-hero-bg">
    <!-- Estrellas decorativas -->
    <div class="hero-stars">
      <?php for($s=0;$s<20;$s++): ?>
      <span class="star" style="
        left:<?= rand(2,98) ?>%;
        top:<?= rand(5,95) ?>%;
        animation-delay:<?= rand(0,30)/10 ?>s;
        width:<?= rand(2,5) ?>px;
        height:<?= rand(2,5) ?>px;
      "></span>
      <?php endfor; ?>
    </div>
    <!-- Líneas decorativas -->
    <div class="hero-lines">
      <div class="hero-line hero-line-1"></div>
      <div class="hero-line hero-line-2"></div>
    </div>
  </div>

  <div class="container position-relative">

    <nav class="mundial-breadcrumb">
      <a href="<?= url('home') ?>">
        <i class="fas fa-home"></i> Inicio
      </a>
      <span class="bc-sep">›</span>
      <span>Mundial 2026</span>
    </nav>

    <div class="hero-content">

      <div class="trophy-badge">
        <div class="trophy-glow"></div>
        <i class="fas fa-trophy trophy-icon"></i>
      </div>

      <div class="hero-text">
        <div class="hero-eyebrow">
          <span class="eyebrow-pill">
            <i class="fas fa-globe-americas me-1"></i>
            USA · CANADA · MÉXICO
          </span>
        </div>

        <h1 class="hero-title">
          FIFA<br>
          <span class="gold-text">WORLD CUP</span><br>
          <span class="year-text">2026™</span>
        </h1>

        <p class="hero-desc">
          El torneo más grande del mundo. 48 naciones.
          3 países sede. Un campeón.
        </p>

        <div class="hero-stats">
          <div class="stat-pill">
            <i class="fas fa-futbol"></i>
            <span><?= count($partidos) ?> partidos</span>
          </div>
          <div class="stat-pill">
            <i class="fas fa-flag"></i>
            <span>48 selecciones</span>
          </div>
          <div class="stat-pill">
            <i class="fas fa-calendar-alt"></i>
            <span>Jun–Jul 2026</span>
          </div>
        </div>
      </div>

    </div>

    <!-- Logo -->
    <div class="mundial-logo-float">
      <img src="<?= $ligaLogo ?>"
           data-logo-base="<?= $ligaLogo ?>"
           data-fallback-icon="league"
           alt="FIFA World Cup 2026"
           class="lazy-img"
           loading="lazy">
    </div>

  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     PARTIDOS
═══════════════════════════════════════════════════════════ -->
<section class="mundial-matches-section">
  <div class="container">

    <div class="mundial-section-title">
      <div class="title-bar"></div>
      <i class="fas fa-calendar-check title-icon"></i>
      <span>Calendario de partidos</span>
      <?php if (!empty($partidos)): ?>
      <span class="match-count-badge"><?= count($partidos) ?></span>
      <?php endif; ?>
    </div>

    <?php if (empty($partidos)): ?>

    <!-- ESTADO VACÍO: próximos partidos -->
    <div class="mundial-empty">
      <div class="empty-trophy">
        <div class="empty-glow"></div>
        <i class="fas fa-trophy"></i>
      </div>
      <h2 class="empty-title">¡El Mundial se acerca!</h2>
      <p class="empty-desc">
        Los partidos del FIFA World Cup 2026 estarán disponibles aquí
        en cuanto comiencen las transmisiones.
      </p>
      <div class="empty-countdown-wrapper">
        <div class="countdown-label">La espera comenzó...</div>
        <div class="countdown-rings">
          <div class="ring ring-1"></div>
          <div class="ring ring-2"></div>
          <div class="ring ring-3"></div>
        </div>
      </div>
      <a href="<?= url('eventos') ?>" class="btn-back-eventos">
        <i class="fas fa-trophy me-2"></i>Ver otros eventos
      </a>
    </div>

    <?php else: ?>

    <!-- LISTA DE PARTIDOS -->
    <div class="accordion sh-accordion mundial-accordion" id="mundialAccordion">

    <?php foreach($partidos as $i => $p):

      $id         = "match" . $p['id'];
      $local      = $p['homeTeam']['name'] ?? '';
      $visit      = $p['awayTeam']['name'] ?? '';
      $localLogo  = mTeamLogo($p['homeTeam']['logo'] ?? '');
      $visitLogo  = mTeamLogo($p['awayTeam']['logo'] ?? '');
      $status     = $p['status'] ?? 'upcoming';
      $time       = $p['time'] ?? '--:--';

      $canalesPartido = [];
      for($x = 1; $x <= 10; $x++) {
          $key     = "cnl{$x}";
          $keyName = "cnl{$x}Name";
          $keyLogo = "cnl{$x}Logo";
          if (!empty($p[$key])) {
              $cid  = trim($p[$key]);
              $logo = !empty($p[$keyLogo]) ? $p[$keyLogo] : mCanalLogo($cid);
              $canalesPartido[] = [
                  'id'     => $cid,
                  'nombre' => $p[$keyName] ?? "Canal {$cid}",
                  'logo'   => $logo,
              ];
          }
      }

      $primerCanal = $canalesPartido[0] ?? null;
      $isLive = ($status === 'live');
    ?>

    <div class="accordion-item sh-item mundial-item <?= $isLive ? 'is-live' : '' ?>">

      <?php if($isLive): ?>
      <div class="live-glow-bar"></div>
      <?php endif; ?>

      <h2 class="accordion-header">
        <button class="accordion-button sh-btn mundial-btn <?= $i > 0 ? 'collapsed' : '' ?>"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#<?= $id ?>">

          <div class="match-grid">

            <!-- Izquierda: logo liga + badge estado -->
            <div class="match-left">
              <div class="mini-league mundial-mini-league">
                <img src="<?= $ligaLogo ?>"
                     data-logo-base="<?= $ligaLogo ?>"
                     data-fallback-icon="league"
                     alt="FIFA WC"
                     class="lazy-img"
                     loading="lazy">
              </div>

              <?php if($isLive): ?>
              <span class="badge-live mundial-badge-live">
                <span class="dot-live"></span> EN VIVO
              </span>
              <?php else: ?>
              <span class="badge-time mundial-badge-time">
                <i class="fas fa-clock"></i>
                <span class="match-countdown"
                      data-time="<?= htmlspecialchars($p['fecha_hora'] ?? $time) ?>"
                      data-ts="<?= (int)($p['timestamp'] ?? 0) ?>">
                  <?= htmlspecialchars($time) ?>
                </span>
              </span>
              <?php endif; ?>
            </div>

            <!-- Centro: equipos -->
            <div class="match-center">

              <div class="team-box">
                <img src="<?= $localLogo ?>"
                     data-logo-base="<?= $localLogo ?>"
                     data-fallback-icon="team"
                     class="team-logo mundial-team-logo lazy-img"
                     loading="lazy">
                <span><?= htmlspecialchars($local) ?></span>
              </div>

              <div class="vs-box mundial-vs">
                <?php if($isLive && isset($p['homeTeam']['score'])): ?>
                <strong class="score-display">
                  <?= (int)$p['homeTeam']['score'] ?> – <?= (int)$p['awayTeam']['score'] ?>
                </strong>
                <?php else: ?>
                <strong>vs</strong>
                <?php endif; ?>
                <small>
                  <span class="match-time-local"
                        data-hn="<?= htmlspecialchars($time) ?>">
                    <?= htmlspecialchars($time) ?>
                  </span>
                </small>
              </div>

              <div class="team-box">
                <img src="<?= $visitLogo ?>"
                     data-logo-base="<?= $visitLogo ?>"
                     data-fallback-icon="team"
                     class="team-logo mundial-team-logo lazy-img"
                     loading="lazy">
                <span><?= htmlspecialchars($visit) ?></span>
              </div>

            </div>

            <!-- Derecha: primer canal -->
            <div class="match-right">
              <?php if($primerCanal): ?>
              <div class="first-channel mundial-first-channel">
                <img src="<?= $primerCanal['logo'] ?>" alt="" class="lazy-img" loading="lazy">
                <small><?= htmlspecialchars($primerCanal['nombre']) ?></small>
              </div>
              <?php else: ?>
              <div class="no-channel">
                <i class="fas fa-tv"></i>
                <small>Sin canal</small>
              </div>
              <?php endif; ?>
            </div>

          </div>
        </button>
      </h2>

      <div id="<?= $id ?>"
           class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
           data-bs-parent="#mundialAccordion">

        <div class="accordion-body sh-body mundial-body">

          <h6 class="channels-title">
            <i class="fas fa-broadcast-tower me-2"></i>
            Canales de cobertura
          </h6>

          <?php if(empty($canalesPartido)): ?>
          <div class="no-list-channel">No hay canales disponibles.</div>
          <?php else: ?>

          <div class="channel-list">
            <?php foreach($canalesPartido as $canal): ?>
            <a href="<?= url('canal', ['id' => $canal['id'], 'partido' => $p['id']]) ?>"
               class="channel-row mundial-channel-row">
              <img src="<?= $canal['logo'] ?>" class="channel-row-logo lazy-img" loading="lazy" alt="">
              <div class="channel-row-name"><?= htmlspecialchars($canal['nombre']) ?></div>
              <i class="fas fa-play-circle ms-auto"></i>
            </a>
            <?php endforeach; ?>
          </div>

          <?php endif; ?>

        </div>
      </div>
    </div>

    <?php endforeach; ?>

    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     SEDES
═══════════════════════════════════════════════════════════ -->
<section class="mundial-sedes-section">
  <div class="container">

    <div class="mundial-section-title">
      <div class="title-bar"></div>
      <i class="fas fa-map-marker-alt title-icon"></i>
      <span>Países sede</span>
    </div>

    <div class="sedes-grid">

      <div class="sede-card">
        <div class="sede-flag">🇺🇸</div>
        <div class="sede-info">
          <div class="sede-name">Estados Unidos</div>
          <div class="sede-detail">11 ciudades · 60 partidos</div>
        </div>
        <div class="sede-highlight">Principal</div>
      </div>

      <div class="sede-card">
        <div class="sede-flag">🇲🇽</div>
        <div class="sede-info">
          <div class="sede-name">México</div>
          <div class="sede-detail">3 ciudades · 13 partidos</div>
        </div>
      </div>

      <div class="sede-card">
        <div class="sede-flag">🇨🇦</div>
        <div class="sede-info">
          <div class="sede-name">Canadá</div>
          <div class="sede-detail">2 ciudades · 13 partidos</div>
        </div>
      </div>

    </div>

  </div>
</section>

</div><!-- .mundial-wrapper -->


<!-- ══════════════════════════════════════════════════════════
     ESTILOS EXCLUSIVOS MUNDIAL 2026
═══════════════════════════════════════════════════════════ -->
<style>
/* ── Variables de acento dorado para esta página ─────────── */
.mundial-wrapper {
  --gold:        #f59e0b;
  --gold-hover:  #d97706;
  --gold-light:  #fbbf24;
  --gold-soft:   rgba(245,158,11,0.13);
  --gold-glow:   rgba(245,158,11,0.35);
  --gold-border: rgba(245,158,11,0.4);
  --gold-bright: #fde68a;
}

/* ── Hero ─────────────────────────────────────────────────── */
.mundial-hero {
  position: relative;
  background: linear-gradient(135deg, #07070b 0%, #0e0a00 50%, #12100a 100%);
  border-bottom: 1px solid rgba(245,158,11,0.25);
  padding: 3.5rem 0 2.5rem;
  overflow: hidden;
}

.mundial-hero-bg {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

/* Estrellas */
.star {
  position: absolute;
  background: var(--gold);
  border-radius: 50%;
  opacity: 0;
  animation: twinkle 3s ease-in-out infinite;
}

@keyframes twinkle {
  0%, 100% { opacity: 0; transform: scale(.8); }
  50%       { opacity: 0.55; transform: scale(1.2); }
}

/* Líneas decorativas */
.hero-line {
  position: absolute;
  border-radius: 2px;
  opacity: 0.07;
}
.hero-line-1 {
  width: 600px; height: 2px;
  background: linear-gradient(90deg, transparent, var(--gold), transparent);
  top: 40%; left: -100px;
  transform: rotate(-8deg);
}
.hero-line-2 {
  width: 400px; height: 2px;
  background: linear-gradient(90deg, transparent, var(--gold), transparent);
  bottom: 25%; right: -50px;
  transform: rotate(5deg);
}

/* Breadcrumb */
.mundial-breadcrumb {
  font-size: .78rem;
  color: rgba(245,158,11,.5);
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: .4rem;
}
.mundial-breadcrumb a {
  color: rgba(245,158,11,.5);
  text-decoration: none;
  transition: color .2s;
}
.mundial-breadcrumb a:hover { color: var(--gold); }
.bc-sep { opacity: .4; }

/* Hero content */
.hero-content {
  display: flex;
  align-items: flex-start;
  gap: 2.5rem;
}

/* Trofeo */
.trophy-badge {
  position: relative;
  flex-shrink: 0;
}
.trophy-glow {
  position: absolute;
  inset: -12px;
  background: radial-gradient(circle, rgba(245,158,11,.3) 0%, transparent 70%);
  border-radius: 50%;
  animation: trophy-pulse 2.5s ease-in-out infinite;
}
@keyframes trophy-pulse {
  0%, 100% { transform: scale(1);   opacity: .6; }
  50%       { transform: scale(1.1); opacity: 1;  }
}
.trophy-icon {
  font-size: 4.5rem;
  color: var(--gold);
  filter: drop-shadow(0 0 24px rgba(245,158,11,.7));
  position: relative;
  z-index: 1;
  animation: trophy-float 4s ease-in-out infinite;
}
@keyframes trophy-float {
  0%, 100% { transform: translateY(0);    }
  50%       { transform: translateY(-8px); }
}

/* Texto del hero */
.hero-text { flex: 1; }

.eyebrow-pill {
  display: inline-flex;
  align-items: center;
  background: rgba(245,158,11,.12);
  border: 1px solid rgba(245,158,11,.3);
  color: var(--gold);
  font-size: .75rem;
  font-weight: 700;
  padding: .3rem .85rem;
  border-radius: 100px;
  letter-spacing: .5px;
  text-transform: uppercase;
  margin-bottom: .85rem;
}

.hero-title {
  font-family: 'Space Mono', monospace;
  font-size: clamp(2rem, 5vw, 3.2rem);
  font-weight: 700;
  line-height: 1.05;
  color: #f0f0f5;
  margin-bottom: .75rem;
  letter-spacing: -1px;
}
.gold-text {
  background: linear-gradient(90deg, var(--gold) 0%, var(--gold-bright) 50%, var(--gold) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  background-size: 200% auto;
  animation: gold-shine 4s linear infinite;
}
@keyframes gold-shine {
  0%   { background-position: 0% center; }
  100% { background-position: 200% center; }
}
.year-text {
  font-size: .75em;
  color: rgba(245,158,11,.6);
  -webkit-text-fill-color: rgba(245,158,11,.6);
}

.hero-desc {
  color: #9090a8;
  font-size: .92rem;
  margin-bottom: 1.25rem;
  max-width: 380px;
}

.hero-stats {
  display: flex;
  flex-wrap: wrap;
  gap: .5rem;
}
.stat-pill {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  background: rgba(245,158,11,.08);
  border: 1px solid rgba(245,158,11,.2);
  color: var(--gold-light);
  font-size: .78rem;
  font-weight: 600;
  padding: .3rem .8rem;
  border-radius: 8px;
}
.stat-pill i { font-size: .7rem; opacity: .75; }

/* Logo flotante */
.mundial-logo-float {
  position: absolute;
  right: 1.5rem;
  top: 50%;
  transform: translateY(-50%);
  width: 120px;
  height: 120px;
  opacity: .18;
  pointer-events: none;
}
.mundial-logo-float img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  filter: sepia(1) saturate(3) hue-rotate(5deg);
}

/* ── Sección de partidos ──────────────────────────────────── */
.mundial-matches-section {
  background: var(--bg-primary);
  padding: 2.5rem 0;
}

.mundial-section-title {
  font-family: 'Space Mono', monospace;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 1.75rem;
}
.title-bar {
  width: 4px;
  height: 20px;
  background: var(--gold);
  border-radius: 2px;
  flex-shrink: 0;
  box-shadow: 0 0 8px var(--gold-glow);
}
.title-icon { color: var(--gold); font-size: .95rem; }
.match-count-badge {
  margin-left: auto;
  background: var(--gold-soft);
  border: 1px solid var(--gold-border);
  color: var(--gold);
  font-size: .72rem;
  font-weight: 700;
  padding: 2px 10px;
  border-radius: 100px;
}

/* ── Accordion mundial ───────────────────────────────────── */
.mundial-accordion { display: flex; flex-direction: column; gap: 1rem; }

.mundial-item {
  border: 1px solid rgba(245,158,11,.18) !important;
  border-radius: 14px !important;
  background: #0f0d06 !important;
  overflow: hidden;
  transition: border-color .25s, box-shadow .25s;
  position: relative;
}
.mundial-item:hover {
  border-color: rgba(245,158,11,.45) !important;
  box-shadow: 0 4px 28px rgba(245,158,11,.12);
}
.mundial-item.is-live {
  border-color: rgba(245,158,11,.55) !important;
  box-shadow: 0 0 20px rgba(245,158,11,.18);
}

/* Barra superior dorada para partidos en vivo */
.live-glow-bar {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--gold-hover), var(--gold-light), var(--gold-hover));
  background-size: 200% auto;
  animation: bar-sweep 2.5s linear infinite;
}
@keyframes bar-sweep {
  0%   { background-position: 0% center; }
  100% { background-position: 200% center; }
}

.mundial-btn {
  padding: 1rem 1.2rem !important;
  background: transparent !important;
  box-shadow: none !important;
  color: var(--text-primary) !important;
}
.mundial-btn::after { filter: invert(0) brightness(2) sepia(1) saturate(3) hue-rotate(5deg) !important; }

.mundial-mini-league {
  background: rgba(245,158,11,.08) !important;
  border-color: rgba(245,158,11,.2) !important;
}

.mundial-badge-live {
  background: rgba(239,68,68,.15);
  color: #ef4444;
  font-size: .72rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 30px;
  white-space: nowrap;
}
.mundial-badge-time {
  background: rgba(245,158,11,.12);
  color: var(--gold);
  border: 1px solid rgba(245,158,11,.25);
  font-size: .72rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 30px;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.mundial-team-logo {
  filter: drop-shadow(0 2px 6px rgba(245,158,11,.2));
  transition: filter .2s;
}
.mundial-item:hover .mundial-team-logo {
  filter: drop-shadow(0 2px 10px rgba(245,158,11,.4));
}

.mundial-vs strong {
  font-family: 'Space Mono', monospace;
  color: var(--gold-light);
  text-shadow: 0 0 10px rgba(245,158,11,.5);
}
.score-display {
  font-size: 1.25rem;
  color: var(--gold) !important;
}

.mundial-first-channel {
  background: rgba(245,158,11,.07) !important;
  border: 1px solid rgba(245,158,11,.2) !important;
}

.mundial-body {
  background: #080600 !important;
  border-top: 1px solid rgba(245,158,11,.12) !important;
}

.mundial-channel-row {
  border-color: rgba(245,158,11,.12) !important;
  background: rgba(245,158,11,.04) !important;
  color: var(--text-primary) !important;
  transition: .2s !important;
}
.mundial-channel-row:hover {
  border-color: var(--gold-border) !important;
  background: var(--gold-soft) !important;
  color: var(--gold) !important;
}

/* ── Estado vacío ────────────────────────────────────────── */
.mundial-empty {
  text-align: center;
  padding: 4rem 1rem;
}

.empty-trophy {
  position: relative;
  display: inline-block;
  font-size: 4rem;
  color: var(--gold);
  margin-bottom: 1.5rem;
}
.empty-glow {
  position: absolute;
  inset: -16px;
  background: radial-gradient(circle, rgba(245,158,11,.25) 0%, transparent 70%);
  border-radius: 50%;
  animation: trophy-pulse 2.5s ease-in-out infinite;
}
.empty-trophy i { position: relative; z-index: 1; }

.empty-title {
  font-family: 'Space Mono', monospace;
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: .5rem;
  background: linear-gradient(90deg, var(--gold), var(--gold-bright), var(--gold));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  background-size: 200% auto;
  animation: gold-shine 4s linear infinite;
}

.empty-desc {
  color: var(--text-muted);
  font-size: .9rem;
  max-width: 420px;
  margin: 0 auto 1.75rem;
  line-height: 1.6;
}

/* Anillos animados */
.countdown-rings {
  position: relative;
  width: 80px;
  height: 80px;
  margin: 0 auto 1rem;
}
.ring {
  position: absolute;
  border-radius: 50%;
  border: 2px solid rgba(245,158,11,.3);
  inset: 0;
  animation: ring-expand 2.4s ease-out infinite;
}
.ring-2 { animation-delay: .8s; }
.ring-3 { animation-delay: 1.6s; }
@keyframes ring-expand {
  0%   { transform: scale(.4); opacity: .8; border-color: rgba(245,158,11,.6); }
  100% { transform: scale(2);  opacity: 0; }
}
.countdown-label {
  font-size: .8rem;
  color: rgba(245,158,11,.5);
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-bottom: 1.5rem;
  font-family: 'Space Mono', monospace;
}

.btn-back-eventos {
  display: inline-flex;
  align-items: center;
  padding: .65rem 1.5rem;
  background: rgba(245,158,11,.12);
  border: 1px solid rgba(245,158,11,.35);
  color: var(--gold);
  font-weight: 700;
  font-size: .88rem;
  border-radius: 12px;
  text-decoration: none;
  transition: all .25s;
  letter-spacing: .3px;
}
.btn-back-eventos:hover {
  background: rgba(245,158,11,.22);
  border-color: var(--gold);
  color: var(--gold-light);
  box-shadow: 0 4px 18px rgba(245,158,11,.25);
  transform: translateY(-2px);
}

/* ── Sedes ───────────────────────────────────────────────── */
.mundial-sedes-section {
  background: #07070b;
  border-top: 1px solid rgba(245,158,11,.12);
  padding: 2.5rem 0;
}

.sedes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
}

.sede-card {
  background: rgba(245,158,11,.05);
  border: 1px solid rgba(245,158,11,.15);
  border-radius: 14px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: .25s;
  position: relative;
  overflow: hidden;
}
.sede-card:hover {
  border-color: rgba(245,158,11,.4);
  background: rgba(245,158,11,.09);
  box-shadow: 0 4px 20px rgba(245,158,11,.1);
}

.sede-flag { font-size: 2.5rem; flex-shrink: 0; }
.sede-name {
  font-weight: 700;
  font-size: .95rem;
  color: var(--text-primary);
  margin-bottom: .2rem;
}
.sede-detail {
  font-size: .75rem;
  color: var(--text-muted);
}
.sede-highlight {
  margin-left: auto;
  background: rgba(245,158,11,.15);
  border: 1px solid rgba(245,158,11,.3);
  color: var(--gold);
  font-size: .68rem;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: .3px;
  flex-shrink: 0;
}

/* ── Reutilizar estilos de liga.php ─────────────────────── */
.sh-accordion { display: flex; flex-direction: column; gap: 1rem; }

.mundial-item { border-radius: 14px !important; overflow: hidden; }

.match-grid {
  width: 100%;
  display: grid;
  grid-template-columns: 100px 1fr 120px;
  gap: 1rem;
  align-items: center;
}
.match-left  { display: flex; flex-direction: column; align-items: center; gap: .5rem; }
.match-right { display: flex; justify-content: flex-end; }

.mini-league {
  width: 54px; height: 54px;
  border-radius: 12px;
  background: var(--bg-secondary);
  display: flex; align-items: center; justify-content: center;
  padding: 8px;
  border: 1px solid var(--border);
}
.mini-league img { width: 100%; height: 100%; object-fit: contain; }

.match-center {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.team-box {
  display: flex; flex-direction: column;
  align-items: center; gap: .5rem;
  width: 130px; text-align: center;
  font-weight: 700; font-size: .82rem;
}
.team-logo { width: 44px; height: 44px; object-fit: contain; }

.vs-box {
  display: flex; flex-direction: column;
  align-items: center;
  font-family: 'Space Mono', monospace;
}
.vs-box small { color: var(--text-muted); font-size: .72rem; }

.first-channel {
  display: flex; flex-direction: column;
  align-items: center; gap: .4rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border);
  padding: .6rem; border-radius: 10px;
  min-width: 90px;
}
.first-channel img { width: 80px; height: 30px; object-fit: contain; }

.no-channel { color: var(--text-muted); text-align: center; }

.dot-live {
  width: 6px; height: 6px;
  background: #ef4444; border-radius: 50%;
  display: inline-block; margin-right: 6px;
  animation: live-blink 1s ease-in-out infinite;
}
@keyframes live-blink {
  0%, 100% { opacity: 1; }
  50%       { opacity: .3; }
}

.sh-body { background: var(--bg-secondary) !important; }

.channels-title {
  font-size: .8rem; font-weight: 700;
  margin-bottom: 1rem; color: var(--text-muted);
}

.channel-list { display: flex; flex-direction: column; gap: .6rem; }

.channel-row {
  display: flex; align-items: center; gap: .75rem;
  padding: .7rem .9rem;
  border: 1px solid var(--border);
  border-radius: 12px;
  text-decoration: none; color: var(--text-primary);
  background: var(--bg-card);
  transition: .2s;
}
.channel-row:hover { border-color: var(--accent); background: var(--accent-soft); color: var(--accent); }

.channel-row-logo { height: 26px; width: 60px; object-fit: contain; }
.channel-row-name { font-weight: 600; font-size: .85rem; }

.no-list-channel { color: var(--text-muted); font-size: .85rem; }

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 768px) {
  .hero-content { flex-direction: column; align-items: flex-start; gap: 1.25rem; }
  .trophy-icon  { font-size: 3rem; }
  .hero-title   { font-size: 2rem; }
  .mundial-logo-float { display: none; }

  .match-grid {
    grid-template-columns: 1fr;
    text-align: center;
  }
  .match-right  { justify-content: center; }
  .match-center { flex-direction: column; }
  .team-box     { width: 100%; }
}

@media (max-width: 576px) {
  .sedes-grid { grid-template-columns: 1fr; }
  .hero-stats { gap: .35rem; }
  .stat-pill  { font-size: .72rem; }
  .hero-desc  { font-size: .85rem; }
}
</style>
