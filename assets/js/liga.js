/**
 * StreamHub - JS de la página de liga (?p=liga&id=X&type=Y)
 * Los countdowns se manejan inline en liga.php.
 * Este script maneja animaciones de entrada.
 */
document.addEventListener('DOMContentLoaded', () => {
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
