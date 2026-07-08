/**
 * StreamHub - Página de partido (?p=partido&id=X)
 * Llena los skeletons de tabla de posiciones / h2h / alineaciones / MVP /
 * estadísticas / timeline pidiendo api/partido_extra.php de forma
 * asíncrona — el header y los canales ya salieron server-side (instantáneo,
 * sin depender de FotMob), esto es solo lo que sí necesita esperar a
 * FotMob (1-3s).
 */
function ppTeamLogo(id) {
  const folder = 'fm'; // único proveedor en uso — ver includes/config.php logoFolder()
  return `assets/img/equipos/${folder}/${id}.png`;
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = String(str ?? '');
  return div.innerHTML;
}

function ppToast(message) {
  let toast = document.getElementById('sh-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'sh-toast';
    toast.style.cssText = 'position:fixed;bottom:2rem;left:50%;transform:translateX(-50%) translateY(10px);background:var(--accent);color:white;padding:.6rem 1.2rem;border-radius:100px;font-size:.85rem;font-weight:600;z-index:9999;opacity:0;transition:all .3s ease;font-family:"DM Sans",sans-serif;';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.style.opacity = '1';
  toast.style.transform = 'translateX(-50%) translateY(0)';
  clearTimeout(toast._t);
  toast._t = setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(-50%) translateY(10px)'; }, 2500);
}

/* ── Equipo favorito (estrella junto al escudo) ──────────────────────── */
document.querySelectorAll('.pp-fav-star').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!PP_IS_LOGGED_IN) {
      ppToast('Inicia sesión para guardar equipos favoritos');
      return;
    }
    const equipoId = btn.dataset.equipoId;
    btn.disabled = true;
    try {
      const fd = new FormData();
      fd.append('action', 'save_equipo');
      fd.append('equipo_id', equipoId);
      const res = await fetch('api/interacciones.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.ok) {
        btn.classList.toggle('active', data.active);
        ppToast(data.active ? 'Equipo agregado a favoritos' : 'Equipo quitado de favoritos');
      } else {
        ppToast(data.msg || 'Error al guardar');
      }
    } catch (e) {
      ppToast('Error de conexión');
    }
    btn.disabled = false;
  });
});

function ppHide(sectionId) {
  const el = document.getElementById(sectionId);
  if (el) el.style.display = 'none';
}
function ppShow(sectionId) {
  const el = document.getElementById(sectionId);
  if (el) el.style.display = '';
}

/* ── Ganador / perdedor: reacciona al evento genérico de live-scores.js ── */
document.addEventListener('livescore:update', (e) => {
  const d = e.detail;
  if (String(d.id) !== String(PP_PARTIDO_ID)) return;
  if (!d.finished || d.homeScore === null || d.awayScore === null) return;

  const home = document.getElementById('pp-team-home');
  const away = document.getElementById('pp-team-away');
  if (!home || !away) return;

  home.classList.remove('pp-winner', 'pp-loser');
  away.classList.remove('pp-winner', 'pp-loser');

  if (d.homeScore > d.awayScore) {
    home.classList.add('pp-winner');
    away.classList.add('pp-loser');
  } else if (d.awayScore > d.homeScore) {
    away.classList.add('pp-winner');
    home.classList.add('pp-loser');
  }
  // Empate: no se agrega ninguna clase, colores neutrales para ambos.

  const badge = document.getElementById('pp-status-badge');
  if (badge) {
    badge.className = 'pp-status-badge pp-status-finished';
    badge.textContent = 'Finalizado';
  }
});

/* ── Goleadores bajo el nombre de cada equipo ────────────────────────── */
function ppRenderGoals(goals, targetId) {
  const el = document.getElementById(targetId);
  if (!el || !goals || !goals.length) return;
  el.innerHTML = goals.map(g => `
    <div class="pp-goal-row">
      <i class="fas fa-futbol"></i>
      <span>${escapeHtml(g.minute)}' ${escapeHtml(g.player)}${g.ownGoal ? ' (p.p.)' : ''}</span>
    </div>
  `).join('');
}

/* ── MVP ──────────────────────────────────────────────────────────────── */
function ppRenderMvp(mvp) {
  if (!mvp || !mvp.name) { ppHide('pp-mvp-section'); return; }
  const body = document.getElementById('pp-mvp-body');
  const photoTag = mvp.photo
    ? `<img class="lazy-img" loading="lazy" src="${escapeHtml(mvp.photo)}" data-fallback-icon="team">`
    : `<div class="pp-mvp-photo-fallback"><i class="fas fa-user"></i></div>`;
  body.innerHTML = `
    <div class="pp-mvp">
      ${photoTag}
      <div class="pp-mvp-info">
        <div class="pp-mvp-name">${escapeHtml(mvp.name)} ${mvp.rating ? `<span class="pp-mvp-rating">${escapeHtml(mvp.rating)}</span>` : ''}</div>
        <div class="pp-mvp-team">${escapeHtml(mvp.team)}</div>
        <div class="pp-mvp-stats">
          ${(mvp.stats || []).filter(s => s.value !== null && s.value !== undefined).map(s => `
            <span class="pp-mvp-stat">${escapeHtml(s.label)}: <b>${escapeHtml(s.value)}</b></span>
          `).join('')}
        </div>
      </div>
    </div>
  `;
  ppShow('pp-mvp-section');
}

