/**
 * StreamHub - JS de la página principal (home)
 * Carga partidos y canales desde JSON.
 */

// ============================================================
// SLIDER DE PARTIDOS
// ============================================================
// Cambia una vez por minuto: evita servir una copia vieja desde algún
// caché intermedio (navegador, Nginx) sin renunciar del todo a cachear.
function cacheBustedUrl(url) {
  return url + '?v=' + Math.floor(Date.now() / 60000);
}

async function loadMatches() {
  try {
    const res = await fetch(cacheBustedUrl('data/matches.json'));
    const matches = await res.json();
    const slider = document.getElementById('matches-slider');
    if (!slider) return;

    const now = Date.now();
    const in24h = now + 86400000;    // 24 horas hacia adelante
    const minus3h = now - 10800000;  // mostrar hasta 3h después del inicio (incl. badge Finalizado)

    const filtered = matches.filter(m => {
      if (m.status === 'live') {
        // Excluir live con fecha conocida de hace más de 2h
        if (m.fecha_hora) {
          const t = new Date(m.fecha_hora.replace(' ', 'T') + '-06:00').getTime();
          return isNaN(t) || t >= now - 7200000;
        }
        return true;
      }
      if (!m.fecha_hora) return false;
      const t = new Date(m.fecha_hora.replace(' ', 'T') + '-06:00').getTime();
      return !isNaN(t) && t >= minus3h && t <= in24h;
    });

    const getTs = m => {
      if (m.timestamp) return m.timestamp * 1000;
      if (m.fecha_hora) return new Date(m.fecha_hora.replace(' ', 'T') + '-06:00').getTime();
      return Infinity;
    };

    const LIVE_WINDOW = 7200000; // 2h en ms — duración estimada de un partido
    const FINISHED    = ['finished', 'ended', 'canceled', 'cancelled', 'postponed'];

    filtered.sort((a, b) => {
      const peso = m => {
        const t = getTs(m);
        // Explícitamente marcado como vivo en el JSON
        if (m.status === 'live') return 0;
        // Iniciado hace menos de 2h y no finalizado → tratar como vivo
        // (cubre status 'inprogress' u otros valores que la fuente devuelva durante el partido)
        if (t > now - LIVE_WINDOW && t <= now && !FINISHED.includes(m.status)) return 0;
        return t >= now ? 1 : 2;                     // próximos → 1, pasados/finalizados → 2
      };
      const pa = peso(a), pb = peso(b);
      if (pa !== pb) return pa - pb;
      const ta = getTs(a), tb = getTs(b);
      return pa === 2 ? tb - ta : ta - tb;           // pasados: más reciente; próximos: más cercano
    });

    slider.innerHTML = '';
    if (filtered.length === 0) {
      slider.innerHTML = '<p style="color:var(--text-muted); padding:1rem 0.5rem; font-size:0.9rem;">No hay partidos disponibles en las próximas 24 horas.</p>';
      return;
    }
    filtered.forEach(m => slider.appendChild(createMatchCard(m)));
    if (typeof guardaHorario === 'function') guardaHorario();
    initCountdowns();
  } catch (e) {
    console.error('Error cargando partidos:', e);
  }
}

function getTeamLogoPath(logo) {
  if (!logo) return '';
  const value = String(logo).trim();
  if (!value) return '';
  if (value.startsWith('http://') || value.startsWith('https://') || value.startsWith('/')) {
    return value;
  }
  return `assets/img/equipos/fm/${encodeURIComponent(value)}.png`;
}

