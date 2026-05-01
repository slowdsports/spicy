<?php
/**
 * StreamHub - Router principal
 * Todas las páginas pasan por aquí mediante ?p=
 */

$page = isset($_GET['p']) ? trim($_GET['p']) : 'home';

// Sanitizar: solo letras, números y guiones
$page = preg_replace('/[^a-z0-9\-]/', '', strtolower($page));

$allowed = ['home', 'tv', 'eventos', 'login', 'canal', 'liga'];

if (!in_array($page, $allowed)) {
    $page = 'home';
}

require_once 'includes/config.php';
require_once 'includes/db.php';

// Construir título dinámico
$titles = [
    'home'    => 'Tele Deportes - TV en Vivo & Deportes',
    'tv'      => 'Canales - Tele Deportes',
    'eventos' => 'Eventos - Tele Deportes',
    'login'   => 'Iniciar Sesión - Tele Deportes',
    'canal'   => 'Canal - Tele Deportes',
    'liga'    => 'Liga - Tele Deportes',
];
$pageTitle = $titles[$page] ?? 'Tele Deportes';
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>

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

<?php
// En la página de login no mostramos navbar completo
if ($page !== 'login') {
    require 'includes/navbar.php';
}

// Cargar la vista correspondiente
require "pages/{$page}.php";

if ($page !== 'login') {
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
    'home'    => 'assets/js/main.js',
    'tv'      => 'assets/js/channels.js',
    'canal'   => 'assets/js/channel.js',
    'eventos' => 'assets/js/eventos.js',
    'liga'    => 'assets/js/liga.js',
    'login'   => 'assets/js/auth.js',
];
if (isset($scripts[$page])) {
    echo '<script src="' . $scripts[$page] . '"></script>';
}
?>
<script id="_wauxbn">var _wau = _wau || []; _wau.push(["small", "o4zjmmmefw", "xbn"]);</script><script async src="//waust.at/s.js"></script>
</body>
</html>
