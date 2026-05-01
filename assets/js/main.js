/**
 * StreamHub - JS de la página principal (home)
 * Carga partidos y canales desde JSON.
 */

// ============================================================
// SLIDER DE PARTIDOS
// ============================================================
async function loadMatches() {
  try {
    const res = await fetch('data/matches.json');
    const matches = await res.json();
    const slider = document.getElementById('matches-slider');
    if (!slider) return;

    const now = Date.now();
    const in24h = now + 86400000;    // 24 horas hacia adelante
    const minus3h = now - 10800000;  // 3 horas atrás (partidos que pueden seguir en curso)

    const filtered = matches.filter(m => {
      if (m.status === 'live') return true;
      if (!m.fecha_hora) return false;
      const t = new Date(m.fecha_hora.replace(' ', 'T') + '-06:00').getTime();
      return !isNaN(t) && t >= minus3h && t <= in24h;
    });

    const getTs = m => {
      if (m.timestamp) return m.timestamp * 1000;
      if (m.fecha_hora) return new Date(m.fecha_hora.replace(' ', 'T') + '-06:00').getTime();
      return Infinity;
    };

    filtered.sort((a, b) => {
      const peso = m => {
        if (m.status === 'live') return 0;           // en vivo primero
        return getTs(m) >= now ? 1 : 2;              // próximos, luego pasados
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
  return `assets/img/equipos/sf/${encodeURIComponent(value)}.png`;
}

function updateCountdown(el) {
  const timeStr = el.dataset.time;
  if (!timeStr) return;
  // "2026-05-04 08:00:00" en hora de Honduras (UTC-6) → convertir a UTC
  const target = new Date(timeStr.replace(' ', 'T') + '-06:00');
  if (isNaN(target)) return;
  const distance = target - Date.now();
  const badge = el.closest('.match-status-badge');
  if (distance < 0) {
    if (distance > -10800000) {
      el.textContent = '● EN VIVO';
      if (badge) { badge.classList.remove('badge-upcoming'); badge.classList.add('badge-live'); }
    } else {
      el.textContent = 'Finalizó';
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
  document.querySelectorAll('.match-countdown').forEach(el => {
    updateCountdown(el);
    setInterval(() => updateCountdown(el), 1000);
  });
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
  const badgeClass = isLive ? 'badge-live' : 'badge-upcoming';
  const hasDatetime = !isLive && match.fecha_hora;
  const badgeText = isLive
    ? '● EN VIVO'
    : hasDatetime
      ? `<span class="match-countdown" data-time="${match.fecha_hora}"><span class="t">${match.time || '--:--'}</span></span>`
      : `<span class="t">${match.time || '--:--'}</span>`;
  const leagueName = match.leagueName || match.league || '';
  const homeLogo = getTeamLogoPath(match.homeTeam?.logo);
  const awayLogo = getTeamLogoPath(match.awayTeam?.logo);

  card.innerHTML = `
    <div class="match-league">
      <img src="${match.leagueLogo}" alt="${leagueName}" class="match-league-logo" onerror="this.style.display='none'">
      <span class="match-league-name">${leagueName}</span>
      <span class="match-status-badge ${badgeClass}">${badgeText}</span>
    </div>
    <div class="match-teams">
      <div class="match-team">
        <img src="${homeLogo}" alt="${match.homeTeam.name}" class="team-logo" onerror="this.style.opacity='0'">
        <span class="team-name">${match.homeTeam.name}</span>
      </div>
      <div class="score-vs">vs</div>
      <div class="match-team">
        <img src="${awayLogo}" alt="${match.awayTeam.name}" class="team-logo" onerror="this.style.opacity='0'">
        <span class="team-name">${match.awayTeam.name}</span>
      </div>
    </div>
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
    const res  = await fetch('data/fuentes.json');
    const data = await res.json();
    allChannels = data.filter(ch => ch.activo === 1);

    const page   = new URLSearchParams(window.location.search).get('p') || 'home';
    const toShow = page === 'home' ? allChannels.slice(0, 12) : allChannels;

    generateCategoryPills(allChannels);
    renderChannels(toShow);
    initSearch();
  } catch (e) {
    console.error('Error cargando canales:', e);
  }
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
    const res  = await fetch(SAVED_JSON_URL + '?t=' + Date.now());
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
    ? `<img src="${ch.logo}" alt="${ch.nombre}" class="channel-logo" onerror="this.style.opacity='0'">`
    : `<i class="fas fa-bookmark" style="color:var(--accent); font-size:2rem;"></i>`;
  card.innerHTML = `
    <div class="channel-logo-wrapper">${logoHtml}</div>
    <span class="channel-name">${ch.nombre}</span>
    <span class="channel-category-label" style="color:var(--accent); font-size:.65rem;">Guardado</span>
  `;
  return card;
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
    const res = await fetch('data/programas/all.json');
    if (!res.ok) return;
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
        ? `<img src="${escHtml(prog.imagen)}" alt="${escHtml(nombre)}" onerror="this.style.display='none'" loading="lazy">`
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
  } catch (e) { /* silencioso */ }
}

document.addEventListener('DOMContentLoaded', () => {
  loadMatches().then(initSliderDrag);
  loadChannels().then(loadPrograms); // loadPrograms necesita allChannels listo
  loadSavedChannels();
});