function updateCountdown(el) {
  const timeStr = el.dataset.time;
  if (!timeStr) return;
  // "2026-05-04 08:00:00" en hora de Honduras (UTC-6) → convertir a UTC
  const target = new Date(timeStr.replace(' ', 'T') + '-06:00');
  if (isNaN(target)) return;
  const distance = target - Date.now();
  const extraMs = (parseInt(el.dataset.extraMin, 10) || 0) * 60000;
  const badge = el.closest('.match-status-badge');
  if (distance < 0) {
    if (distance > -(7200000 + extraMs)) {
      el.textContent = '● EN VIVO';
      if (badge) { badge.classList.remove('badge-upcoming', 'badge-finished'); badge.classList.add('badge-live'); }
    } else {
      el.textContent = 'Finalizado';
      if (badge) { badge.classList.remove('badge-live', 'badge-upcoming'); badge.classList.add('badge-finished'); }
    }
    return;
  }
  if (badge) { badge.classList.remove('badge-live'); badge.classList.add('badge-upcoming'); }
  const d = Math.floor(distance / 86400000);
  const h = Math.floor((distance % 86400000) / 3600000);
  const m = Math.floor((distance % 3600000) / 60000);
  const s = Math.floor((distance % 60000) / 1000);
  if (d === 1)            el.textContent = 'Mañana';
  else if (d > 1 && d < 7)    el.textContent = `${d}d ${h}h`;
  else if (d >= 7  && d < 14) el.textContent = 'Próx. Semana';
  else if (d >= 14 && d < 21) el.textContent = '2 Semanas';
  else if (d >= 21 && d < 28) el.textContent = '3 Semanas';
  else if (d >= 28 && d < 60) el.textContent = 'Próx. Mes';
  else if (d >= 60 && d < 90) el.textContent = '2 Meses';
  else if (d >= 90 && d < 120) el.textContent = '3 Meses';
  else if (d === 0 && h > 0)  el.textContent = `${h}h ${m}m ${s}s`;
  else if (h === 0 && m > 0)  el.textContent = `${m}m ${s}s`;
  else                         el.textContent = `${s}s`;
}

function initCountdowns() {
  const els = document.querySelectorAll('.match-countdown');
  if (!els.length) return;
  els.forEach(updateCountdown);
  setInterval(() => els.forEach(updateCountdown), 1000);
}

function createMatchCard(match) {
  // Tarjeta cliqueable: lleva a la página de la liga
  const card = document.createElement('a');
  const tipo = match.tipo || 'soccer';
  const ligaId = match.liga || match.league || '';
  card.href = '?p=liga&id=' + ligaId + '&type=' + tipo;
  card.style.textDecoration = 'none';
  card.className = 'match-card fade-in';
  const isLive = match.status === 'live';
  const isFinished = match.status === 'finished';
  const hasDatetime = !!match.fecha_hora;
  const badgeClass = isLive ? 'badge-live' : 'badge-upcoming';
  // El marcador se muestra en vivo (con polling) y se congela al finalizar
  // (un solo fetch, ver assets/js/live-scores.js) — nunca vuelve a "vs".
  const hasLiveScore = isLive || isFinished;
  // Si hay fecha_hora, el countdown gestiona el estado (EN VIVO → Finalizado)
  const badgeText = hasDatetime
    ? `<span class="match-countdown" data-time="${match.fecha_hora}" data-extra-min="${match.extraMin || 0}"><span class="t">${match.time || '--:--'}</span></span>`
    : isLive
      ? '● EN VIVO'
      : `<span class="t">${match.time || '--:--'}</span>`;
  const leagueName = match.leagueName || match.league || '';

  const theme        = document.documentElement.getAttribute('data-theme') || 'dark';
  const homeBase     = getTeamLogoPath(match.homeTeam?.logo);
  const awayBase     = getTeamLogoPath(match.awayTeam?.logo);
  const leagueBase   = match.leagueLogo || '';
  const homeLogo     = sfLogoSrc(homeBase, theme);
  const awayLogo     = sfLogoSrc(awayBase, theme);
  const leagueLogo   = sfLogoSrc(leagueBase, theme);

  card.innerHTML = `
    <div class="match-league">
      <img src="${leagueLogo}" data-logo-base="${leagueBase}" data-fallback-icon="league" alt="${leagueName}" class="match-league-logo lazy-img" loading="lazy">
      <span class="match-league-name">${leagueName}</span>
      <span class="match-status-badge ${badgeClass}">${badgeText}</span>
    </div>
    <div class="match-teams">
      <div class="match-team">
        <img src="${homeLogo}" data-logo-base="${homeBase}" data-fallback-icon="team" alt="${match.homeTeam.name}" class="team-logo lazy-img" loading="lazy">
        <span class="team-name">${match.homeTeam.name}</span>
      </div>
      <div class="match-score">
        <div class="score-vs"${hasLiveScore ? ` data-live-score="${match.id}"` : ''}>vs</div>
        ${isLive ? `<div class="match-minute" data-live-minute="${match.id}"></div>` : ''}
      </div>
      <div class="match-team">
        <img src="${awayLogo}" data-logo-base="${awayBase}" data-fallback-icon="team" alt="${match.awayTeam.name}" class="team-logo lazy-img" loading="lazy">
        <span class="team-name">${match.awayTeam.name}</span>
      </div>
    </div>
    ${hasLiveScore ? `
    <div class="match-info-footer">
      <span data-live-venue="${match.id}"></span>
      <span data-live-referee="${match.id}"></span>
    </div>` : ''}
  `;
  return card;
}

