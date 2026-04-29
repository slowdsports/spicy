/**
 * StreamHub - JS del reproductor de canal (?p=canal&id=X)
 */

async function loadChannelPage() {
  const id = typeof CHANNEL_ID !== 'undefined' ? CHANNEL_ID : 0;
  if (!id) { window.location.href = '?p=home'; return; }

  try {
    const res = await fetch('data/fuentes.json');
    const sources = await res.json();
    const source = sources.find(c => c.id === id);
    if (!source) { window.location.href = '?p=home'; return; }

    renderPlayerPage(source);
    const recommended = sources.filter(c => c.id !== id && c.activo === 1).slice(0, 8);
    renderRecommendedChannels(recommended);
    startDemoChat();
  } catch (e) {
    console.error('Error cargando fuente:', e);
  }
}

function renderPlayerPage(source) {
  document.title = `${source.nombre} - Tele Deportes`;
  const nameEl = document.getElementById('player-channel-name');
  if (nameEl) nameEl.textContent = source.nombre;

  const iframe = document.getElementById('player-iframe');
  if (iframe && iframe.src) {
    // El src ya está cargado desde PHP, solo mostrar
    const placeholder = document.getElementById('player-placeholder');
    if (placeholder) placeholder.style.display = 'none';
    iframe.style.display = 'block';
  }

  const titleEl = document.getElementById('channel-title');
  if (titleEl) titleEl.textContent = source.nombre;

  const viewsEl = document.getElementById('channel-views');
  if (viewsEl) viewsEl.textContent = `${source.id} transmisión`;
}

function renderRecommendedChannels(sources) {
  const slider = document.getElementById('recommended-slider');
  if (!slider) return;
  slider.innerHTML = '';
  sources.forEach(ch => {
    const card = document.createElement('a');
    card.href = `?p=canal&id=${ch.id}`;
    card.className = 'match-card';
    card.style.cssText = 'min-width:180px;max-width:180px;text-decoration:none;display:flex;flex-direction:column;align-items:center;gap:.75rem;';
    card.innerHTML = `
      <div style="width:60px;height:60px;background:var(--bg-input);border-radius:12px;display:flex;align-items:center;justify-content:center;padding:8px;border:1px solid var(--border);">
        <i class="fas fa-broadcast-tower" style="font-size:1.5rem;color:var(--accent);"></i>
      </div>
      <div style="text-align:center;">
        <div style="font-size:.82rem;font-weight:700;color:var(--text-primary);">${ch.nombre}</div>
        <div style="font-size:.7rem;color:var(--accent);margin-top:2px;">Fuente</div>
      </div>
      <span style="font-size:.65rem;background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3);padding:2px 8px;border-radius:4px;font-family:'Space Mono',monospace;font-weight:700;">● EN VIVO</span>
    `;
    slider.appendChild(card);
  });
}

function scrollRecommended(direction) {
  const slider = document.getElementById('recommended-slider');
  if (slider) slider.scrollBy({ left: direction === 'left' ? -200 : 200, behavior: 'smooth' });
}

// Interacciones
function initInteractionButtons() {
  document.querySelectorAll('.btn-interact').forEach(btn => {
    btn.addEventListener('click', () => {
      const wasActive = btn.classList.contains('active');
      btn.classList.toggle('active', !wasActive);
      btn.style.transform = 'scale(0.9)';
      setTimeout(() => { btn.style.transform = ''; }, 150);
      const msgs = { love: !wasActive ? '❤️ ¡Te gustó!' : 'Like eliminado', report: !wasActive ? '⚠️ Canal reportado' : 'Reporte cancelado', save: !wasActive ? '🔖 Canal guardado' : 'Canal removido' };
      showToast(msgs[btn.dataset.action]);
    });
  });
}

function showToast(message) {
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

// Chat demo
const demoMessages = [
  { user: 'Alex - Admin', text: '¡Bienvenido a Tele Deportes! Este chat se encuentra en desarrollo.', color: '#ef4444' },
];
let msgIdx = 0;

function startDemoChat() {
  const container = document.getElementById('chat-messages');
  if (!container) return;
  addChatMessage(container);
  setInterval(() => addChatMessage(container), Math.random() * 3000 + 3000);
}

function addChatMessage(container) {
  const msg = demoMessages[msgIdx % demoMessages.length];
  msgIdx++;
  const el = document.createElement('div');
  el.className = 'chat-message';
  el.innerHTML = `
    <div class="chat-avatar" style="background:${msg.color};">${msg.user.substring(0,2).toUpperCase()}</div>
    <div class="chat-bubble">
      <div class="chat-user" style="color:${msg.color};">${msg.user}</div>
      <div class="chat-text">${msg.text}</div>
    </div>
  `;
  container.appendChild(el);
  container.scrollTop = container.scrollHeight;
  const all = container.querySelectorAll('.chat-message');
  if (all.length > 50) all[0].remove();
}

document.addEventListener('DOMContentLoaded', () => {
  loadChannelPage();
  initInteractionButtons();
});
