<?php
/**
 * StreamHub - Router principal
 * Todas las páginas pasan por aquí mediante ?p=
 */

$page = isset($_GET['p']) ? trim($_GET['p']) : 'home';

// Sanitizar: solo letras, números, guiones y guión bajo (ej. "eu_pendiente",
// "reset_password" — antes este regex no incluía "_" y los rompía a ambos,
// colapsando silenciosamente a home)
$page = preg_replace('/[^a-z0-9_\-]/', '', strtolower($page));

$allowed = ['home', 'tv', 'eventos', 'login', 'canal', 'liga', 'donaciones', 'eu_pendiente', 'mundial2026', 'reset_password'];

if (!in_array($page, $allowed)) {
    $page = 'home';
}

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/eu_check.php';
_autoLoginFromCookie(); // solo en el router público, nunca en APIs ni admin
checkEuAccess($page);   // bloqueo de acceso para usuarios europeos no aprobados

// ── Pre-render SEO ───────────────────────────────────────────────────────────
$_proto    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host     = $_SERVER['HTTP_HOST'] ?? 'teledeportes.online';
$SITE_URL  = $_proto . '://' . $_host;
$BASE_FULL = $SITE_URL . BASE_URL;

// Canonical según página y parámetros relevantes
$_qp = ['p' => $page];
if ($page === 'canal')   $_qp['id']   = (int)($_GET['id']   ?? 0);
if ($page === 'liga')  { $_qp['id']   = (int)($_GET['id']   ?? 0);
                         $_qp['type'] = preg_replace('/[^a-z]/', '', strtolower($_GET['type'] ?? 'soccer')); }
if ($page === 'eventos') $_qp['type'] = preg_replace('/[^a-z]/', '', strtolower($_GET['type'] ?? 'football'));
$seoCanonical = $SITE_URL . BASE_URL . '?' . http_build_query($_qp);

$seoTitle       = 'Tele Deportes - TV en Vivo & Deportes';
$seoDescription = 'Mira deportes en vivo y gratis. Fútbol, básquet, tenis, béisbol y más canales de TV sin registro.';
$seoKeywords    = 'deportes en vivo, fútbol en vivo, ver fútbol gratis, canales TV en vivo, streaming deportivo, Tele Deportes';
$seoRobots      = 'index, follow';
$seoOgType      = 'website';
$seoOgImage     = $BASE_FULL . 'assets/img/og-image.jpg';
$seoJsonLd      = null;

