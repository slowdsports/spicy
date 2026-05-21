<?php
/**
 * Página de prueba - Control de acceso EU
 * Solo accesible para administradores.
 * URL: /spicy/test/eu_test.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/eu_check.php';

_autoLoginFromCookie();

// Guardia: solo admins
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><body style="font-family:sans-serif;background:#0a0a0c;color:#f0f0f5;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;">
        <div style="text-align:center;">
          <div style="font-size:2.5rem;margin-bottom:.75rem;">🔒</div>
          <div style="font-weight:700;margin-bottom:.4rem;">Acceso restringido</div>
          <div style="color:#9090a8;font-size:.85rem;">Esta página solo está disponible para administradores.</div>
          <a href="../?p=home" style="display:inline-block;margin-top:1rem;color:#8b5cf6;font-size:.85rem;">← Volver al inicio</a>
        </div></body></html>';
    exit();
}

// ── Acciones POST ─────────────────────────────────────────────────────────────
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'simular_pais') {
        $pais = trim($_POST['pais'] ?? '');
        if ($pais) {
            $_SESSION['_eu_country'] = $pais;
            $mensaje = "País simulado en sesión: <strong>{$pais}</strong>";
        }
    }

    if ($accion === 'limpiar_sesion') {
        unset($_SESSION['_eu_country'], $_SESSION['_eu_status']);
        $mensaje = 'Caché EU de sesión eliminada.';
    }

    if ($accion === 'insertar_registro') {
        $userId  = (int)($_POST['user_id']  ?? userId());
        $ip      = trim($_POST['ip']         ?? _euGetIp());
        $pais    = trim($_POST['pais_reg']   ?? 'Germany');
        $estado  = $_POST['estado']           ?? 'pendiente';
        if (!in_array($estado, ['pendiente', 'aprobado', 'denegado'])) $estado = 'pendiente';
        try {
            $conn = getDBConnection();
            _euEnsureTable();
            $s = $conn->prepare(
                "INSERT INTO eu_access (user_id, ip, pais, estado) VALUES (?,?,?,?)"
            );
            $s->bind_param('isss', $userId, $ip, $pais, $estado);
            $s->execute();
            $s->close();
            $mensaje = "Registro insertado en eu_access (user_id={$userId}, ip={$ip}, estado={$estado}).";
        } catch (Throwable $e) {
            $mensaje = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    }

    if ($accion === 'eliminar_registro') {
        $id = (int)($_POST['reg_id'] ?? 0);
        try {
            $conn = getDBConnection();
            $s = $conn->prepare("DELETE FROM eu_access WHERE id = ?");
            $s->bind_param('i', $id);
            $s->execute();
            $s->close();
            $mensaje = "Registro #{$id} eliminado.";
        } catch (Throwable $e) {
            $mensaje = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    }

    if ($accion === 'cambiar_estado') {
        $id     = (int)($_POST['reg_id_estado'] ?? 0);
        $estado = $_POST['nuevo_estado'] ?? '';
        if ($id && in_array($estado, ['pendiente', 'aprobado', 'denegado'])) {
            try {
                $conn = getDBConnection();
                $s = $conn->prepare("UPDATE eu_access SET estado = ?, updated_at = NOW() WHERE id = ?");
                $s->bind_param('si', $estado, $id);
                $s->execute();
                $s->close();
                $mensaje = "Registro #{$id} actualizado a '{$estado}'.";
            } catch (Throwable $e) {
                $mensaje = 'Error: ' . htmlspecialchars($e->getMessage());
            }
        }
    }

    // Redirigir para evitar re-POST al refrescar (URL explícita, sin PHP_SELF)
    header('Location: ' . BASE_URL . 'test/eu_test.php?msg=' . urlencode($mensaje));
    exit();
}

if (isset($_GET['msg'])) $mensaje = htmlspecialchars_decode($_GET['msg']);

// ── Datos para mostrar ────────────────────────────────────────────────────────
$ipActual       = _euGetIp();
$paisSession    = $_SESSION['_eu_country']  ?? null;
$statusSession  = $_SESSION['_eu_status']   ?? null;
$esEu           = $paisSession !== null ? _euIsEuCountry($paisSession) : null;

try {
    $conn = getDBConnection();
    _euEnsureTable();

    // Todos los registros (limitado a 100)
    $todosRegistros = $conn->query("
        SELECT ea.*, u.nombre, u.email
        FROM eu_access ea
        JOIN usuarios u ON u.id = ea.user_id
        ORDER BY ea.created_at DESC
        LIMIT 100
    ")->fetch_all(MYSQLI_ASSOC);

    // Lista de usuarios para el formulario de inserción
    $listaUsuarios = $conn->query(
        "SELECT id, nombre, email FROM usuarios ORDER BY nombre ASC"
    )->fetch_all(MYSQLI_ASSOC);

} catch (Throwable $e) {
    $todosRegistros = [];
    $listaUsuarios  = [];
}

$colores = [
    'pendiente' => ['bg' => 'rgba(251,191,36,.12)',  'color' => '#fbbf24', 'borde' => 'rgba(251,191,36,.3)'],
    'aprobado'  => ['bg' => 'rgba(34,197,94,.12)',   'color' => '#22c55e', 'borde' => 'rgba(34,197,94,.3)'],
    'denegado'  => ['bg' => 'rgba(239,68,68,.12)',   'color' => '#ef4444', 'borde' => 'rgba(239,68,68,.3)'],
];
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test EU Access · Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body { padding: 2rem 1rem; max-width: 1100px; margin: 0 auto; }
    .test-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 14px;
      margin-bottom: 1.5rem;
      overflow: hidden;
    }
    .test-card-header {
      padding: .75rem 1.25rem;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      gap: 8px;
      font-family: 'Space Mono', monospace;
      font-size: .85rem;
      font-weight: 700;
      color: var(--text-primary);
    }
    .test-card-body { padding: 1.25rem; }
    .info-row {
      display: flex;
      align-items: flex-start;
      gap: .75rem;
      padding: .5rem 0;
      border-bottom: 1px solid var(--border);
      font-size: .85rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label {
      width: 200px;
      flex-shrink: 0;
      color: var(--text-muted);
      font-family: 'Space Mono', monospace;
      font-size: .75rem;
    }
    .info-value { color: var(--text-primary); word-break: break-all; }
    .badge-estado {
      display: inline-block;
      font-size: .7rem;
      font-weight: 700;
      padding: 2px 9px;
      border-radius: 100px;
    }
    .btn-test {
      padding: .4rem .9rem;
      border-radius: 8px;
      font-size: .8rem;
      font-weight: 600;
      border: 1px solid var(--border-accent);
      background: var(--accent-soft);
      color: var(--accent);
      cursor: pointer;
      transition: var(--transition);
    }
    .btn-test:hover { background: var(--accent); color: #fff; }
    .btn-test-red {
      border-color: rgba(239,68,68,.4);
      background: rgba(239,68,68,.08);
      color: #ef4444;
    }
    .btn-test-red:hover { background: #ef4444; color: #fff; }
    .form-control-sm-dark {
      background: var(--bg-input);
      border: 1px solid var(--border);
      color: var(--text-primary);
      border-radius: 8px;
      padding: .3rem .65rem;
      font-size: .8rem;
    }
    .form-control-sm-dark:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px var(--accent-glow);
    }
    table.reg-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
    table.reg-table th {
      text-align: left;
      padding: .5rem .75rem;
      color: var(--text-muted);
      font-family: 'Space Mono', monospace;
      font-size: .7rem;
      font-weight: 700;
      border-bottom: 1px solid var(--border);
    }
    table.reg-table td {
      padding: .5rem .75rem;
      border-bottom: 1px solid var(--border);
      vertical-align: middle;
      color: var(--text-secondary);
    }
    table.reg-table tr:last-child td { border-bottom: none; }
    .alert-test {
      padding: .65rem 1rem;
      border-radius: 10px;
      margin-bottom: 1.25rem;
      font-size: .85rem;
      background: var(--accent-soft);
      border: 1px solid var(--border-accent);
      color: var(--text-primary);
    }
  </style>
</head>
<body>

<!-- Cabecera -->
<div style="display:flex; align-items:center; gap:12px; margin-bottom:1.75rem;">
  <div style="width:36px;height:36px;border-radius:10px;background:var(--accent-soft);border:1px solid var(--border-accent);
              display:flex;align-items:center;justify-content:center;">
    <i class="fas fa-flask" style="color:var(--accent);"></i>
  </div>
  <div>
    <div style="font-weight:700;font-size:1.05rem;color:var(--text-primary);">Test · Control de acceso EU</div>
    <div style="font-size:.75rem;color:var(--text-muted);">
      Solo visible para administradores &mdash;
      <a href="../admin/?p=accesos_eu" style="color:var(--accent);text-decoration:none;">
        <i class="fas fa-arrow-right me-1"></i>Ir al panel admin
      </a>
    </div>
  </div>
  <a href="../?p=home" style="margin-left:auto;font-size:.8rem;color:var(--text-muted);text-decoration:none;">
    <i class="fas fa-home me-1"></i> Inicio
  </a>
</div>

<?php if ($mensaje): ?>
<div class="alert-test"><i class="fas fa-circle-info me-2" style="color:var(--accent);"></i><?= $mensaje ?></div>
<?php endif; ?>

<!-- ── 1. ESTADO ACTUAL ─────────────────────────────────────────────────────── -->
<div class="test-card">
  <div class="test-card-header">
    <i class="fas fa-radar" style="color:var(--accent);"></i>
    Estado actual de detección
  </div>
  <div class="test-card-body">
    <div class="info-row">
      <span class="info-label">IP detectada</span>
      <span class="info-value">
        <code style="background:var(--bg-secondary);padding:1px 7px;border-radius:5px;font-size:.78rem;">
          <?= htmlspecialchars($ipActual ?: '(no detectada)') ?>
        </code>
      </span>
    </div>
    <div class="info-row">
      <span class="info-label">País en sesión</span>
      <span class="info-value">
        <?php if ($paisSession !== null): ?>
          <strong><?= htmlspecialchars($paisSession) ?></strong>
          <?php if ($esEu): ?>
            <span style="margin-left:6px;font-size:.7rem;background:rgba(239,68,68,.12);color:#ef4444;
                         border:1px solid rgba(239,68,68,.3);padding:1px 7px;border-radius:100px;">
              EUROPA
            </span>
          <?php else: ?>
            <span style="margin-left:6px;font-size:.7rem;background:rgba(34,197,94,.12);color:#22c55e;
                         border:1px solid rgba(34,197,94,.3);padding:1px 7px;border-radius:100px;">
              NO EU
            </span>
          <?php endif; ?>
        <?php else: ?>
          <span style="color:var(--text-muted);">
            No detectado aún
            <span style="font-size:.72rem;">(se detectará en la primera visita no exenta)</span>
          </span>
        <?php endif; ?>
      </span>
    </div>
    <div class="info-row">
      <span class="info-label">Estado EU en sesión</span>
      <span class="info-value">
        <?php if ($statusSession): ?>
          <?php $c = $colores[$statusSession] ?? $colores['pendiente']; ?>
          <span class="badge-estado"
                style="background:<?= $c['bg'] ?>;color:<?= $c['color'] ?>;border:1px solid <?= $c['borde'] ?>;">
            <?= strtoupper($statusSession) ?>
          </span>
        <?php else: ?>
          <span style="color:var(--text-muted);">—</span>
        <?php endif; ?>
      </span>
    </div>
    <div class="info-row">
      <span class="info-label">Usuario logueado</span>
      <span class="info-value">
        <?= htmlspecialchars(userName()) ?>
        <span style="font-size:.73rem;color:var(--text-muted);">(ID: <?= userId() ?>)</span>
      </span>
    </div>
  </div>
</div>

<!-- ── 2. CONTROLES DE SIMULACIÓN ───────────────────────────────────────────── -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

  <!-- Simular país -->
  <div class="test-card">
    <div class="test-card-header">
      <i class="fas fa-earth-europe" style="color:#fbbf24;"></i>
      Simular país en sesión
    </div>
    <div class="test-card-body">
      <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:1rem;">
        Sobreescribe <code style="font-size:.75rem;">$_SESSION['_eu_country']</code> para probar
        el flujo sin necesitar una IP europea real.
      </p>
      <form method="post" style="display:flex;flex-direction:column;gap:.75rem;">
        <input type="hidden" name="accion" value="simular_pais">
        <select name="pais" class="form-control-sm-dark">
          <optgroup label="— Países EU (bloqueados) —">
            <?php
            global $_EU_COUNTRIES;
            foreach ($_EU_COUNTRIES as $p):
            ?>
            <option value="<?= htmlspecialchars($p) ?>"
              <?= ($paisSession === $p) ? 'selected' : '' ?>>
              <?= htmlspecialchars($p) ?>
            </option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="— No EU (libre acceso) —">
            <option value="Mexico">Mexico</option>
            <option value="Argentina">Argentina</option>
            <option value="Colombia">Colombia</option>
            <option value="United States">United States</option>
          </optgroup>
        </select>
        <button type="submit" class="btn-test">
          <i class="fas fa-play me-1"></i> Aplicar simulación
        </button>
      </form>

      <hr style="border-color:var(--border);margin:1rem 0;">

      <form method="post">
        <input type="hidden" name="accion" value="limpiar_sesion">
        <button type="submit" class="btn-test btn-test-red" style="width:100%;">
          <i class="fas fa-trash-alt me-1"></i> Limpiar caché EU de sesión
        </button>
      </form>
    </div>
  </div>

  <!-- Insertar registro manual -->
  <div class="test-card">
    <div class="test-card-header">
      <i class="fas fa-database" style="color:#22c55e;"></i>
      Insertar registro en eu_access
    </div>
    <div class="test-card-body">
      <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:1rem;">
        Crea un registro directo en la tabla para simular estados sin pasar por el flujo normal.
      </p>
      <form method="post" style="display:flex;flex-direction:column;gap:.6rem;">
        <input type="hidden" name="accion" value="insertar_registro">

        <label style="font-size:.75rem;color:var(--text-muted);">Usuario</label>
        <select name="user_id" class="form-control-sm-dark">
          <?php foreach ($listaUsuarios as $u): ?>
          <option value="<?= $u['id'] ?>" <?= $u['id'] == userId() ? 'selected' : '' ?>>
            #<?= $u['id'] ?> — <?= htmlspecialchars($u['nombre']) ?>
          </option>
          <?php endforeach; ?>
        </select>

        <label style="font-size:.75rem;color:var(--text-muted);">IP</label>
        <input type="text" name="ip" class="form-control-sm-dark"
               value="<?= htmlspecialchars($ipActual) ?>" placeholder="IPv4 o IPv6">

        <label style="font-size:.75rem;color:var(--text-muted);">País</label>
        <input type="text" name="pais_reg" class="form-control-sm-dark"
               value="Germany" placeholder="Ej: Germany">

        <label style="font-size:.75rem;color:var(--text-muted);">Estado inicial</label>
        <select name="estado" class="form-control-sm-dark">
          <option value="pendiente">pendiente</option>
          <option value="aprobado">aprobado</option>
          <option value="denegado">denegado</option>
        </select>

        <button type="submit" class="btn-test" style="margin-top:.25rem;">
          <i class="fas fa-plus me-1"></i> Insertar registro
        </button>
      </form>
    </div>
  </div>
</div>

<!-- ── 3. FLUJOS DE PRUEBA RÁPIDA ───────────────────────────────────────────── -->
<div class="test-card" style="margin-bottom:1.5rem;">
  <div class="test-card-header">
    <i class="fas fa-bolt" style="color:#fbbf24;"></i>
    Flujos de prueba rápida
  </div>
  <div class="test-card-body">
    <div style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;">

      <!-- Simular visita EU y probar middleware -->
      <form method="post" action="">
        <input type="hidden" name="accion" value="simular_pais">
        <input type="hidden" name="pais" value="Germany">
        <button type="submit" class="btn-test">
          <i class="fas fa-flag me-1"></i> Simular IP alemana
        </button>
      </form>

      <!-- Simular país no EU -->
      <form method="post" action="">
        <input type="hidden" name="accion" value="simular_pais">
        <input type="hidden" name="pais" value="Mexico">
        <button type="submit" class="btn-test" style="border-color:rgba(34,197,94,.4);background:rgba(34,197,94,.08);color:#22c55e;">
          <i class="fas fa-unlock me-1"></i> Simular IP México (libre)
        </button>
      </form>

      <!-- Visitar home con la simulación activa -->
      <a href="../?p=home" class="btn-test" style="text-decoration:none;display:inline-block;">
        <i class="fas fa-house me-1"></i> Visitar inicio (activa el middleware)
      </a>

      <!-- Ir directo a eu_pendiente -->
      <a href="../?p=eu_pendiente" class="btn-test" style="text-decoration:none;display:inline-block;">
        <i class="fas fa-hourglass me-1"></i> Ver pantalla eu_pendiente
      </a>

      <!-- Panel admin EU -->
      <a href="../admin/?p=accesos_eu" class="btn-test" style="text-decoration:none;display:inline-block;">
        <i class="fas fa-sliders me-1"></i> Panel Accesos EU
      </a>

    </div>
    <div style="margin-top:1rem;padding:.65rem .9rem;background:var(--bg-secondary);border:1px solid var(--border);
                border-radius:8px;font-size:.75rem;color:var(--text-muted);line-height:1.7;">
      <strong style="color:var(--text-secondary);">Flujo de prueba sugerido:</strong><br>
      1. <em>Simular IP alemana</em> → 2. <em>Visitar inicio</em> (redirigirá al login)
      → 3. Iniciar sesión → 4. Se creará registro pendiente y se verá la pantalla de espera
      → 5. Aprobar en el <em>Panel Accesos EU</em> → 6. Refrescar pantalla de espera → debería entrar.
    </div>
  </div>
</div>

<!-- ── 4. TABLA eu_access ───────────────────────────────────────────────────── -->
<div class="test-card">
  <div class="test-card-header">
    <i class="fas fa-table" style="color:var(--accent);"></i>
    Registros en eu_access
    <span style="margin-left:auto;font-size:.75rem;color:var(--text-muted);font-weight:400;">
      <?= count($todosRegistros) ?> registro<?= count($todosRegistros) !== 1 ? 's' : '' ?>
      (máx. 100)
    </span>
  </div>
  <div class="test-card-body" style="padding:0;">
    <?php if (empty($todosRegistros)): ?>
      <div style="padding:2rem;text-align:center;color:var(--text-muted);">
        <i class="fas fa-inbox" style="font-size:1.5rem;margin-bottom:.5rem;display:block;"></i>
        La tabla está vacía.
      </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="reg-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Usuario</th>
          <th>IP</th>
          <th>País</th>
          <th>Estado</th>
          <th>Creado</th>
          <th>Actualizado</th>
          <th style="text-align:center;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($todosRegistros as $r):
          $c = $colores[$r['estado']] ?? $colores['pendiente'];
        ?>
        <tr>
          <td style="color:var(--text-muted);font-size:.72rem;"><?= $r['id'] ?></td>
          <td>
            <div style="color:var(--text-primary);font-weight:600;line-height:1.3;">
              <?= htmlspecialchars($r['nombre']) ?>
            </div>
            <div style="font-size:.7rem;color:var(--text-muted);"><?= htmlspecialchars($r['email']) ?></div>
          </td>
          <td style="font-family:'Space Mono',monospace;font-size:.75rem;"><?= htmlspecialchars($r['ip']) ?></td>
          <td><?= htmlspecialchars($r['pais'] ?: '—') ?></td>
          <td>
            <span class="badge-estado"
                  style="background:<?= $c['bg'] ?>;color:<?= $c['color'] ?>;border:1px solid <?= $c['borde'] ?>;">
              <?= strtoupper($r['estado']) ?>
            </span>
          </td>
          <td style="font-size:.72rem;font-family:'Space Mono',monospace;white-space:nowrap;">
            <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
          </td>
          <td style="font-size:.72rem;font-family:'Space Mono',monospace;white-space:nowrap;">
            <?= date('d/m/Y H:i', strtotime($r['updated_at'])) ?>
          </td>
          <td style="text-align:center;white-space:nowrap;">
            <!-- Cambiar estado -->
            <form method="post" style="display:inline-flex;gap:4px;align-items:center;">
              <input type="hidden" name="accion" value="cambiar_estado">
              <input type="hidden" name="reg_id_estado" value="<?= $r['id'] ?>">
              <select name="nuevo_estado" class="form-control-sm-dark" style="font-size:.7rem;padding:2px 5px;">
                <option value="pendiente" <?= $r['estado']==='pendiente'?'selected':'' ?>>pendiente</option>
                <option value="aprobado"  <?= $r['estado']==='aprobado' ?'selected':'' ?>>aprobado</option>
                <option value="denegado"  <?= $r['estado']==='denegado' ?'selected':'' ?>>denegado</option>
              </select>
              <button type="submit" class="btn-test" style="padding:2px 8px;font-size:.7rem;">
                <i class="fas fa-check"></i>
              </button>
            </form>
            <!-- Eliminar -->
            <form method="post" style="display:inline;">
              <input type="hidden" name="accion" value="eliminar_registro">
              <input type="hidden" name="reg_id" value="<?= $r['id'] ?>">
              <button type="submit" class="btn-test btn-test-red"
                      style="padding:2px 8px;font-size:.7rem;margin-left:2px;"
                      onclick="return confirm('¿Eliminar registro #<?= $r['id'] ?>?')">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/theme.js"></script>
</body>
</html>