function scrollSlider(direction) {
  const slider = document.getElementById('matches-slider');
  if (slider) slider.scrollBy({ left: direction === 'left' ? -280 : 280, behavior: 'smooth' });
}

function initSliderDrag() {
  const slider = document.getElementById('matches-slider');
  if (!slider) return;
  let isDown = false, startX, scrollLeft;
  slider.addEventListener('mousedown', e => { isDown = true; slider.classList.add('grabbing'); startX = e.pageX - slider.offsetLeft; scrollLeft = slider.scrollLeft; });
  slider.addEventListener('mouseleave', () => { isDown = false; slider.classList.remove('grabbing'); });
  slider.addEventListener('mouseup', () => { isDown = false; slider.classList.remove('grabbing'); });
  slider.addEventListener('mousemove', e => {
    if (!isDown) return;
    e.preventDefault();
    slider.scrollLeft = scrollLeft - (e.pageX - slider.offsetLeft - startX) * 2;
  });
}

// ============================================================
// CANALES
// ============================================================
let allChannels = [];

async function loadChannels() {
  try {
    const [fRes, cRes] = await Promise.all([
      fetch(cacheBustedUrl('data/fuentes.json')),
      fetch(cacheBustedUrl('data/channels.json'))
    ]);
    const data     = await fRes.json();
    const channels = await cRes.json();
    allChannels = data.filter(ch => ch.activo === 1);

    renderTopChannels(channels, allChannels);
    generateCategoryPills(allChannels);
    initSearch();
  } catch (e) {
    console.error('Error cargando canales:', e);
  }
}

// "Top Channels" del home: una card por CANAL (no por fuente), ordenadas por
// vistas descendente. Cada card enlaza a una fuente representativa de ese
// canal (la primera activa y visible en TV/Home).
function renderTopChannels(channels, fuentes) {
  const grid = document.getElementById('channels-grid');
  if (!grid) return;

  const fuenteIdPorCanal = {};
  fuentes.forEach(f => {
    if (f.mostrar_tv === 0) return;
    if (!(f.canal in fuenteIdPorCanal)) fuenteIdPorCanal[f.canal] = f.id;
  });

  const top = channels
    .filter(c => c.active === 1 && fuenteIdPorCanal[c.id] !== undefined)
    .sort((a, b) => (parseInt(b.views, 10) || 0) - (parseInt(a.views, 10) || 0))
    .slice(0, 12);

  if (!top.length) {
    grid.innerHTML = '<div class="no-results"><i class="fas fa-search" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:.5rem;"></i>No se encontraron canales</div>';
    return;
  }

  grid.innerHTML = '';
  top.forEach((ch, i) => {
    const logoHtml = ch.logo
      ? `<img src="${ch.logo}" alt="${ch.name}" class="channel-logo lazy-img" loading="lazy">`
      : `<i class="fas fa-broadcast-tower" style="color:var(--accent); font-size:2rem;"></i>`;

    const card = document.createElement('a');
    card.href = `?p=canal&id=${fuenteIdPorCanal[ch.id]}`;
    card.className = 'channel-card fade-in';
    card.style.animationDelay = `${i * 0.05}s`;
    card.style.opacity = '0';
    card.innerHTML = `
      <div class="channel-logo-wrapper">${logoHtml}</div>
      <span class="channel-name">${ch.name}</span>
      <span style="display: none" class="channel-category-label">${ch.category}</span>
    `;
    grid.appendChild(card);
  });
}

function generateCategoryPills(channels) {
  const container = document.getElementById('category-pills');
  if (!container) return;
  // Para fuentes, no hay categorías, solo mostrar "Todas las fuentes"
  container.innerHTML = '';
  const btn = document.createElement('button');
  btn.className = 'pill active';
  btn.style.display = 'none'; // Ocultar el botón de categorías ya que no hay categorías para fuentes
  btn.textContent = 'Todas las fuentes';
  btn.dataset.category = 'all';
  btn.addEventListener('click', () => filterByCategory('all', btn));
  container.appendChild(btn);
}

function filterByCategory(category, pill) {
  document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
  pill.classList.add('active');
  const search = document.getElementById('channel-search');
  if (search) search.value = '';
  renderChannels(allChannels);
}