/* ── Estadísticas comparativas (posesión, tiros, etc.) ──────────────────── */
function ppRenderStats(rows) {
  if (!rows || !rows.length) { ppHide('pp-stats-section'); return; }
  const body = document.getElementById('pp-stats-body');
  body.innerHTML = rows.map(r => {
    const total = (Number(r.home) || 0) + (Number(r.away) || 0);
    const homePct = total > 0 ? Math.round((Number(r.home) / total) * 100) : 50;
    return `
    <div class="pp-stat-row">
      <div class="pp-stat-labels">
        <span class="pp-stat-home">${escapeHtml(r.home)}${r.isPercent ? '%' : ''}</span>
        <span class="pp-stat-title">${escapeHtml(r.title)}</span>
        <span class="pp-stat-away">${escapeHtml(r.away)}${r.isPercent ? '%' : ''}</span>
      </div>
      <div class="pp-stat-bar">
        <div class="pp-stat-bar-home" style="width:${homePct}%"></div>
        <div class="pp-stat-bar-away" style="width:${100 - homePct}%"></div>
      </div>
    </div>`;
  }).join('');
  ppShow('pp-stats-section');
}

/* ── Timeline ─────────────────────────────────────────────────────────── */
const PP_TL_ICONS = {
  Goal:          { icon: 'fa-futbol', cls: 'goal' },
  Substitution:  { icon: 'fa-right-left', cls: 'sub' },
  YellowCard:    { icon: 'fa-square', cls: 'yellow' },
  RedCard:       { icon: 'fa-square', cls: 'red' },
  YellowRedCard: { icon: 'fa-square', cls: 'red' },
  AddedTime:     { icon: 'fa-clock', cls: '' },
};

function ppTimelineText(ev) {
  switch (ev.type) {
    case 'Goal':
      return `${ev.ownGoal ? 'Autogol' : 'Gol'} de ${escapeHtml(ev.player)}` +
        (ev.assist ? `<small>Asistencia: ${escapeHtml(ev.assist)}</small>` : '');
    case 'Substitution':
      return `${escapeHtml(ev.playerIn)} entra <small>Sale: ${escapeHtml(ev.playerOut)}</small>`;
    case 'YellowCard':
      return `Tarjeta amarilla — ${escapeHtml(ev.player)}`;
    case 'RedCard':
    case 'YellowRedCard':
      return `Tarjeta roja — ${escapeHtml(ev.player)}`;
    case 'AddedTime':
      return `Tiempo añadido: +${escapeHtml(ev.minutesAdded)} min`;
    default:
      return '';
  }
}

function ppRenderTimeline(events) {
  if (!events || !events.length) { ppHide('pp-timeline-section'); return; }
  const body = document.getElementById('pp-timeline-body');
  // Más reciente primero — es lo que se quiere ver de un vistazo en vivo o ya terminado
  const sorted = events.slice().sort((a, b) => b.time - a.time);
  body.innerHTML = `<div class="pp-timeline">${sorted.map(ev => {
    const meta = PP_TL_ICONS[ev.type] || { icon: 'fa-circle', cls: '' };
    const side = ev.isHome === true ? 'Local' : (ev.isHome === false ? 'Visita' : '');
    return `
    <div class="pp-tl-item">
      <span class="pp-tl-minute">${escapeHtml(ev.minute)}'</span>
      <span class="pp-tl-icon ${meta.cls}"><i class="fas ${meta.icon}"></i></span>
      <span class="pp-tl-text">${ppTimelineText(ev)}</span>
      <span class="pp-tl-side">${side}</span>
    </div>`;
  }).join('')}</div>`;
  ppShow('pp-timeline-section');
}

