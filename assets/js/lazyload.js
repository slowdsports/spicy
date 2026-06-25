/**
 * StreamHub - Lazy-load de imágenes con shimmer + fallback genérico
 * Quita la clase "lazy-img" (y por lo tanto la animación de carga) en
 * cuanto cada <img class="lazy-img"> termina de cargar o falla.
 * Observa todo el documento, así que también cubre imágenes insertadas
 * después por fetch+innerHTML (sliders, grids de canales, etc.) sin que
 * cada script tenga que llamar nada manualmente.
 *
 * Si una imagen falla (logo roto, poster caído, etc.) se reemplaza por un
 * ícono genérico del mismo tamaño, en vez de dejarla en blanco/rota.
 * Esto reemplaza los onerror="opacity=0/display=none" que había sueltos
 * por el sitio — ahora el fallback es uno solo y consistente.
 *
 * El ícono depende del contenido de la imagen, vía data-fallback-icon:
 *   "team"   → escudo de equipo   (data-fallback-icon="team")
 *   "league" → trofeo de liga     (data-fallback-icon="league")
 *   (nada)   → TV (canal/programa) — comportamiento por defecto
 */
(function () {
  var FALLBACK_ICONS = {
    team:   'fa-shield-alt',
    league: 'fa-trophy',
    tv:     'fa-tv'
  };

  function reveal(img) {
    img.classList.add('lazy-img-loaded');
  }

  function applyFallback(img) {
    if (img.dataset.lazyFallback) return;
    img.dataset.lazyFallback = '1';

    var w = img.offsetWidth  || parseInt(img.getAttribute('width'),  10) || 32;
    var h = img.offsetHeight || parseInt(img.getAttribute('height'), 10) || 32;
    var iconClass = FALLBACK_ICONS[img.dataset.fallbackIcon] || FALLBACK_ICONS.tv;

    var icon = document.createElement('i');
    icon.className = 'fas ' + iconClass + ' lazy-img-fallback';
    icon.setAttribute('aria-hidden', 'true');
    icon.style.width    = w + 'px';
    icon.style.height   = h + 'px';
    icon.style.fontSize = Math.max(10, Math.round(Math.min(w, h) * 0.42)) + 'px';

    img.replaceWith(icon);
  }

  function watch(img) {
    if (img.classList.contains('lazy-img-loaded')) return;

    // Ya estaba en caché del navegador: nunca dispara "load"/"error"
    if (img.complete) {
      reveal(img);
      if (img.naturalWidth === 0) applyFallback(img);
      return;
    }

    img.addEventListener('load', function () { reveal(img); }, { once: true });
    img.addEventListener('error', function () { reveal(img); applyFallback(img); }, { once: true });
  }

  function scan(root) {
    if (root.matches && root.matches('img.lazy-img')) watch(root);
    if (root.querySelectorAll) {
      root.querySelectorAll('img.lazy-img').forEach(watch);
    }
  }

  scan(document.body || document.documentElement);

  var observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      m.addedNodes.forEach(function (node) {
        if (node.nodeType === 1) scan(node);
      });
    });
  });

  observer.observe(document.documentElement, { childList: true, subtree: true });
})();
