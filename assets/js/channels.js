/**
 * StreamHub - JS de la página de canales (?p=tv)
 * Reutiliza las mismas funciones pero con paths correctos
 */

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
  card.href = `?p=canal&id=${ch.id}`;
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

document.addEventListener('DOMContentLoaded', loadChannels);
