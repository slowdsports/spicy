<?php
/**
 * StreamHub - Router principal
 * Todas las páginas pasan por aquí mediante ?p=
 */

$page = isset($_GET['p']) ? trim($_GET['p']) : 'home';

// Sanitizar: solo letras, números y guiones
$page = preg_replace('/[^a-z0-9\-]/', '', strtolower($page));

$allowed = ['home', 'tv', 'eventos', 'login', 'canal', 'liga', 'donaciones', 'eu_pendiente', 'mundial2026'];

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
$_noChrome = in_array($page, ['login', 'eu_pendiente']);
if (!$_noChrome) {
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
<?php if (!isSpicy() && $page !== 'login'): ?>
<script data-name="BMC-Widget"
        data-cfasync="false"
        src="https://cdnjs.buymeacoffee.com/1.0.0/widget.prod.min.js"
        data-id="slowdsports"
        data-description="Apoya Tele Deportes"
        data-message="¿Disfrutas el contenido? ¡Invítanos un café!"
        data-color="#8b5cf6"
        data-position="Right"
        data-x_margin="18"
        data-y_margin="18">
</script>
<?php endif; ?>
</body>
</html>
