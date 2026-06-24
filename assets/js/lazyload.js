/**
 * StreamHub - Lazy-load de imágenes con shimmer
 * Quita la clase "lazy-img" (y por lo tanto la animación de carga) en
 * cuanto cada <img class="lazy-img"> termina de cargar o falla.
 * Observa todo el documento, así que también cubre imágenes insertadas
 * después por fetch+innerHTML (sliders, grids de canales, etc.) sin que
 * cada script tenga que llamar nada manualmente.
 */
(function () {
  function reveal(img) {
    img.classList.add('lazy-img-loaded');
  }

  function watch(img) {
    if (img.classList.contains('lazy-img-loaded')) return;

    // Ya estaba en caché del navegador: nunca dispara "load"
    if (img.complete && img.naturalWidth > 0) {
      reveal(img);
      return;
    }

    img.addEventListener('load', function () { reveal(img); }, { once: true });
    img.addEventListener('error', function () { reveal(img); }, { once: true });
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
