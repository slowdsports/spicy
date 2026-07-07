# Sofascore Bridge

Sofascore bloquea con `403` cualquier cliente que no sea un navegador real
(fingerprint TLS vía Fastly Bot Manager) — afecta a `curl`, PHP-curl, y
cualquier librería HTTP normal, sin importar los headers que le mandes.

Este bridge abre un Chromium real (Playwright), visita el home de Sofascore
para "calentar" la sesión, y expone un endpoint HTTP que reenvía peticiones a
la API de Sofascore usando ese navegador — pasa el bot-check porque es
indistinguible de un usuario real.

`admin/sofa.php` lo usa de dos formas (ver `sofaFetch()`):

- **Local (dev):** si `SOFA_BRIDGE_URL` no está seteado en `includes/.env.php`,
  invoca `fetch.js` directo con `proc_open` — no hace falta tener el server
  HTTP corriendo aparte.
- **Producción (hosting compartido sin Node):** con `SOFA_BRIDGE_URL` seteado,
  le pega por HTTP a este servicio desplegado en Render.

## Desarrollo local

```
cd tools/sofascore-bridge
npm install
npx playwright install chromium
node fetch.js "https://api.sofascore.com/api/v1/unique-tournament/17/seasons"   # prueba suelta
node server.js                                                                   # levanta el server HTTP en :4321
```

## Deploy en Render (plan free)

1. Sube este repo a GitHub (si no lo está ya).
2. En Render: **New → Web Service** → conecta el repo.
3. **Root Directory:** `tools/sofascore-bridge`
4. **Runtime:** Docker (Render detecta el `Dockerfile` solo).
5. **Instance Type:** Free.
6. **Environment Variables:**
   - `BRIDGE_SECRET` = un valor random largo (ej. genera uno con
     `openssl rand -hex 32`). Sin esto, cualquiera que encuentre la URL del
     bridge podría usarlo como proxy abierto hacia Sofascore.
7. Deploy. La primera build tarda unos minutos (descarga la imagen base de
   Playwright con Chromium incluido).
8. Copia la URL pública que te da Render (`https://xxxx.onrender.com`).

En `includes/.env.php` de producción (el que vive solo en el servidor cPanel,
nunca en git):

```php
'SOFA_BRIDGE_URL'    => 'https://xxxx.onrender.com',
'SOFA_BRIDGE_SECRET' => 'el-mismo-valor-que-BRIDGE_SECRET-en-render',
```

## Limitaciones del plan free de Render

- **Cold start:** el servicio se duerme tras ~15 min sin requests. La primera
  petición después de dormir tarda ~30-50s en despertar (por eso
  `sofaFetch()` usa un timeout de 45s en el lado PHP). Las siguientes son
  rápidas mientras siga despierto.
- **512MB RAM / recursos compartidos:** si Chromium se cae por memoria, el
  bridge lo relanza solo en la siguiente request (ver `getContext()` en
  `server.js`), a costa de otro cold start.
- **50GB/mes de banda** (tal como esté tu plan) — el tráfico de esto es
  mínimo (solo JSON de partidos, no video), no debería acercarse al límite
  con uso normal de importación de partidos.

## Seguridad

- `/fetch` exige el header `x-bridge-key` si `BRIDGE_SECRET` está seteado —
  siempre setealo en Render, o cualquiera con la URL puede usar tu Chromium
  como proxy gratis.
- Solo se permiten URLs bajo `*.sofascore.com` (ver `isAllowedUrl` en
  `server.js`) — no es un proxy HTTP genérico.