switch ($page) {
    case 'home':
        $seoTitle       = 'Tele Deportes - Ver TV y Deportes en Vivo Gratis';
        $seoDescription = 'Mira fútbol, básquet, tenis y más deportes en vivo y gratis. Canales de TV 24/7 sin registro ni pago.';
        $seoKeywords    = 'deportes en vivo, fútbol gratis, ver partido en vivo, canales TV en vivo, streaming deportivo, Tele Deportes';
        $seoJsonLd = json_encode([
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => 'Tele Deportes',
            'url'             => $BASE_FULL,
            'description'     => $seoDescription,
            'inLanguage'      => 'es',
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => $BASE_FULL . '?p=tv&q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        break;

    case 'tv':
        $seoTitle       = 'Canales de TV en Vivo Gratis - Tele Deportes';
        $seoDescription = 'Todos los canales de TV en vivo: ESPN, Fox Sports, Univision, TNT y más. Sin registro ni pago.';
        $seoKeywords    = 'canales TV en vivo, ESPN en vivo, Fox Sports gratis, Univision online, ver TV gratis';
        break;

    case 'eventos':
        $_evType = preg_replace('/[^a-z]/', '', strtolower($_GET['type'] ?? 'football'));
        $_evLabels = [
            'football'   => ['nombre' => 'Fútbol',   'desc' => 'partidos de fútbol en vivo'],
            'basketball' => ['nombre' => 'Básquet',  'desc' => 'partidos de básquet en vivo'],
            'tennis'     => ['nombre' => 'Tenis',    'desc' => 'torneos de tenis en vivo'],
            'baseball'   => ['nombre' => 'Béisbol',  'desc' => 'partidos de béisbol en vivo'],
        ];
        $_ev = $_evLabels[$_evType] ?? $_evLabels['football'];
        $seoTitle       = "{$_ev['nombre']} en Vivo Hoy - Tele Deportes";
        $seoDescription = "Mira los {$_ev['desc']} hoy. Transmisiones en directo, gratis y sin registro en Tele Deportes.";
        $seoKeywords    = "{$_ev['nombre']} en vivo, {$_ev['desc']}, streaming {$_ev['nombre']}, ver {$_ev['nombre']} gratis";
        break;

    case 'liga':
        $_ligaId  = (int)($_GET['id'] ?? 0);
        $_ligaNom = "Liga {$_ligaId}";
        if ($_ligaId > 0 && file_exists(__DIR__ . '/data/matches.json')) {
            foreach (json_decode(file_get_contents(__DIR__ . '/data/matches.json'), true) ?? [] as $_m) {
                if ((string)($_m['league'] ?? '') === (string)$_ligaId && !empty($_m['leagueName'])) {
                    $_ligaNom = $_m['leagueName'];
                    break;
                }
            }
        }
        $seoTitle       = "{$_ligaNom} en Vivo - Tele Deportes";
        $seoDescription = "Todos los partidos de {$_ligaNom} en vivo. Transmisiones en directo, gratis y sin registro.";
        $seoKeywords    = "{$_ligaNom} en vivo, partidos {$_ligaNom}, streaming {$_ligaNom}, ver {$_ligaNom} gratis";
        break;

    case 'canal':
        $_cId = (int)($_GET['id'] ?? 0);
        if ($_cId > 0) {
            try {
                $_cStmt = getDBConnection()->prepare("SELECT nombre FROM fuentes WHERE id = ? LIMIT 1");
                $_cStmt->bind_param('i', $_cId);
                $_cStmt->execute();
                $_cRow = $_cStmt->get_result()->fetch_assoc();
                $_cStmt->close();
                if (!empty($_cRow['nombre'])) {
                    $_cn = htmlspecialchars($_cRow['nombre'], ENT_QUOTES);
                    $seoTitle       = "{$_cn} en Vivo - Tele Deportes";
                    $seoDescription = "Mira {$_cn} en vivo y gratis en Tele Deportes. Transmisión en directo disponible 24/7 sin registro.";
                    $seoKeywords    = "{$_cn} en vivo, {$_cn} online, ver {$_cn} gratis, {$_cn} directo";
                    $seoOgType      = 'video.other';
                }
            } catch (Throwable $_e) {}
        }
        break;

    case 'donaciones':
        $seoTitle       = 'Apoya Tele Deportes - Donaciones';
        $seoDescription = 'Apoya Tele Deportes con una donación. Tu aporte nos ayuda a mantener el streaming deportivo gratuito para todos.';
        $seoKeywords    = 'apoyar Tele Deportes, donar, streaming deportivo gratis, Buy Me a Coffee';
        break;

    case 'mundial2026':
        $seoTitle       = 'FIFA World Cup 2026™ en Vivo - Tele Deportes';
        $seoDescription = 'Mira todos los partidos del Mundial FIFA 2026 en vivo y gratis. USA, Canadá y México como sedes. 48 selecciones, transmisiones en directo.';
        $seoKeywords    = 'mundial 2026, FIFA World Cup 2026, mundial en vivo, ver mundial gratis, copa del mundo 2026, partidos mundial';
        break;

    case 'login':
        $seoTitle  = 'Iniciar Sesión - Tele Deportes';
        $seoRobots = 'noindex, nofollow';
        break;

    case 'reset_password':
        $seoTitle  = 'Restablecer Contraseña - Tele Deportes';
        $seoRobots = 'noindex, nofollow';
        break;
}
// ────────────────────────────────────────────────────────────────────────────
$pageTitle = $seoTitle;
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <?php if ($page !== 'login') include __DIR__ . '/includes/ads.php'; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php if ($page === 'reset_password'): ?>
  <!-- El token de reseteo va en la URL: evitar que se filtre por el header
       Referer hacia recursos de terceros (CDNs) cargados en esta página. -->
  <meta name="referrer" content="same-origin">
  <?php endif; ?>
  <title><?= htmlspecialchars($seoTitle) ?></title>

  <!-- SEO básico -->
  <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta name="keywords"    content="<?= htmlspecialchars($seoKeywords) ?>">
  <meta name="robots"      content="<?= $seoRobots ?>">
  <meta name="author"      content="Tele Deportes">
  <link rel="canonical"    href="<?= htmlspecialchars($seoCanonical) ?>">

  <!-- Open Graph -->
  <meta property="og:type"        content="<?= $seoOgType ?>">
  <meta property="og:site_name"   content="Tele Deportes">
  <meta property="og:locale"      content="es_LA">
  <meta property="og:title"       content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta property="og:url"         content="<?= htmlspecialchars($seoCanonical) ?>">
  <meta property="og:image"       content="<?= htmlspecialchars($seoOgImage) ?>">
  <meta property="og:image:width"  content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt"   content="Tele Deportes - Streaming deportivo">

  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= htmlspecialchars($seoTitle) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta name="twitter:image"       content="<?= htmlspecialchars($seoOgImage) ?>">

  <?php if ($seoJsonLd): ?>
  <!-- JSON-LD -->
  <script type="application/ld+json"><?= $seoJsonLd ?></script>
  <?php endif; ?>

  <!-- ── Protección anti-DevTools: se carga primero ─────────────────────── -->
  <script>
  (function(){
    // Bloquear menú contextual
    document.addEventListener('contextmenu', function(e){ e.preventDefault(); }, true);

    // Bloquear atajos de teclado
    document.addEventListener('keydown', function(e){
      var k = e.key;
      var c = e.ctrlKey, s = e.shiftKey, m = e.metaKey, a = e.altKey;
      if (
        k === 'F12' ||
        (c && s && ['I','J','C','K'].indexOf(k) > -1) ||
        (c && ['u','U','s','S','p','P'].indexOf(k) > -1) ||
        (m && a && (k === 'I' || k === 'i')) ||
        (c && k === 'F5') || k === 'F5'
      ) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
      }
    }, true);

    // Silenciar consola
    var noop = function(){};
    ['log','warn','error','info','debug','table','trace','dir','dirxml',
     'group','groupCollapsed','groupEnd','clear','count','assert',
     'profile','profileEnd','time','timeLog','timeEnd'].forEach(function(m){
      try{ console[m] = noop; } catch(e){}
    });
  })();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/disable-devtool@latest/dist/disable-devtool.min.js"></script>
  <!-- ──────────────────────────────────────────────────────────────────── -->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<script>window.BASE_URL = '<?= BASE_URL ?>';</script>

<?php
// Login y eu_pendiente se muestran sin navbar/footer
$_noChrome = in_array($page, ['login', 'eu_pendiente', 'reset_password']);
if (!$_noChrome) {
    $maintenanceOn = isMaintenanceMode();
    ?>
    <div class="site-status-bar">
      <div class="container">
        <span class="status-pill <?= $maintenanceOn ? 'status-pill-maint' : 'status-pill-ok' ?>"
              title="<?= $maintenanceOn ? 'Sistema en mantenimiento' : 'Sistema operativo' ?>">
          <span class="status-dot"></span>
          <span class="status-pill-label"><?= $maintenanceOn ? 'Mantenimiento' : 'Operativo' ?></span>
        </span>
      </div>
    </div>
    <style>
    .site-status-bar {
      background: var(--bg-secondary);
      border-bottom: 1px solid var(--border);
      padding: 5px 0;
    }
    .site-status-bar .container {
      display: flex;
      justify-content: flex-end;
    }
    .status-pill {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 0.3rem 0.75rem;
      border-radius: 100px;
      font-size: 0.75rem;
      font-weight: 600;
      white-space: nowrap;
      line-height: 1;
    }
    .status-pill-ok {
      background: var(--accent-soft);
      border: 1px solid var(--border-accent);
      color: var(--text-secondary);
    }
    .status-pill-maint {
      background: #fef3c7;
      border: 1px solid #fde68a;
      color: #92400e;
    }
    .status-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      flex-shrink: 0;
      background: #22c55e;
      animation: pulse-badge 2s infinite;
    }
    .status-pill-maint .status-dot { background: #d97706; }
    </style>
    <?php
    require 'includes/navbar.php';
}

// Cargar la vista correspondiente
require "pages/{$page}.php";

if (!$_noChrome) {
    require 'includes/footer.php';
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme.js"></script>
<script src="assets/js/lazyload.js"></script>

<!-- ── Trampa debugger + configuración DisableDevtool ──────────────────── -->
<script>
(function(){
  // Trampa de debugger: congela DevTools cada 100 ms
  setInterval(function(){
    (function(){ return false; })['constructor']('debugger')();
  }, 100);

  // Configurar disable-devtool
  if (typeof DisableDevtool !== 'undefined') {
    DisableDevtool({
      interval: 200,
      disableMenu: true,
      ondevtoolopen: function(){
        document.documentElement.innerHTML =
          '<body style="margin:0;background:#000;display:flex;align-items:center;' +
          'justify-content:center;height:100vh;font-family:sans-serif;color:#fff;' +
          'flex-direction:column;gap:12px;">' +
          '<div style="font-size:3rem;">⛔</div>' +
          '<div style="font-size:1.2rem;font-weight:700;">Acceso restringido</div>' +
          '<div style="color:rgba(255,255,255,.6);font-size:.9rem;">Las herramientas de desarrollador están deshabilitadas.</div>' +
          '</body>';
      }
    });
  }
})();
</script>
<!-- ──────────────────────────────────────────────────────────────────── -->

<?php
// Scripts específicos por página
$scripts = [
    'home'        => 'assets/js/main.js',
    'tv'          => 'assets/js/channels.js',
    'canal'       => 'assets/js/channel.js',
    'eventos'     => 'assets/js/eventos.js',
    'liga'        => 'assets/js/liga.js',
    'mundial2026' => 'assets/js/liga.js',
    'login'       => 'assets/js/auth.js',
    'reset_password' => 'assets/js/auth.js',
];
if ($page === 'home') {
    echo '<script src="assets/js/huso.js"></script>';
}
if (isset($scripts[$page])) {
    echo '<script src="' . $scripts[$page] . '"></script>';
}
?>
<div<?= (!isLoggedIn() || userId() !== 2) ? ' style="display:none;"' : ' style="text-align:center;"' ?>>
<script id="_wauh8l">var _wau = _wau || []; _wau.push(["small", "j9isfwldlg", "h8l"]);</script><script async src="//waust.at/s.js"></script>
</div>
<?php if (!isSpicy() && !in_array($page, ['login', 'reset_password'], true)): ?>
<div class="kofi-float-wrap">
  <div class="kofi-float-popup" id="kofiPopup">
    ¿Disfrutas el contenido? ¡Invítanos un café!
    <button type="button" class="kofi-popup-close" id="kofiPopupClose" aria-label="Cerrar">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1 1L11 11M11 1L1 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
    </button>
  </div>
  <a href="https://ko-fi.com/N6R52203OC" target="_blank" rel="noopener noreferrer"
     class="kofi-float-btn" title="¿Disfrutas el contenido? ¡Invítanos un café!">
    <img src="https://cdn.buymeacoffee.com/widget/assets/coffee%20cup.svg" width="36" height="36" alt="" aria-hidden="true">
  </a>
</div>
<button type="button" class="back-to-top-btn" id="backToTopBtn" title="Volver arriba" aria-label="Volver arriba">
  <i class="fas fa-arrow-up"></i>
</button>
<style>
.kofi-float-wrap {
  position: fixed;
  right: 18px;
  bottom: 18px;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 14px;
  opacity: 1;
  transition: opacity .25s ease, visibility .25s ease;
}
/* Oculto mientras el usuario scrollea más allá del 30% de la página
   (ver script más abajo). Usa una clase en vez de inline style para que
   el toggle de canal.php (modo teatro con chat abierto) siga pudiendo
   forzar visibility vía style="" sin que ambos se peleen. */
.kofi-float-wrap.kofi-scroll-hidden {
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
}
/* Bubble del mensaje: mismas medidas, tipografía y botón de cerrar que usaba BMC */
.kofi-float-popup {
  position: relative;
  background: #fff;
  color: #111;
  max-width: 260px;
  padding: 16px 32px 16px 16px;
  border-radius: 4px;
  font-family: "Avenir Book", sans-serif;
  font-size: 18px;
  font-weight: 600;
  line-height: 1.4;
  box-shadow: 0px 2px 5px rgba(0,0,0,.05), 0px 8px 40px rgba(0,0,0,.04), 0px 0px 2px rgba(0,0,0,.15);
  transform-origin: bottom right;
  transform: scale(1);
  opacity: 1;
  transition: .25s ease all;
}
.kofi-float-popup.kofi-popup-hidden {
  opacity: 0;
  transform: scale(0.7);
  pointer-events: none;
}
.kofi-popup-close {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 16px;
  height: 16px;
  border: none;
  background: transparent;
  color: #111;
  opacity: .5;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.kofi-popup-close:hover { opacity: 1; }
/* Botón: mismo diámetro, radio, sombra y curva de animación que BMC (64px, hover/active a escala) */
.kofi-float-btn {
  width: 64px;
  height: 64px;
  border-radius: 32px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #8b5cf6;
  color: #fff;
  text-decoration: none;
  box-shadow: 0 4px 8px rgba(0,0,0,.15);
  transition: .25s ease all;
}
.kofi-float-btn:hover {
  transform: scale(1.1);
  color: #fff;
}
.kofi-float-btn:active {
  transform: scale(0.90);
}
.kofi-float-btn img {
  width: 36px;
  height: 36px;
}

/* Botón "volver arriba": mismo lugar/tamaño que el de Ko-fi, se muestran
   de forma excluyente según el % de scroll (ver script más abajo) */
.back-to-top-btn {
  position: fixed;
  right: 18px;
  bottom: 18px;
  z-index: 9999;
  width: 64px;
  height: 64px;
  border-radius: 32px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #8b5cf6;
  color: #fff;
  font-size: 1.3rem;
  cursor: pointer;
  box-shadow: 0 4px 8px rgba(0,0,0,.15);
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity .25s ease, visibility .25s ease, transform .25s ease;
}
.back-to-top-btn.show {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
}
.back-to-top-btn:hover { transform: scale(1.1); }
.back-to-top-btn:active { transform: scale(0.90); }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var popup = document.getElementById('kofiPopup');
  var closeBtn = document.getElementById('kofiPopupClose');

  var timer = setTimeout(function () {
    if (popup) popup.classList.add('kofi-popup-hidden');
  }, 3000);

  if (closeBtn) {
    closeBtn.addEventListener('click', function () {
      clearTimeout(timer);
      if (popup) popup.classList.add('kofi-popup-hidden');
    });
  }

  // Pasado el 30% de scroll de la página, sustituye el botón de Ko-fi por
  // uno de "volver arriba" en el mismo lugar. Por debajo del 30%, vuelve a
  // mostrarse el de Ko-fi.
  var kofiWrap   = document.querySelector('.kofi-float-wrap');
  var topBtn     = document.getElementById('backToTopBtn');
  if (kofiWrap || topBtn) {
    var ticking = false;
    function scrollPercent() {
      var doc = document.documentElement;
      var scrollable = doc.scrollHeight - doc.clientHeight;
      return scrollable > 0 ? (window.scrollY / scrollable) * 100 : 0;
    }
    function updateFloatButtons() {
      var pastThreshold = scrollPercent() >= 30;
      if (kofiWrap) kofiWrap.classList.toggle('kofi-scroll-hidden', pastThreshold);
      if (topBtn)   topBtn.classList.toggle('show', pastThreshold);
      ticking = false;
    }
    window.addEventListener('scroll', function () {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(updateFloatButtons);
      }
    }, { passive: true });
    updateFloatButtons(); // estado inicial, por si la página carga ya scrolleada
  }

  if (topBtn) {
    topBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
});
</script>
<?php endif; ?>
</body>
</html>
