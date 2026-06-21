/**
 * StreamHub - Sistema de temas (dark/light)
 * Compartido en todas las páginas.
 */

/**
 * Convierte una ruta base de logo de liga (Sofascore o FotMob) a su versión dark o normal.
 * Ej: "assets/img/ligas/sf/384.png"  →  "assets/img/ligas/sf/dark/384.png"
 * Ej: "assets/img/ligas/fm/77.png"   →  "assets/img/ligas/fm/dark/77.png"
 * La regex inserta /dark/ antes del nombre de archivo; no se aplica si la ruta
 * ya contiene /dark/ (el patrón [^/]+ no cruza barras).
 */
function sfLogoSrc(baseSrc, theme) {
  if (!baseSrc) return baseSrc;
  if (theme !== 'dark') return baseSrc;
  if (!/\/ligas\/(sf|fm)\//.test(baseSrc)) return baseSrc;
  return baseSrc.replace(/\/(sf|fm)\/([^/]+\.png)$/, '/$1/dark/$2');
}

/** Actualiza el src de todos los logos sofascore al tema activo. */
function updateSfLogos(theme) {
  document.querySelectorAll('img[data-logo-base]').forEach(function (img) {
    img.src = sfLogoSrc(img.dataset.logoBase, theme);
  });
}

function initTheme() {
  const saved = localStorage.getItem('streamhub-theme') || 'dark';
  applyTheme(saved);
}

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('streamhub-theme', theme);
  const icon = document.getElementById('theme-icon');
  if (icon) icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
  updateSfLogos(theme);
}

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') || 'dark';
  applyTheme(current === 'dark' ? 'light' : 'dark');
}

// Aplicar inmediatamente al cargar para evitar flash
initTheme();

// Actualizar logos una vez el DOM está disponible (páginas PHP renderizadas en servidor)
document.addEventListener('DOMContentLoaded', function () {
  updateSfLogos(document.documentElement.getAttribute('data-theme') || 'dark');
});
