/**
 * StreamHub - JS de la página de eventos (?p=eventos&type=X)
 * La página es server-rendered. Este script solo maneja animaciones.
 */
document.addEventListener('DOMContentLoaded', () => {
  // Animar las tarjetas de liga al entrar en viewport
  const cards = document.querySelectorAll('#leagues-grid .channel-card');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  cards.forEach(card => observer.observe(card));
});
