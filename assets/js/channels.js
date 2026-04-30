/**
 * StreamHub - JS de la página de canales (?p=tv)
 * Muestra fuentes activas enriquecidas con logo y categoría del canal padre.
 */

let allItems = []; // fuentes enriquecidas

async function loadChannels() {
  try {
    const [fuentesRes, channelsRes] = await Promise.all([
      fetch('data/fuentes.json'),
      fetch('data/channels.json')
    ]);
    const fuentes  = await fuentesRes.json();
    const channels = await channelsRes.json();

    // Mapa rápido canal_id → canal
    const channelMap = {};
    channels.forEach(c => { channelMap[c.id] = c; });

    // Fuentes activas enriquecidas con datos del canal padre
    allItems = fuentes
      .filter(f => f.activo === 1)
      .map(f => {
        const parent = channelMap[f.canal] ?? null;
        return {
          id:       f.id,
          nombre:   f.nombre,
          logo:     parent?.logo    ?? '',
          category: parent?.category ?? 'Sin categoría',
        };
      });

    generateCategoryPills(allItems);
    renderChannels(allItems);
    initSearch();
  } catch (e) {
    console.error('Error cargando canales:', e);
  }
}

function generateCategoryPills(items) {
  const container = document.getElementById('category-pills');
  if (!container) return;
  const cats = ['Todos', ...new Set(items.map(c => c.category))];
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
  renderChannels(category === 'Todos' ? allItems : allItems.filter(c => c.category === category));
}

function renderChannels(items) {
  const grid = document.getElementById('channels-grid');
  if (!grid) return;
  if (!items.length) {
    grid.innerHTML = '<div class="no-results"><i class="fas fa-search" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:.5rem;"></i>No se encontraron canales</div>';
    return;
  }
  grid.innerHTML = '';
  items.forEach((ch, i) => grid.appendChild(createChannelCard(ch, i)));
}

function createChannelCard(ch, index) {
  const card = document.createElement('a');
  card.href = `?p=canal&id=${ch.id}`;
  card.className = 'channel-card fade-in';
  card.style.animationDelay = `${index * 0.05}s`;
  card.style.opacity = '0';
  card.innerHTML = `
    <div class="channel-logo-wrapper">
      <img src="${ch.logo}" alt="${ch.nombre}" class="channel-logo" onerror="this.style.opacity='0'">
    </div>
    <span class="channel-name">${ch.nombre}</span>
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
    renderChannels(allItems.filter(c =>
      c.nombre.toLowerCase().includes(q) &&
      (activeCat === 'Todos' || c.category === activeCat)
    ));
  });
}

document.addEventListener('DOMContentLoaded', loadChannels);
