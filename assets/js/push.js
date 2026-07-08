/**
 * StreamHub - Notificaciones push (equipos favoritos)
 *
 * Registra el service worker (push-sw.js, en la raíz del sitio) y maneja el
 * botón de campana en la navbar: activa/desactiva la suscripción del
 * navegador actual. El envío real de las notificaciones lo hace
 * cron/push_notify.php del lado del servidor cuando un equipo favorito del
 * usuario está por jugar.
 */
function pushSupported() {
  return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
}

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = atob(base64);
  return Uint8Array.from([...rawData].map((c) => c.charCodeAt(0)));
}

let pushRegistration = null;

async function pushInit() {
  if (!pushSupported()) return;
  const btn = document.getElementById('btn-push-toggle');
  if (!btn) return;

  try {
    pushRegistration = await navigator.serviceWorker.register(`${PUSH_BASE_URL}push-sw.js`, { scope: PUSH_BASE_URL });
    const sub = await pushRegistration.pushManager.getSubscription();
    btn.style.display = 'inline-flex';
    pushUpdateIcon(!!sub);
    btn.addEventListener('click', pushToggle);
  } catch (e) {
    console.error('Error registrando service worker de push:', e);
  }
}

function pushUpdateIcon(active) {
  const icon = document.getElementById('push-bell-icon');
  const btn = document.getElementById('btn-push-toggle');
  if (!icon || !btn) return;
  icon.className = active ? 'fas fa-bell' : 'far fa-bell';
  icon.style.color = active ? 'var(--accent)' : '';
  btn.title = active
    ? 'Notificaciones activadas — clic para desactivar'
    : 'Activar notificaciones de tus equipos favoritos';
}

async function pushToggle() {
  if (!pushRegistration) return;
  const existing = await pushRegistration.pushManager.getSubscription();

  if (existing) {
    await pushUnsubscribe(existing);
    return;
  }

  if (Notification.permission === 'denied') {
    pushToast('Bloqueaste las notificaciones en el navegador — actívalas desde la configuración del sitio para usar esto.');
    return;
  }

  try {
    const sub = await pushRegistration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(PUSH_VAPID_PUBLIC_KEY),
    });
    const res = await fetch('api/push_subscribe.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'subscribe', subscription: sub.toJSON() }),
    });
    const data = await res.json();
    if (data.ok) {
      pushUpdateIcon(true);
      pushToast('Notificaciones activadas — te avisaremos cuando jueguen tus equipos favoritos');
    } else {
      pushToast('No se pudo activar la notificación');
    }
  } catch (e) {
    console.error('Error al suscribirse a push:', e);
    pushToast('No se pudo activar la notificación');
  }
}

async function pushUnsubscribe(sub) {
  try {
    const endpoint = sub.endpoint;
    await sub.unsubscribe();
    await fetch('api/push_subscribe.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'unsubscribe', endpoint }),
    });
    pushUpdateIcon(false);
    pushToast('Notificaciones desactivadas');
  } catch (e) {
    console.error('Error al desuscribirse de push:', e);
  }
}

function pushToast(message) {
  let toast = document.getElementById('sh-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'sh-toast';
    toast.style.cssText = 'position:fixed;bottom:2rem;left:50%;transform:translateX(-50%) translateY(10px);background:var(--accent);color:white;padding:.6rem 1.2rem;border-radius:100px;font-size:.85rem;font-weight:600;z-index:9999;opacity:0;transition:all .3s ease;font-family:"DM Sans",sans-serif;max-width:90vw;text-align:center;';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.style.opacity = '1';
  toast.style.transform = 'translateX(-50%) translateY(0)';
  clearTimeout(toast._t);
  toast._t = setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(-50%) translateY(10px)'; }, 3500);
}

document.addEventListener('DOMContentLoaded', pushInit);