function renderChannels(channels) {
  const grid = document.getElementById('channels-grid');
  if (!grid) return;
  if (!channels.length) {
    grid.innerHTML = '<div class="no-results"><i class="fas fa-search" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:.5rem;"></i>No se encontraron canales</div>';
    return;
  }
  grid.innerHTML = '';
  channels.forEach((ch, i) => grid.appendChild(createChannelCard(ch, i)));
}

function createChannelCard(ch, index) {
  const card = document.createElement('a');
  card.href = `?p=canal&id=${ch.id}`;
  card.className = 'channel-card fade-in';
  card.style.animationDelay = `${index * 0.05}s`;
  card.style.opacity = '0';
  card.innerHTML = `
    <div class="channel-logo-wrapper">
      <i class="fas fa-broadcast-tower" style="color:var(--accent); font-size:2rem;"></i>
    </div>
    <span class="channel-name">${ch.nombre}</span>
    <span style="display: none" class="channel-category-label">Fuente activa</span>
  `;
  return card;
}

function initSearch() {
  const input = document.getElementById('channel-search');
  if (!input) return;
  input.addEventListener('input', e => {
    const q = e.target.value.toLowerCase().trim();
    renderChannels(allChannels.filter(c =>
      c.nombre.toLowerCase().includes(q)
    ));
  });
}

// ============================================================
// CANALES GUARDADOS
// ============================================================
async function loadSavedChannels() {
  if (typeof SAVED_JSON_URL === 'undefined' || !SAVED_JSON_URL) return;
  const section = document.getElementById('saved-section');
  if (!section) return;

  try {
    const res  = await fetch(SAVED_JSON_URL);
    const data = await res.json();
    if (!data.fuentes || data.fuentes.length === 0) return;

    const grid = document.getElementById('saved-channels-grid');
    if (!grid) return;

    grid.innerHTML = '';
    data.fuentes.forEach((ch, i) => grid.appendChild(createSavedCard(ch, i)));
    section.style.display = '';
  } catch (e) { /* silencioso */ }
}

function createSavedCard(ch, index) {
  const card = document.createElement('a');
  card.href  = `?p=canal&id=${ch.id}`;
  card.className = 'channel-card fade-in';
  card.style.animationDelay = `${index * 0.05}s`;
  card.style.opacity = '0';
  const logoHtml = ch.logo
    ? `<img src="${ch.logo}" alt="${ch.nombre}" class="channel-logo lazy-img" loading="lazy">`
    : `<i class="fas fa-bookmark" style="color:var(--accent); font-size:2rem;"></i>`;
  card.innerHTML = `
    <div class="channel-logo-wrapper">${logoHtml}</div>
    <span class="channel-name">${ch.nombre}</span>
    <span class="channel-category-label" style="color:var(--accent); font-size:.65rem;">Guardado</span>
  `;
  return card;
}

// ============================================================
// PARTIDOS DE EQUIPOS FAVORITOS (cualquier liga, próximos o en vivo)
// ============================================================
async function loadFavoriteTeamMatches() {
  if (typeof SAVED_TEAMS_JSON_URL === 'undefined' || !SAVED_TEAMS_JSON_URL) return;
  const section = document.getElementById('team-matches-section');
  const slider  = document.getElementById('team-matches-slider');
  if (!section || !slider) return;

  try {
    const [teamsRes, matchesRes] = await Promise.all([
      fetch(SAVED_TEAMS_JSON_URL),
      fetch(cacheBustedUrl('data/matches.json')),
    ]);
    const teamsData = await teamsRes.json();
    const matches   = await matchesRes.json();

    const favIds = new Set((teamsData.equipos || []).map(e => String(e.id)));
    if (!favIds.size) return;

    const filtered = matches.filter(m => {
      if (m.status !== 'live' && m.status !== 'upcoming') return false;
      return favIds.has(String(m.homeTeam?.logo)) || favIds.has(String(m.awayTeam?.logo));
    });
    if (!filtered.length) return;

    filtered.sort((a, b) => (a.status === 'live' ? 0 : 1) - (b.status === 'live' ? 0 : 1) || (a.timestamp || 0) - (b.timestamp || 0));

    slider.innerHTML = '';
    filtered.forEach(m => slider.appendChild(createMatchCard(m)));
    section.style.display = '';
    initCountdowns();
    if (typeof initLiveScores === 'function') initLiveScores();
  } catch (e) { /* silencioso */ }
}

