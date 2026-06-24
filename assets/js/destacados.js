/**
 * StreamHub — Banner de Partidos Destacados
 * Carga los partidos destacados del día y renderiza el slider en la home.
 */
(function () {
  'use strict';

  var BASE = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/';

  function init() {
    var section = document.getElementById('featured-section');
    var slider  = document.getElementById('featured-slider');
    if (!section || !slider) return;

    fetch(BASE + 'api/destacados.php')
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.ok || !data.matches || data.matches.length === 0) return;
        renderBanner(section, slider, data.matches);
      })
      .catch(function () { /* error de red — no mostrar sección */ });
  }

  function renderBanner(section, slider, matches) {
    slider.innerHTML = '';

    matches.forEach(function (m) {
      var card = buildCard(m);
      slider.appendChild(card);
    });

    section.style.display = '';
    initDrag(slider);
  }

  function buildCard(m) {
    var ligaId   = m.league  || m.liga || '';
    var tipo     = m.tipo    || 'football';
    var pid      = m.id      || '';
    var ctaUrl   = BASE + '?p=liga&id=' + ligaId + '&type=' + tipo + '&partido=' + pid;

    var homeName  = (m.homeTeam && m.homeTeam.name) ? m.homeTeam.name : '—';
    var awayName  = (m.awayTeam && m.awayTeam.name) ? m.awayTeam.name : '—';
    var homeLogo  = logoSrc(m.homeTeam  && m.homeTeam.logo);
    var awayLogo  = logoSrc(m.awayTeam  && m.awayTeam.logo);
    var leagueLogo = m.leagueLogo || '';
    var leagueName = m.leagueName || '';
    var timeLabel  = buildTimeLabel(m);

    var theme = document.documentElement.getAttribute('data-theme') || 'dark';
    if (typeof sfLogoSrc === 'function') {
      leagueLogo = sfLogoSrc(leagueLogo, theme);
    }

    var card = document.createElement('a');
    card.href = ctaUrl;
    card.className = 'featured-card fade-in';
    card.style.textDecoration = 'none';

    card.innerHTML =
      '<div class="fc-header">' +
        (leagueLogo
          ? '<img src="' + esc(leagueLogo) + '" alt="' + esc(leagueName) + '" class="fc-league-logo lazy-img" loading="lazy" onerror="this.style.display=\'none\'">'
          : '') +
        '<span class="fc-league-name">' + esc(leagueName) + '</span>' +
        '<span class="fc-time">' + esc(timeLabel) + '</span>' +
      '</div>' +
      '<div class="fc-teams">' +
        '<div class="fc-team">' +
          (homeLogo ? '<img src="' + esc(homeLogo) + '" alt="' + esc(homeName) + '" class="fc-team-logo lazy-img" loading="lazy" onerror="this.style.opacity=\'0\'">' : '') +
          '<span class="fc-team-name">' + esc(homeName) + '</span>' +
        '</div>' +
        '<div class="fc-vs">VS</div>' +
        '<div class="fc-team">' +
          (awayLogo ? '<img src="' + esc(awayLogo) + '" alt="' + esc(awayName) + '" class="fc-team-logo lazy-img" loading="lazy" onerror="this.style.opacity=\'0\'">' : '') +
          '<span class="fc-team-name">' + esc(awayName) + '</span>' +
        '</div>' +
      '</div>' +
      '<div class="fc-cta">' +
        '<span class="fc-cta-btn">' +
          '<i class="fas fa-play" style="font-size:0.7rem;"></i> Ver partido' +
        '</span>' +
      '</div>';

    return card;
  }

  function logoSrc(logo) {
    if (!logo) return '';
    var v = String(logo).trim();
    if (!v) return '';
    if (v.startsWith('http://') || v.startsWith('https://') || v.startsWith('/')) return v;
    var folder = Number(v) >= 900000000 ? 'fm' : 'sf';
    return BASE + 'assets/img/equipos/' + folder + '/' + encodeURIComponent(v) + '.png';
  }

  function buildTimeLabel(m) {
    if (m.status === 'live') return '● EN VIVO';
    if (!m.fecha_hora && !m.time) return '';
    if (m.fecha_hora) {
      try {
        var d = new Date(m.fecha_hora.replace(' ', 'T') + '-06:00');
        if (!isNaN(d)) {
          return d.toLocaleDateString('es', { weekday: 'short', month: 'short', day: 'numeric' }) +
                 ' · ' + String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
        }
      } catch (e) {}
    }
    return m.time || '';
  }

  function esc(str) {
    return String(str || '')
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  /* Drag-to-scroll para el slider */
  function initDrag(el) {
    var isDown = false, startX, scrollLeft;
    el.addEventListener('mousedown', function (e) {
      isDown = true; el.classList.add('grabbing');
      startX = e.pageX - el.offsetLeft; scrollLeft = el.scrollLeft;
    });
    el.addEventListener('mouseleave', function () { isDown = false; el.classList.remove('grabbing'); });
    el.addEventListener('mouseup',    function () { isDown = false; el.classList.remove('grabbing'); });
    el.addEventListener('mousemove',  function (e) {
      if (!isDown) return;
      e.preventDefault();
      el.scrollLeft = scrollLeft - (e.pageX - el.offsetLeft - startX) * 1.5;
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
