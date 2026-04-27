/**
 * StreamHub - Sistema de temas (dark/light)
 * Compartido en todas las páginas.
 */
function initTheme() {
  const saved = localStorage.getItem('streamhub-theme') || 'dark';
  applyTheme(saved);
}

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('streamhub-theme', theme);
  const icon = document.getElementById('theme-icon');
  if (icon) icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') || 'dark';
  applyTheme(current === 'dark' ? 'light' : 'dark');
}

// Aplicar inmediatamente al cargar para evitar flash
initTheme();