// ============================================================
// PROGRAMAS EN VIVO
// ============================================================
function convertProgramTimes() {
  document.querySelectorAll('.prog-time-local[data-hn]').forEach(el => {
    const [h, m] = el.dataset.hn.split(':').map(Number);
    if (isNaN(h) || isNaN(m)) return;
    const d = new Date();
    d.setUTCHours(h + 6, m, 0, 0); // hora es UTC-6 (Honduras) → convertir a UTC y luego a local
    el.textContent = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
  });
}
function escHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function canalNombreFromSlug(slug) {
  return slug.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

function calcProgress(horaInicio, horaFin, tsInicio, tsFin) {
  // Usar Unix timestamps cuando estén disponibles (TDT): precisión exacta e independiente de timezone
  if (tsInicio && tsFin) {
    const now = Math.floor(Date.now() / 1000);
    return Math.min(100, Math.max(0, Math.round((now - tsInicio) / (tsFin - tsInicio) * 100)));
  }
  // Fallback: comparar strings HH:MM con hora local del cliente
  const now   = new Date();
  const toMin = str => { const [h, m] = str.split(':').map(Number); return h * 60 + m; };
  const cur   = now.getHours() * 60 + now.getMinutes();
  const start = toMin(horaInicio);
  let   end   = toMin(horaFin);
  if (end <= start) end += 1440;
  return Math.min(100, Math.max(0, Math.round((cur - start) / (end - start) * 100)));
}

async function loadPrograms() {
  const container = document.getElementById('programs-list');
  if (!container) return;
  try {
    const res = await fetch(cacheBustedUrl('data/programas/all.json'));
    if (!res.ok) { container.innerHTML = ''; return; }
    const canales = await res.json();

    // Solo mostrar programas de canales disponibles en fuentes.json (con epg configurado)
    const liveRows = [];
    canales.forEach(canal => {
      const fuente = allChannels.find(ch => ch.epg && ch.epg === canal.canal);
      if (!fuente) return; // canal no disponible, omitir
      (canal.programas || []).forEach(prog => {
        if (prog.en_vivo) liveRows.push({ canal, prog, fuente });
      });
    });

    if (liveRows.length === 0) {
      container.innerHTML = '<p style="color:var(--text-muted);padding:1rem 0;font-size:.9rem;">No hay programas en vivo en este momento.</p>';
      return;
    }

    container.innerHTML = '';
    liveRows.forEach(({ canal, prog, fuente }) => {
      const nombre  = fuente.nombre || canalNombreFromSlug(canal.canal || '');
      const link    = `?p=canal&id=${fuente.id}`;
      const pct     = calcProgress(prog.hora_inicio, prog.hora_fin, prog.ts_inicio, prog.ts_fin);
      const thumbHtml = prog.imagen
        ? `<img src="${escHtml(prog.imagen)}" alt="${escHtml(nombre)}" class="lazy-img" loading="lazy">`
        : `<i class="fas fa-tv" style="color:var(--accent);font-size:1.2rem;"></i>`;

      const row = document.createElement('a');
      row.className = 'program-row';
      row.href = link;
      row.innerHTML = `
        <div class="program-ch">
          <div class="program-ch-thumb">${thumbHtml}</div>
          <div>
            <div class="program-ch-name">${escHtml(nombre)}</div>
            <div class="program-ch-cat">${escHtml(prog.tipo || '')}</div>
          </div>
        </div>
        <div class="program-info">
          <h4>${escHtml(prog.titulo)}</h4>
          <p>${escHtml(prog.descripcion)}</p>
        </div>
        <div class="program-progress-wrap">
          <span class="program-time">
            <span class="prog-time-local" data-hn="${escHtml(prog.hora_inicio)}">${escHtml(prog.hora_inicio)}</span>
            –
            <span class="prog-time-local" data-hn="${escHtml(prog.hora_fin)}">${escHtml(prog.hora_fin)}</span>
          </span>
          <div class="program-bar"><div class="program-bar-fill" style="width:${pct}%"></div></div>
        </div>`;
      container.appendChild(row);
    });
    convertProgramTimes();
  } catch (e) {
    container.innerHTML = ''; // no dejar el skeleton girando para siempre si falla
  }
}

document.addEventListener('DOMContentLoaded', () => {
  loadMatches().then(initSliderDrag).then(() => {
    if (typeof initLiveScores === 'function') initLiveScores();
  });
  loadChannels().then(loadPrograms); // loadPrograms necesita allChannels listo
  loadSavedChannels();
  loadFavoriteTeamMatches();
});
