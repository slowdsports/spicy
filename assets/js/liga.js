/**
 * StreamHub - JS de la página de liga (?p=liga&id=X&type=Y)
 */

function updateCountdown(el) {
  const ts = parseInt(el.dataset.ts, 10);
  let distance;
  if (ts > 0) {
    distance = ts * 1000 - Date.now();
  } else {
    // fallback para entradas antiguas sin data-ts (fecha_hora en hora Honduras UTC-6)
    const timeStr = el.dataset.time;
    if (!timeStr) return;
    const target = new Date(timeStr.replace(' ', 'T') + '-06:00');
    if (isNaN(target)) return;
    distance = target - Date.now();
  }
  const badge = el.closest('.badge-time');
  if (distance < 0) {
    if (distance > -10800000) {
      el.textContent = '● EN VIVO';
      if (badge) {
        badge.classList.remove('badge-time');
        badge.classList.add('badge-live');
        const icon = badge.querySelector('i');
        if (icon) icon.remove();
      }
    } else {
      el.textContent = 'Finalizó';
    }
    return;
  }
  const d = Math.floor(distance / 86400000);
  const h = Math.floor((distance % 86400000) / 3600000);
  const m = Math.floor((distance % 3600000) / 60000);
  const s = Math.floor((distance % 60000) / 1000);
  if (d === 1)             el.textContent = 'Mañana';
  else if (d > 1 && d < 7)    el.textContent = `${d}d ${h}h`;
  else if (d >= 7  && d < 14) el.textContent = 'Próx. Semana';
  else if (d >= 14 && d < 21) el.textContent = '2 Semanas';
  else if (d >= 21 && d < 28) el.textContent = '3 Semanas';
  else if (d >= 28 && d < 60) el.textContent = 'Próx. Mes';
  else if (d >= 60 && d < 90) el.textContent = '2 Meses';
  else if (d >= 90 && d < 120) el.textContent = '3 Meses';
  else if (d === 0 && h > 0)  el.textContent = `${h}h ${m}m ${s}s`;
  else if (h === 0 && m > 0)  el.textContent = `${m}m ${s}s`;
  else                         el.textContent = `${s}s`;
}

document.addEventListener('DOMContentLoaded', () => {
  if (typeof guardaHorario === 'function') guardaHorario();

  document.querySelectorAll('.match-countdown').forEach(el => {
    updateCountdown(el);
    setInterval(() => updateCountdown(el), 1000);
  });

  // Animar items del accordion al aparecer
  const items = document.querySelectorAll('.sh-accordion-item');
  items.forEach((item, i) => {
    item.style.opacity = '0';
    item.style.transform = 'translateY(12px)';
    item.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    setTimeout(() => {
      item.style.opacity = '1';
      item.style.transform = 'translateY(0)';
    }, i * 60);
  });
});
