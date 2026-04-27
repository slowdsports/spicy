/**
 * StreamHub - JS del reproductor de canal (?p=canal&id=X)
 */

async function loadChannelPage() {
  const id = typeof CHANNEL_ID !== 'undefined' ? CHANNEL_ID : 0;
  if (!id) { window.location.href = 'index.php?p=home'; return; }

  try {
    const res = await fetch('data/channels.json');
    const channels = await res.json();
    const channel = channels.find(c => c.id === id);
    if (!channel) { window.location.href = 'index.php?p=home'; return; }

    renderPlayerPage(channel);
    const recommended = channels.filter(c => c.id !== id).slice(0, 8);
    renderRecommendedChannels(recommended);
    startDemoChat();
  } catch (e) {
    console.error('Error cargando canal:', e);
  }
}

function renderPlayerPage(channel) {
  document.title = `${channel.name} - StreamHub`;
  const nameEl = document.getElementById('player-channel-name');
  if (nameEl) nameEl.textContent = channel.name;

  const iframe = document.getElementById('player-iframe');
  if (iframe) {
    iframe.src = channel.streamUrl;
    const placeholder = document.getElementById('player-placeholder');
    if (placeholder) placeholder.style.display = 'none';
    iframe.style.display = 'block';
  }

  const avatarImg = document.getElementById('channel-avatar-img');
  if (avatarImg) { avatarImg.src = channel.logo; avatarImg.alt = channel.name; }

  const titleEl = document.getElementById('channel-title');
  if (titleEl) titleEl.textContent = channel.name;

  const viewsEl = document.getElementById('channel-views');
  if (viewsEl) viewsEl.textContent = `${channel.views} espectadores`;
}

function renderRecommendedChannels(channels) {
  const slider = document.getElementById('recommended-slider');
  if (!slider) return;
  slider.innerHTML = '';
  channels.forEach(ch => {
    const card = document.createElement('a');
    card.href = `index.php?p=canal&id=${ch.id}`;
    card.className = 'match-card';
    card.style.cssText = 'min-width:180px;max-width:180px;text-decoration:none;display:flex;flex-direction:column;align-items:center;gap:.75rem;';
    card.innerHTML = `
      <div style="width:60px;height:60px;background:var(--bg-input);border-radius:12px;display:flex;align-items:center;justify-content:center;padding:8px;border:1px solid var(--border);">
        <img src="${ch.logo}" alt="${ch.name}" style="width:100%;height:100%;object-fit:contain;filter:brightness(0) invert(1);" onerror="this.style.filter='none'">
      </div>
      <div style="text-align:center;">
        <div style="font-size:.82rem;font-weight:700;color:var(--text-primary);">${ch.name}</div>
        <div style="font-size:.7rem;color:var(--accent);margin-top:2px;">${ch.category}</div>
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
  { user: 'Alex - Admin', text: '¡Bienvenido a StreamHub! Este chat se encuentra en desarrollo.', color: '#ef4444' },
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
