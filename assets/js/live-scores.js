/**
 * StreamHub - Marcador en vivo (FotMob)
 * Actualiza elementos [data-live-score], [data-live-minute], [data-live-venue]
 * y [data-live-referee] (todos con el mismo valor: el partido-id) en
 * cualquier plantilla — home, liga.php, mundial2026.php, canal.php...
 *
 * Partidos en vivo: se re-consultan cada LIVE_SCORE_POLL_MS.
 * Partidos ya finalizados (según FotMob, no según nuestro estimado local):
 * se consultan una sola vez y el resultado queda congelado — no tiene
 * sentido seguir pegándole a la API por algo que no se va a mover más.
 */
const LIVE_SCORE_POLL_MS = 25000;
let liveScoreTimer = null;
const settledIds = new Set();

function collectLiveIds() {
  const ids = new Set();
  document.querySelectorAll('[data-live-score]').forEach(el => ids.add(el.dataset.liveScore));
  document.querySelectorAll('[data-live-minute]').forEach(el => ids.add(el.dataset.liveMinute));
  document.querySelectorAll('[data-live-venue]').forEach(el => ids.add(el.dataset.liveVenue));
  document.querySelectorAll('[data-live-referee]').forEach(el => ids.add(el.dataset.liveReferee));
  settledIds.forEach(id => ids.delete(id));
  return ids;
}

async function pollLiveScores() {
  const ids = collectLiveIds();
  if (!ids.size) return;

  await Promise.all(Array.from(ids).map(async (id) => {
    try {
      const res = await fetch(`api/live_score.php?id=${encodeURIComponent(id)}`);
      if (!res.ok) return;
      const data = await res.json();
      if (!data.ok) return;

      if (data.homeScore !== null && data.awayScore !== null) {
        document.querySelectorAll(`[data-live-score="${id}"]`).forEach(el => {
          el.textContent = `${data.homeScore} – ${data.awayScore}`;
        });
      }
      // El minuto sí puede volver a null (ej. entretiempo real vs nuestro
      // cálculo) así que a diferencia del resto, si viene null no tocamos el
      // texto anterior — mejor un dato viejo que borrar el minuto a medias.
      if (data.minute) {
        document.querySelectorAll(`[data-live-minute="${id}"]`).forEach(el => {
          el.textContent = data.minute;
        });
      }
      if (data.venue) {
        document.querySelectorAll(`[data-live-venue="${id}"]`).forEach(el => {
          el.textContent = data.venue;
        });
      }
      if (data.referee) {
        document.querySelectorAll(`[data-live-referee="${id}"]`).forEach(el => {
          el.textContent = data.referee;
        });
      }

      // Evento genérico para que cualquier página reaccione al dato fresco
      // (ej. pages/partido.php lo usa para pintar equipo ganador/perdedor)
      // sin que este script tenga que conocer la estructura de cada una.
      document.dispatchEvent(new CustomEvent('livescore:update', { detail: { id, ...data } }));

      // FotMob ya lo da por terminado (independiente de nuestra ventana local
      // de 3h+extra) — el marcador queda fijo, no hace falta seguir pidiéndolo.
      if (data.finished) settledIds.add(id);
    } catch (e) {
      // Silencioso: si falla una vuelta, se queda con el último valor visto
      // y lo reintenta en el próximo ciclo (a menos que ya esté "settled").
    }
  }));
}

function initLiveScores() {
  pollLiveScores();
  if (liveScoreTimer) clearInterval(liveScoreTimer);
  liveScoreTimer = setInterval(pollLiveScores, LIVE_SCORE_POLL_MS);
}

document.addEventListener('DOMContentLoaded', () => {
  if (collectLiveIds().size) initLiveScores();
});
