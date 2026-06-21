<?php
// ─────────────────────────────────────────────────────────────────────────────
//  Bitmovin Config Extractor — desofuscación completa en PHP
//  Detecta el patrón por ESTRUCTURA (no por nombre de variable)
// ─────────────────────────────────────────────────────────────────────────────

$result   = null;
$error    = null;
$htmlCode = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $htmlCode = $_POST['html_code'] ?? '';
    if (trim($htmlCode) === '') {
        $error = 'Pega el código HTML primero.';
    } else {
        try {
            $result = extractBitmovinConfig($htmlCode);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  kWc: algoritmo de shuffle de caracteres (semilla fija 4939235)
// ─────────────────────────────────────────────────────────────────────────────
function kWc(string $p): string
{
    $y = 4939235;
    $x = strlen($p);
    if ($x === 0) return '';
    $m = str_split($p);
    for ($v = 0; $v < $x; $v++) {
        $a    = $y * ($v + 489) + ($y % 32552);
        $u    = $y * ($v + 419) + ($y % 19680);
        $r    = $a % $x;
        $o    = $u % $x;
        $f    = $m[$r]; $m[$r] = $m[$o]; $m[$o] = $f;
        $y    = ($a + $u) % 7644514;
    }
    return implode('', $m);
}

// ─────────────────────────────────────────────────────────────────────────────
//  unescapeJsString: normaliza un string capturado del HTML a como JS lo leería
//  dentro de comillas simples: \' → '   \/ → /   \\ → \
// ─────────────────────────────────────────────────────────────────────────────
function unescapeJsString(string $s): string
{
    // Orden importa: procesar \\ primero para no doblar-procesar
    $s = str_replace('\\\\', '\x00BSLASH\x00', $s); // \\ → placeholder
    $s = str_replace("\\'", "'", $s);                    // \' → '
    $s = str_replace('\\/', '/', $s);                    // \/ → /
    $s = str_replace('\x00BSLASH\x00', '\\', $s);     // placeholder → \
    return $s;
}

// ─────────────────────────────────────────────────────────────────────────────
//  applyDecoderTokens: sustituye los tokens .a-.z del decoder intermedio
//  k = [65,85,89,79,82,76,71,94,86,60,66,75,90,81,70,87,88,74,72,80]
//  g = [10,96,92,39,32,42] + k  →  '.a'→chr(10), '.b'→chr(96), ... '.z'→chr(80)
//  Luego '.!' → '.'
// ─────────────────────────────────────────────────────────────────────────────
function applyDecoderTokens(string $s): string
{
    $k   = [65,85,89,79,82,76,71,94,86,60,66,75,90,81,70,87,88,74,72,80];
    $g   = array_merge([10,96,92,39,32,42], $k);
    $abc = 'abcdefghijklmnopqrstuvwxyz';
    for ($i = 0; $i < 26; $i++) {
        $s = str_replace('.' . $abc[$i], chr($g[$i]), $s);
    }
    return str_replace('.!', '.', $s);
}

// ─────────────────────────────────────────────────────────────────────────────
//  decodeD068Array: decodifica el array de valores del player
// ─────────────────────────────────────────────────────────────────────────────
function decodeD068Array(string $n, int $seed): array
{
    $u = strlen($n);
    if ($u === 0) return [];
    $k = str_split($n);
    $j = $seed;
    for ($f = 0; $f < $u; $f++) {
        $s    = $j * ($f + 473) + ($j % 36950);
        $d    = $j * ($f + 200) + ($j % 17669);
        $y    = $s % $u;
        $e    = $d % $u;
        $w    = $k[$y]; $k[$y] = $k[$e]; $k[$e] = $w;
        $j    = ($s + $d) % 1806386;
    }
    $sep = chr(127);
    $str = implode('', $k);
    $str = str_replace('%',  $sep, $str);
    $str = str_replace('#1', '%',  $str);
    $str = str_replace('#0', '#',  $str);
    return explode($sep, $str);
}

// ─────────────────────────────────────────────────────────────────────────────
//  ORQUESTADOR
// ─────────────────────────────────────────────────────────────────────────────
function extractBitmovinConfig(string $html): array
{
    // ── 1. Localizar el bloque <script> con la ofuscación ────────────────────
    // Buscamos el bloque que contiene la función shuffle interna (kWc/similar)
    // identificada por la constante 4939235 o la semilla característica
    // Fallback: buscar en todo el HTML

    // ── 2. Extraer los strings largos entre comillas simples ─────────────────
    // El ofuscador siempre tiene exactamente 2 strings largos (>200 chars) en
    // comillas simples dentro del IIFE principal:
    //   Posición 1: fDu  (el código del decoder intermedio)
    //   Posición 2: Rqs  (el payload cifrado con kWc)
    //
    // Regex: comilla simple, contenido que puede tener \' o cualquier char != ',
    // comilla simple de cierre
    $pattern = "/'((?:[^'\\\\]|\\\\.){'200,})'/";
    preg_match_all($pattern, $html, $matches);

    $longStrings = $matches[1] ?? [];

    if (count($longStrings) < 2) {
        // Intentar con threshold más bajo por si el HTML es comprimido
        $pattern2 = "/'((?:[^'\\\\]|\\\\.){50,})'/";
        preg_match_all($pattern2, $html, $matches2);
        $longStrings = $matches2[1] ?? [];

        if (count($longStrings) < 2) {
            throw new Exception(
                'No se encontraron los strings ofuscados en el HTML. ' .
                'Asegúrate de pegar el bloque &lt;script&gt; completo del player Bitmovin. ' .
                'Strings largos detectados: ' . count($longStrings)
            );
        }
    }

    // El 1er string largo es fDu (decoder), el 2do es Rqs (payload)
    $fDuRaw = $longStrings[0];
    $rqsRaw = $longStrings[1];

    // CRÍTICO: procesar escapes JS antes de kWc
    // PHP recibe el string con \\' y \\/ como 2 chars; JS los interpreta como 1 char
    // Sin este paso kWc opera sobre una cadena de longitud diferente → resultado incorrecto
    $fDuDecoded = kWc($fDuRaw);
    $rqsDecoded = kWc(str_replace("\\'", "'", $rqsRaw));

    // ── 3. Del código final, extraer cadena y semilla de la función anónima ───
    // Patrón: })("CADENA_BASE",NUMERO_SEMILLA)
    // o:      })( "CADENA_BASE" , NUMERO )
    $patArr = '/\}\s*\)\s*\(\s*"((?:[^"\\\\]|\\\\.)*)"\s*,\s*(\d+)\s*\)/s';
    if (!preg_match($patArr, $rqsDecoded, $mArr)) {
        // Intentar con comillas simples como fallback
        $patArr2 = "/\}\s*\)\s*\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*,\s*(\d+)\s*\)/s";
        if (!preg_match($patArr2, $rqsDecoded, $mArr)) {
            throw new Exception(
                'Se encontraron los strings ofuscados pero no se pudo extraer el array interno. ' .
                'El formato del payload puede ser diferente al esperado.'
            );
        }
    }

    // El arrayStr extraído del rqsDecoded aún tiene tokens del decoder (.q→B, .m→G, etc.)
    // Es necesario aplicar las mismas sustituciones que hace cPF en JS
    $arrayStr = applyDecoderTokens(str_replace('\/', '/', $mArr[1]));
    $seed     = (int) $mArr[2];

    // ── 4. Decodificar el array ───────────────────────────────────────────────
    $arr = decodeD068Array($arrayStr, $seed);

    if (empty(array_filter($arr))) {
        throw new Exception('El array decodificado está vacío. La semilla o el string pueden ser incorrectos.');
    }

    return buildConfig($arr);
}

function buildConfig(array $arr): array
{
    return [
        'stream_url'     => $arr[4]  ?? null,
        'drm_key_id'     => $arr[5]  ?? null,
        'drm_key'        => $arr[6]  ?? null,
        'license_key'    => $arr[20] ?? null,
        'allowed_domain' => $arr[1]  ?? null,
        'redirect_url'   => $arr[2]  ?? null,
        'player_div'     => $arr[18] ?? null,
        'width_height'   => $arr[21] ?? null,
        'canal_label'    => $arr[26] ?? null,
        'full_array'     => $arr,
    ];
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bitmovin Config Extractor</title>
<style>
  :root {
    --bg:      #0d1117;
    --surface: #161b22;
    --border:  #30363d;
    --accent:  #58a6ff;
    --green:   #3fb950;
    --danger:  #f85149;
    --text:    #e6edf3;
    --muted:   #8b949e;
    --radius:  8px;
    --mono:    'Fira Code','Cascadia Code','Consolas',monospace;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: var(--bg); color: var(--text);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 15px; line-height: 1.6; min-height: 100vh;
    display: flex; flex-direction: column; align-items: center;
    padding: 2rem 1rem 4rem;
  }
  header {
    width: 100%; max-width: 860px; margin-bottom: 1.75rem;
    display: flex; align-items: center; gap: 1rem;
  }
  .logo {
    width: 42px; height: 42px; background: var(--accent);
    border-radius: 10px; display: flex; align-items: center;
    justify-content: center; font-size: 1.4rem; flex-shrink: 0;
  }
  header h1 { font-size: 1.35rem; font-weight: 700; }
  header p  { color: var(--muted); font-size: .85rem; }
  .card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1.4rem;
    width: 100%; max-width: 860px; margin-bottom: 1.1rem;
  }
  label {
    display: block; font-size: .75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    color: var(--muted); margin-bottom: .5rem;
  }
  textarea {
    width: 100%; height: 210px; background: var(--bg);
    border: 1px solid var(--border); border-radius: var(--radius);
    color: var(--text); font-family: var(--mono); font-size: .78rem;
    padding: .7rem 1rem; resize: vertical; outline: none;
    transition: border-color .15s;
  }
  textarea:focus { border-color: var(--accent); }
  .actions { display: flex; gap: .6rem; margin-top: .9rem; }
  .btn {
    display: inline-flex; align-items: center; gap: .4rem;
    font-weight: 700; font-size: .88rem; padding: .55rem 1.3rem;
    border: none; border-radius: var(--radius); cursor: pointer; transition: opacity .15s;
  }
  .btn-primary { background: var(--accent); color: #0d1117; }
  .btn-primary:hover { opacity: .85; }
  .btn-ghost { background: transparent; border: 1px solid var(--border); color: var(--muted); }
  .btn-ghost:hover { border-color: var(--text); color: var(--text); }
  .alert {
    width: 100%; max-width: 860px; background: #2d1a1a;
    border: 1px solid var(--danger); color: var(--danger);
    border-radius: var(--radius); padding: .9rem 1.1rem;
    font-size: .875rem; margin-bottom: 1rem;
  }
  .section-title {
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .1em; color: var(--muted);
    margin: 1.4rem 0 .65rem; width: 100%; max-width: 860px;
  }
  .grid { display: grid; grid-template-columns: 1fr 1fr; gap: .9rem; width: 100%; max-width: 860px; }
  .grid .full { grid-column: 1 / -1; }
  @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
  .info-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1rem 1.1rem;
  }
  .ic-label {
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--muted); margin-bottom: .35rem;
  }
  .ic-row { display: flex; align-items: flex-start; gap: .5rem; }
  .ic-value { font-family: var(--mono); font-size: .78rem; color: var(--text); word-break: break-all; flex: 1; }
  .copy-btn {
    background: none; border: 1px solid var(--border); color: var(--muted);
    border-radius: 4px; padding: 2px 9px; font-size: .68rem;
    cursor: pointer; flex-shrink: 0; margin-top: 1px;
    transition: border-color .15s, color .15s;
  }
  .copy-btn:hover { border-color: var(--accent); color: var(--accent); }
  pre.json-block {
    font-family: var(--mono); font-size: .77rem; color: var(--text);
    overflow-x: auto; white-space: pre-wrap; word-break: break-all;
  }
  .raw-table { width: 100%; border-collapse: collapse; font-family: var(--mono); font-size: .73rem; }
  .raw-table th { text-align: left; color: var(--muted); font-weight: 600; padding: .3rem .5rem; border-bottom: 1px solid var(--border); }
  .raw-table td { padding: .27rem .5rem; border-bottom: 1px solid #1c2128; word-break: break-all; vertical-align: top; }
  .raw-table td:first-child { color: var(--muted); width: 2.5rem; text-align: right; padding-right: .9rem; }
  .raw-table tr:hover td { background: #1c2128; }
  details { width: 100%; max-width: 860px; }
  details summary { cursor: pointer; color: var(--muted); font-size: .8rem; padding: .5rem 0; user-select: none; }
  details summary:hover { color: var(--text); }
</style>
</head>
<body>

<header>
  <div class="logo">⚙</div>
  <div>
    <h1>Bitmovin Config Extractor</h1>
    <p>Pega el HTML del player ofuscado y extrae la configuración automáticamente.</p>
  </div>
</header>

<div class="card">
  <form method="POST">
    <label for="html_code">Código HTML del player</label>
    <textarea id="html_code" name="html_code"
      placeholder="Pega aquí el HTML completo de la página del player (con el bloque <script> de ofuscación)..."
      spellcheck="false"><?= h($htmlCode) ?></textarea>
    <div class="actions">
      <button type="submit" class="btn btn-primary">▶ Extraer configuración</button>
      <button type="button" class="btn btn-ghost"
        onclick="document.getElementById('html_code').value='';">Limpiar</button>
    </div>
  </form>
</div>

<?php if ($error): ?>
<div class="alert">⚠ <?= h($error) ?></div>
<?php endif; ?>

<?php if ($result): ?>

<div class="section-title">📡 Stream</div>
<div class="grid">
  <div class="info-card full">
    <div class="ic-label">URL del stream (MPEG-DASH)</div>
    <div class="ic-row">
      <code class="ic-value"><?= h($result['stream_url'] ?? '—') ?></code>
      <button class="copy-btn" onclick="cp(this,<?= json_encode($result['stream_url'] ?? '') ?>)">Copiar</button>
    </div>
  </div>
</div>

<div class="section-title">🔐 DRM — ClearKey</div>
<div class="grid">
  <div class="info-card">
    <div class="ic-label">Key ID</div>
    <div class="ic-row">
      <code class="ic-value"><?= h($result['drm_key_id'] ?? '—') ?></code>
      <button class="copy-btn" onclick="cp(this,<?= json_encode($result['drm_key_id'] ?? '') ?>)">Copiar</button>
    </div>
  </div>
  <div class="info-card">
    <div class="ic-label">Key</div>
    <div class="ic-row">
      <code class="ic-value"><?= h($result['drm_key'] ?? '—') ?></code>
      <button class="copy-btn" onclick="cp(this,<?= json_encode($result['drm_key'] ?? '') ?>)">Copiar</button>
    </div>
  </div>
</div>

<div class="section-title">🎛 Player</div>
<div class="grid">
  <div class="info-card">
    <div class="ic-label">License Key (Bitmovin)</div>
    <div class="ic-row">
      <code class="ic-value"><?= h($result['license_key'] ?? '—') ?></code>
      <button class="copy-btn" onclick="cp(this,<?= json_encode($result['license_key'] ?? '') ?>)">Copiar</button>
    </div>
  </div>
  <div class="info-card">
    <div class="ic-label">Canal / Label</div>
    <code class="ic-value"><?= h($result['canal_label'] ?? '—') ?></code>
  </div>
  <div class="info-card">
    <div class="ic-label">Div ID</div>
    <code class="ic-value"><?= h($result['player_div'] ?? '—') ?></code>
  </div>
  <div class="info-card">
    <div class="ic-label">Ancho / Alto</div>
    <code class="ic-value"><?= h($result['width_height'] ?? '—') ?></code>
  </div>
</div>

<div class="section-title">🛡 Anti-hotlink</div>
<div class="grid">
  <div class="info-card">
    <div class="ic-label">Dominio permitido</div>
    <code class="ic-value"><?= h($result['allowed_domain'] ?? '—') ?></code>
  </div>
  <div class="info-card">
    <div class="ic-label">Redirect si dominio inválido</div>
    <code class="ic-value"><?= h($result['redirect_url'] ?? '—') ?></code>
  </div>
</div>

<?php
$jsonConfig = json_encode([
    'source' => ['dash' => $result['stream_url']],
    'drm'    => ['clearkey' => [['keyId' => $result['drm_key_id'], 'key' => $result['drm_key']]]],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
<div class="section-title">🗂 JSON para player.load()</div>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;">
    <span style="color:var(--muted);font-size:.8rem;">Listo para usar en Bitmovin</span>
    <button class="copy-btn" style="padding:4px 12px;font-size:.73rem;"
      onclick="cp(this,<?= json_encode($jsonConfig) ?>)">Copiar JSON</button>
  </div>
  <pre class="json-block"><?= h($jsonConfig) ?></pre>
</div>

<details>
  <summary>Ver array completo _$_d068 (<?= count($result['full_array']) ?> elementos)</summary>
  <div class="card" style="margin-top:.5rem;">
    <table class="raw-table">
      <thead><tr><th>#</th><th>Valor</th></tr></thead>
      <tbody>
        <?php foreach ($result['full_array'] as $i => $v): ?>
        <tr><td><?= (int)$i ?></td><td><?= h((string)$v) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</details>

<?php endif; ?>

<script>
function cp(btn, text) {
  navigator.clipboard.writeText(text).then(() => {
    const orig = btn.textContent;
    btn.textContent = '✓ Copiado';
    btn.style.color = '#3fb950';
    btn.style.borderColor = '#3fb950';
    setTimeout(() => { btn.textContent = orig; btn.style.color=''; btn.style.borderColor=''; }, 1800);
  });
}
</script>
</body>
</html>