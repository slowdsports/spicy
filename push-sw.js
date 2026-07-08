/**
 * StreamHub - Service Worker de notificaciones push
 *
 * Vive en la raíz del sitio (no en assets/js/) a propósito: el scope por
 * defecto de un service worker es el directorio donde vive el archivo, y
 * necesita controlar clicks de notificación hacia cualquier página del
 * sitio (partido, canal, liga...), sin importar si el sitio corre en la
 * raíz del dominio o en un subdirectorio (BASE_URL).
 */

self.addEventListener('push', (event) => {
  if (!event.data) return;

  let payload;
  try {
    payload = event.data.json();
  } catch (e) {
    payload = { title: 'StreamHub', body: event.data.text() };
  }

  const title = payload.title || 'StreamHub';
  const options = {
    body: payload.body || '',
    icon: payload.icon || undefined,
    badge: payload.badge || undefined,
    data: { url: payload.url || '/' },
    tag: payload.tag || undefined, // agrupa/reemplaza notificaciones del mismo partido
    renotify: !!payload.tag,
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = (event.notification.data && event.notification.data.url) || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      for (const client of windowClients) {
        if (client.url === url && 'focus' in client) return client.focus();
      }
      if (clients.openWindow) return clients.openWindow(url);
    })
  );
});