/* ── Tabla de posiciones ──────────────────────────────────────────────── */
function ppRenderStandings(rows) {
  if (!rows || !rows.length) {
    ppHide('pp-standings-section');
    return;
  }
  const body = document.getElementById('pp-standings-body');
  const wrap = document.createElement('div');
  wrap.style.overflowX = 'auto';
  const table = document.createElement('table');
  table.className = 'pp-table';
  table.innerHTML = `
    <thead>
      <tr>
        <th>#</th><th>Equipo</th><th class="pp-num">PJ</th><th class="pp-num">G</th>
        <th class="pp-num">E</th><th class="pp-num">P</th><th class="pp-num">GF-GC</th>
        <th class="pp-num">DG</th><th class="pp-num">Pts</th>
      </tr>
    </thead>
    <tbody>
      ${rows.map(row => {
        const highlight = row.id === PP_LOCAL_ID || row.id === PP_VISIT_ID;
        return `
        <tr class="${highlight ? 'pp-row-highlight' : ''}">
          <td class="pp-num">${row.idx || 0}</td>
          <td>
            <div class="pp-team-cell">
              <img class="lazy-img" loading="lazy" src="${ppTeamLogo(row.id)}" onerror="this.style.display='none'">
              ${escapeHtml(row.shortName || row.name || '')}
            </div>
          </td>
          <td class="pp-num">${row.played || 0}</td>
          <td class="pp-num">${row.wins || 0}</td>
          <td class="pp-num">${row.draws || 0}</td>
          <td class="pp-num">${row.losses || 0}</td>
          <td class="pp-num">${escapeHtml(row.scoresStr || '-')}</td>
          <td class="pp-num">${row.goalConDiff || 0}</td>
          <td class="pp-num">${row.pts || 0}</td>
        </tr>`;
      }).join('')}
    </tbody>
  `;
  wrap.appendChild(table);
  body.innerHTML = '';
  body.appendChild(wrap);
}

/* ── Head to head ─────────────────────────────────────────────────────── */
function ppRenderH2h(matches) {
  if (!matches || !matches.length) {
    ppHide('pp-h2h-section');
    return;
  }
  const body = document.getElementById('pp-h2h-body');
  body.innerHTML = matches.slice(0, 6).map(hm => {
    const d = hm.time && hm.time.utcTime ? new Date(hm.time.utcTime) : null;
    const dateStr = d ? d.toLocaleDateString('es', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '';
    return `
    <div class="pp-h2h-item">
      <span class="pp-h2h-date">${dateStr}</span>
      <span class="pp-h2h-teams">${escapeHtml(hm.home?.name || '')} vs ${escapeHtml(hm.away?.name || '')}</span>
      <span class="pp-h2h-score">${escapeHtml(hm.status?.scoreStr || '-')}</span>
    </div>`;
  }).join('');
}

/* ── Alineaciones ─────────────────────────────────────────────────────── */
function ppRenderLineupTeam(team) {
  const meta = `${escapeHtml(team.formation || '')}${team.coach?.name ? ' · DT: ' + escapeHtml(team.coach.name) : ''}`;
  const starters = (team.starters || []).map(pl => `
    <div class="pp-player-row">
      <span class="pp-player-num">${escapeHtml(pl.shirtNumber || '')}</span>
      <span>${escapeHtml(pl.name || '')}</span>
    </div>`).join('');
  const subs = (team.subs || []).length ? `
    <div class="pp-subs-title">Suplentes</div>
    ${team.subs.map(pl => `
    <div class="pp-player-row">
      <span class="pp-player-num">${escapeHtml(pl.shirtNumber || '')}</span>
      <span>${escapeHtml(pl.name || '')}</span>
    </div>`).join('')}` : '';
  return `<div class="pp-lineup-meta">${meta}</div>${starters}${subs}`;
}

function ppRenderLineup(lineup) {
  if (!lineup || !lineup.home || !lineup.away) {
    ppHide('pp-lineup-section');
    return;
  }
  const body = document.getElementById('pp-lineup-body');
  body.className = 'pp-lineups';
  body.innerHTML = `
    <div class="pp-lineup-col">${ppRenderLineupTeam(lineup.home)}</div>
    <div class="pp-lineup-col">${ppRenderLineupTeam(lineup.away)}</div>
  `;
}

async function ppLoadExtra() {
  try {
    const res = await fetch(`api/partido_extra.php?id=${PP_PARTIDO_ID}&liga=${PP_LIGA_ID}`);
    const data = await res.json();
    if (!data.ok) throw new Error('respuesta no ok');

    ppRenderGoals(data.homeGoals, 'pp-home-goals');
    ppRenderGoals(data.awayGoals, 'pp-away-goals');
    ppRenderMvp(data.mvp);
    ppRenderStats(data.teamStats);
    ppRenderTimeline(data.timeline);
    ppRenderStandings(data.standings);
    ppRenderH2h(data.h2h);
    ppRenderLineup(data.lineup);
  } catch (e) {
    // Si falla, ocultamos los skeletons en vez de dejarlos animando para siempre
    ['pp-standings-section', 'pp-h2h-section', 'pp-lineup-section', 'pp-mvp-section', 'pp-stats-section', 'pp-timeline-section'].forEach(ppHide);
    console.error('Error cargando datos extendidos del partido:', e);
  }
}

document.addEventListener('DOMContentLoaded', ppLoadExtra);
