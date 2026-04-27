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
    slider.innerHTML = '';
    matches.forEach(m => slider.appendChild(createMatchCard(m)));
  } catch (e) {
    console.error('Error cargando partidos:', e);
  }
}

function createMatchCard(match) {
  // Tarjeta cliqueable: lleva a la página de la liga
  const card = document.createElement('a');
  const tipo = match.tipo || 'soccer';
  const ligaId = match.liga || match.leagueId || '';
  card.href = 'index.php?p=liga&id=' + ligaId + '&type=' + tipo;
  card.style.textDecoration = 'none';
  card.className = 'match-card fade-in';
  const isLive = match.status === 'live';
  const badgeClass = isLive ? 'badge-live' : 'badge-upcoming';
  const badgeText  = isLive ? '● EN VIVO' : match.time;
  const scoreHTML  = isLive
    ? `<div class="score-numbers">${match.homeTeam.score} - ${match.awayTeam.score}</div><div class="score-time">${match.time}</div>`
    : `<div class="score-vs">vs</div><div class="score-time">${match.time}</div>`;

  card.innerHTML = `
    <div class="match-league">
      <img src="${match.leagueLogo}" alt="${match.league}" class="match-league-logo" onerror="this.style.display='none'">
      <span class="match-league-name">${match.league}</span>
      <span class="match-status-badge ${badgeClass}">${badgeText}</span>
    </div>
    <div class="match-teams">
      <div class="match-team">
        <img src="${match.homeTeam.logo}" alt="${match.homeTeam.name}" class="team-logo" onerror="this.style.opacity='0'">
        <span class="team-name">${match.homeTeam.name}</span>
      </div>
      <div class="match-score">${scoreHTML}</div>
      <div class="match-team">
        <img src="${match.awayTeam.logo}" alt="${match.awayTeam.name}" class="team-logo" onerror="this.style.opacity='0'">
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
    const res = await fetch('data/channels.json');
    allChannels = await res.json();
    generateCategoryPills(allChannels);
    renderChannels(allChannels);
    initSearch();
  } catch (e) {
    console.error('Error cargando canales:', e);
  }
}

function generateCategoryPills(channels) {
  const container = document.getElementById('category-pills');
  if (!container) return;
  const cats = ['Todos', ...new Set(channels.map(c => c.category))];
  container.innerHTML = '';
  cats.forEach(cat => {
    const btn = document.createElement('button');
    btn.className = `pill${cat === 'Todos' ? ' active' : ''}`;
    btn.textContent = cat;
    btn.dataset.category = cat;
    btn.addEventListener('click', () => filterByCategory(cat, btn));
    container.appendChild(btn);
  });
}

function filterByCategory(category, pill) {
  document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
  pill.classList.add('active');
  const search = document.getElementById('channel-search');
  if (search) search.value = '';
  renderChannels(category === 'Todos' ? allChannels : allChannels.filter(c => c.category === category));
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
  card.href = `index.php?p=canal&id=${ch.id}`;
  card.className = 'channel-card fade-in';
  card.style.animationDelay = `${index * 0.05}s`;
  card.style.opacity = '0';
  card.innerHTML = `
    <div class="channel-logo-wrapper">
      <img src="${ch.logo}" alt="${ch.name}" class="channel-logo" onerror="this.style.opacity='0'">
    </div>
    <span class="channel-name">${ch.name}</span>
    <span class="channel-category-label">${ch.category}</span>
  `;
  return card;
}

function initSearch() {
  const input = document.getElementById('channel-search');
  if (!input) return;
  input.addEventListener('input', e => {
    const q = e.target.value.toLowerCase().trim();
    const activeCat = document.querySelector('.pill.active')?.dataset.category ?? 'Todos';
    renderChannels(allChannels.filter(c =>
      c.name.toLowerCase().includes(q) &&
      (activeCat === 'Todos' || c.category === activeCat)
    ));
  });
}

document.addEventListener('DOMContentLoaded', () => {
  loadMatches().then(initSliderDrag);
  loadChannels();
});
