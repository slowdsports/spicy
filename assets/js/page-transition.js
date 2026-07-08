/**
 * StreamHub - Transición de fade entre páginas
 *
 * No es un router SPA (no usa pushState ni reemplaza contenido por fetch):
 * cada click todavía dispara una navegación real, así que la URL cambia
 * sola y no hay riesgo de que los scripts de cada página (main.js, liga.js,
 * live-scores.js...) se re-ejecuten sobre el mismo scope global y choquen
 * con sus propios "const"/"function" ya declarados. Lo único que se agrega
 * es un fundido de salida antes de navegar y uno de entrada al cargar —
 * evita el "flash" de página en blanco entre una y otra.
 */
(function () {
  // ── Barra de progreso arriba de la página (estilo YouTube/GitHub) ──────
  // Como cada click dispara una navegación real (no pushState), no hay forma
  // de saber cuánto falta para que la página nueva termine de cargar — la
  // barra solo avanza hasta ~90% mientras esperamos, y desaparece sola junto
  // con el documento viejo cuando el navegador reemplaza la página.
  let bar = null;
  function showProgressBar() {
    bar = document.createElement('div');
    bar.className = 'page-progress-bar';
    document.body.appendChild(bar);
    requestAnimationFrame(function () { bar.style.width = '30%'; });
    setTimeout(function () { if (bar) bar.style.width = '60%'; }, 120);
    setTimeout(function () { if (bar) bar.style.width = '85%'; }, 400);
  }

  function isEligibleLink(a) {
    if (!a || !a.href) return false;
    if (a.target && a.target !== '' && a.target !== '_self') return false;
    if (a.hasAttribute('download')) return false;
    if (a.dataset.noTransition !== undefined) return false;

    let url;
    try { url = new URL(a.href, location.href); } catch (e) { return false; }

    if (url.origin !== location.origin) return false;
    if (url.href.split('#')[0] === location.href.split('#')[0]) return false; // mismo documento (ancla)
    // No interceptar admin/api/reproductor (iframes, descargas de token, etc.)
    if (/\/(admin|api)(\/|$)/.test(url.pathname)) return false;
    if (/\/pages\/reproductor/.test(url.pathname)) return false;

    return true;
  }

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return; // abrir en pestaña nueva, etc.

    const a = e.target.closest('a');
    if (!isEligibleLink(a)) return;

    e.preventDefault();
    document.body.classList.add('page-leaving');
    showProgressBar();
    setTimeout(function () {
      window.location.href = a.href;
    }, 160);
  });

  // Si el usuario vuelve con el botón "atrás" desde el caché de vuelta-atrás
  // del navegador (bfcache), la página puede reaparecer todavía con la clase
  // "page-leaving" del fundido anterior — quitarla para que no quede opaca.
  window.addEventListener('pageshow', function () {
    document.body.classList.remove('page-leaving');
  });
})();
