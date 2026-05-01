<?php
/**
 * Admin - Usuarios & Donaciones Spicy
 * Permite registrar donaciones manuales y gestionar el acceso Spicy.
 */

// Migración automática: agregar columnas si no existen
try {
    $conn = getDBConnection();
    $conn->query("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS spicy_hasta DATETIME NULL");
    $conn->query("
        CREATE TABLE IF NOT EXISTS donaciones (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            user_id    INT NOT NULL,
            cafes      INT NOT NULL DEFAULT 1,
            meses      INT NOT NULL DEFAULT 1,
            notas      TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Throwable $e) { /* columnas ya existen */ }

try {
    $conn = getDBConnection();

    $usuarios = $conn->query("
        SELECT u.id, u.nombre, u.email, u.rol, u.activo, u.spicy_hasta,
               COUNT(d.id)              AS total_donaciones,
               COALESCE(SUM(d.cafes),0) AS total_cafes
        FROM usuarios u
        LEFT JOIN donaciones d ON d.user_id = u.id
        GROUP BY u.id
        ORDER BY FIELD(u.rol,'admin','spicy','usuario'), u.nombre ASC
    ")->fetch_all(MYSQLI_ASSOC);

    $historial = $conn->query("
        SELECT d.id, d.user_id, u.nombre AS usuario, d.cafes, d.meses, d.notas, d.created_at
        FROM donaciones d
        JOIN usuarios u ON u.id = d.user_id
        ORDER BY d.created_at DESC
        LIMIT 100
    ")->fetch_all(MYSQLI_ASSOC);

} catch (Throwable $e) {
    $usuarios  = [];
    $historial = [];
    echo '<div style="padding:1rem;background:#991b1b;color:#fff;border-radius:12px;margin-bottom:1rem;">Error: '
       . htmlspecialchars($e->getMessage()) . '</div>';
}

$now = time();
?>

<div class="admin-section-header">
  <div class="admin-section-title">Usuarios</div>
  <div style="font-size:.8rem; color:var(--text-muted);">
    <i class="fas fa-users me-1" style="color:var(--accent);"></i>
    <?= count($usuarios) ?> registrado<?= count($usuarios) !== 1 ? 's' : '' ?>
  </div>
</div>

<!-- Filtros -->
<div style="display:flex; gap:.75rem; margin-bottom:1.25rem; flex-wrap:wrap; align-items:center;">
  <div style="position:relative; flex:1; min-width:180px;">
    <i class="fas fa-search" style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.72rem;pointer-events:none;"></i>
    <input type="text" id="search-usuarios" class="admin-search" placeholder="Buscar usuario…" style="padding-left:1.9rem; width:100%;">
  </div>
  <select id="filter-rol" class="admin-search" style="min-width:130px; padding-left:.9rem;">
    <option value="">Todos los roles</option>
    <option value="admin">Admin</option>
    <option value="spicy">Spicy</option>
    <option value="usuario">Usuario</option>
  </select>
</div>

<!-- Tabla de usuarios -->
<div class="admin-table-wrapper" style="margin-bottom:2rem;">
  <div style="padding:.75rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-users" style="color:var(--accent);"></i>
    <span style="font-family:'Space Mono',monospace; font-size:.85rem; font-weight:700; color:var(--text-primary);">Lista de usuarios</span>
  </div>

  <?php if (empty($usuarios)): ?>
    <div class="admin-empty"><i class="fas fa-users"></i><p>No hay usuarios.</p></div>
  <?php else: ?>
  <table class="admin-table" id="tabla-usuarios">
    <thead>
      <tr>
        <th style="width:44px;">#</th>
        <th>Usuario</th>
        <th style="width:105px;">Rol</th>
        <th style="width:120px;">Spicy hasta</th>
        <th style="width:80px; text-align:center;">Cafés</th>
        <th style="width:60px; text-align:center;">Estado</th>
        <th style="width:130px; text-align:center;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($usuarios as $u):
        $esAdmin   = $u['rol'] === 'admin';
        $esSpicy   = $u['rol'] === 'spicy';
        $expirado  = $esSpicy && $u['spicy_hasta'] && strtotime($u['spicy_hasta']) < $now;
        $searchVal = strtolower($u['nombre'] . ' ' . $u['email'] . ' ' . $u['rol']);
        $spicyHastaJs = $u['spicy_hasta'] ? htmlspecialchars($u['spicy_hasta']) : '';
      ?>
      <tr data-search="<?= htmlspecialchars($searchVal) ?>" data-rol="<?= $u['rol'] ?>">

        <td style="color:var(--text-muted);font-size:.75rem;"><?= $u['id'] ?></td>

        <td>
          <div style="font-weight:600; color:var(--text-primary); line-height:1.3;">
            <?= htmlspecialchars($u['nombre']) ?>
          </div>
          <div style="font-size:.73rem; color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></div>
        </td>

        <td>
          <?php if ($esAdmin): ?>
            <span style="font-size:.7rem; background:rgba(239,68,68,.15); color:#ef4444; border:1px solid rgba(239,68,68,.3); padding:2px 9px; border-radius:100px; font-weight:700;">ADMIN</span>
          <?php elseif ($esSpicy && !$expirado): ?>
            <span style="font-size:.7rem; background:rgba(139,92,246,.15); color:#8b5cf6; border:1px solid rgba(139,92,246,.3); padding:2px 9px; border-radius:100px; font-weight:700;">☕ SPICY</span>
          <?php elseif ($expirado): ?>
            <span style="font-size:.7rem; background:rgba(251,191,36,.1); color:#fbbf24; border:1px solid rgba(251,191,36,.3); padding:2px 9px; border-radius:100px;">EXPIRADO</span>
          <?php else: ?>
            <span style="font-size:.7rem; background:var(--bg-secondary); color:var(--text-muted); border:1px solid var(--border); padding:2px 9px; border-radius:100px;">usuario</span>
          <?php endif; ?>
        </td>

        <td style="font-size:.77rem; color:var(--text-secondary); font-family:'Space Mono',monospace;">
          <?php if ($u['spicy_hasta']): ?>
            <span <?= $expirado ? 'style="color:#ef4444;"' : '' ?>>
              <?= date('d/m/Y', strtotime($u['spicy_hasta'])) ?>
            </span>
          <?php else: ?><span style="opacity:.3;">—</span><?php endif; ?>
        </td>

        <td style="text-align:center;">
          <?php if ($u['total_cafes'] > 0): ?>
            <span style="font-size:.82rem; font-weight:700; color:#fbbf24;">☕ <?= (int)$u['total_cafes'] ?></span>
          <?php else: ?><span style="opacity:.3; font-size:.8rem;">—</span><?php endif; ?>
        </td>

        <td style="text-align:center;">
          <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:<?= $u['activo'] ? '#22c55e' : '#ef4444' ?>;"></span>
        </td>

        <td style="text-align:center;">
          <?php if (!$esAdmin): ?>
          <button class="btn-admin-edit" style="width:auto; padding:4px 10px; font-size:.72rem; gap:4px;"
                  onclick='openDonarModal(<?= $u['id'] ?>, <?= json_encode($u['nombre']) ?>, <?= json_encode($spicyHastaJs) ?>)'
                  title="Registrar donación">
            <i class="fas fa-coffee"></i> Donar
          </button>
          <?php if ($esSpicy): ?>
          <button class="btn-admin-delete" style="width:auto; padding:4px 8px; font-size:.72rem; margin-top:3px;"
                  onclick="revocarSpicy(<?= $u['id'] ?>, <?= json_encode($u['nombre']) ?>)"
                  title="Revocar Spicy">
            <i class="fas fa-ban"></i>
          </button>
          <?php endif; ?>
          <?php endif; ?>
        </td>

      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- Historial de donaciones -->
<div class="admin-table-wrapper">
  <div style="padding:.75rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-history" style="color:#fbbf24;"></i>
    <span style="font-family:'Space Mono',monospace; font-size:.85rem; font-weight:700; color:var(--text-primary);">Historial de donaciones</span>
    <span style="margin-left:auto; font-size:.75rem; color:var(--text-muted);">Últimas 100</span>
  </div>

  <?php if (empty($historial)): ?>
    <div class="admin-empty"><i class="fas fa-coffee"></i><p>Aún no hay donaciones registradas.</p></div>
  <?php else: ?>
  <table class="admin-table">
    <thead>
      <tr>
        <th style="width:44px;">#</th>
        <th>Usuario</th>
        <th style="width:80px; text-align:center;">Cafés</th>
        <th style="width:75px; text-align:center;">Meses</th>
        <th>Notas</th>
        <th style="width:130px;">Fecha</th>
        <th style="width:50px;"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($historial as $d): ?>
      <tr>
        <td style="color:var(--text-muted);font-size:.75rem;"><?= $d['id'] ?></td>
        <td style="font-weight:600; color:var(--text-primary);"><?= htmlspecialchars($d['usuario']) ?></td>
        <td style="text-align:center; font-weight:700; color:#fbbf24;">☕ <?= (int)$d['cafes'] ?></td>
        <td style="text-align:center; font-size:.82rem; color:var(--text-secondary);">
          <?= (int)$d['meses'] ?> mes<?= $d['meses'] != 1 ? 'es' : '' ?>
        </td>
        <td style="font-size:.8rem; color:var(--text-muted);">
          <?php if ($d['notas']): ?>
            <span title="<?= htmlspecialchars($d['notas']) ?>"><?= htmlspecialchars(mb_strimwidth($d['notas'], 0, 70, '…')) ?></span>
          <?php else: ?><span style="opacity:.3;">—</span><?php endif; ?>
        </td>
        <td style="font-size:.75rem; color:var(--text-muted); white-space:nowrap; font-family:'Space Mono',monospace;">
          <?= date('d/m/Y H:i', strtotime($d['created_at'])) ?>
        </td>
        <td>
          <button class="btn-admin-delete" title="Eliminar"
                  onclick="eliminarDonacion(<?= $d['id'] ?>)">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- ── Modal: Registrar donación (Bootstrap) ──────────────── -->
<div class="modal fade admin-modal" id="modal-donar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">☕ Registrar donación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="padding:1.25rem;">
        <input type="hidden" id="donar-user-id">

        <div style="margin-bottom:1.1rem; padding:.75rem 1rem; background:var(--bg-secondary); border:1px solid var(--border); border-radius:10px;">
          <div style="font-size:.75rem; color:var(--text-muted); margin-bottom:.2rem;">Usuario</div>
          <div id="donar-user-nombre" style="font-weight:700; color:var(--text-primary);"></div>
          <div id="donar-spicy-info" style="font-size:.75rem; color:#8b5cf6; margin-top:.25rem;"></div>
        </div>

        <div class="mb-3">
          <label class="form-label">Número de cafés donados</label>
          <input type="number" id="donar-cafes" class="form-control" value="1" min="1" max="999" oninput="syncMeses()">
          <div class="form-text">1 café = 1 mes de acceso Spicy</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Meses a otorgar</label>
          <input type="number" id="donar-meses" class="form-control" value="1" min="1" max="999" oninput="updatePreview()">
          <div id="donar-expiry-preview" style="font-size:.75rem; color:#8b5cf6; margin-top:.3rem; min-height:1.1em;"></div>
        </div>

        <div class="mb-1">
          <label class="form-label">Notas (opcional)</label>
          <input type="text" id="donar-notas" class="form-control" placeholder="Ej: BMC 3 cafés 01/05/2026">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-confirmar" class="btn btn-sm"
                style="background:var(--accent); color:#fff; font-weight:600;"
                onclick="confirmarDonacion()">
          <i class="fas fa-coffee me-1"></i> Confirmar
        </button>
      </div>

    </div>
  </div>
</div>

<script>
var _modal, _spicyHasta = '';

document.addEventListener('DOMContentLoaded', function() {
  _modal = new bootstrap.Modal(document.getElementById('modal-donar'));
});

function openDonarModal(userId, nombre, spicyHasta) {
  _spicyHasta = spicyHasta || '';
  document.getElementById('donar-user-id').value    = userId;
  document.getElementById('donar-user-nombre').textContent = nombre;
  document.getElementById('donar-cafes').value      = 1;
  document.getElementById('donar-meses').value      = 1;
  document.getElementById('donar-notas').value      = '';

  var info = document.getElementById('donar-spicy-info');
  if (spicyHasta) {
    var d = new Date(spicyHasta.replace(' ', 'T'));
    info.textContent = 'Actualmente Spicy hasta ' + d.toLocaleDateString('es-HN');
  } else {
    info.textContent = '';
  }

  updatePreview();
  _modal.show();
}

function syncMeses() {
  var cafes = parseInt(document.getElementById('donar-cafes').value) || 1;
  document.getElementById('donar-meses').value = cafes;
  updatePreview();
}

function updatePreview() {
  var meses = parseInt(document.getElementById('donar-meses').value) || 1;
  var base;
  if (_spicyHasta) {
    var t = new Date(_spicyHasta.replace(' ', 'T'));
    base = t > new Date() ? t : new Date();
  } else {
    base = new Date();
  }
  base.setMonth(base.getMonth() + meses);
  document.getElementById('donar-expiry-preview').textContent =
    'Nuevo vencimiento: ' + base.toLocaleDateString('es-HN', {day:'2-digit', month:'2-digit', year:'numeric'});
}

async function confirmarDonacion() {
  var userId = parseInt(document.getElementById('donar-user-id').value);
  var cafes  = parseInt(document.getElementById('donar-cafes').value)  || 1;
  var meses  = parseInt(document.getElementById('donar-meses').value)  || 1;
  var notas  = document.getElementById('donar-notas').value.trim();

  var btn = document.getElementById('btn-confirmar');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando…';

  try {
    var res  = await fetch('api/usuarios.php', {
      method:  'POST',
      headers: {'Content-Type': 'application/json'},
      body:    JSON.stringify({action:'grant_spicy', user_id:userId, cafes:cafes, meses:meses, notas:notas})
    });
    var data = await res.json();
    if (data.success) {
      _modal.hide();
      location.reload();
    } else {
      showToast(data.message, false);
    }
  } catch(e) {
    showToast('Error de conexión', false);
  }
  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-coffee me-1"></i> Confirmar';
}

async function revocarSpicy(userId, nombre) {
  if (!confirm('¿Revocar el acceso Spicy de ' + nombre + '?')) return;
  var res  = await fetch('api/usuarios.php', {
    method:  'POST',
    headers: {'Content-Type': 'application/json'},
    body:    JSON.stringify({action:'revoke_spicy', user_id:userId})
  });
  var data = await res.json();
  if (data.success) location.reload();
  else showToast(data.message, false);
}

async function eliminarDonacion(id) {
  if (!confirm('¿Eliminar esta donación del historial?')) return;
  var res  = await fetch('api/usuarios.php', {
    method:  'POST',
    headers: {'Content-Type': 'application/json'},
    body:    JSON.stringify({action:'delete_donacion', id:id})
  });
  var data = await res.json();
  if (data.success) location.reload();
  else showToast(data.message, false);
}

// Filtros de tabla
document.getElementById('search-usuarios').addEventListener('input', filterTable);
document.getElementById('filter-rol').addEventListener('change', filterTable);

function filterTable() {
  var q   = document.getElementById('search-usuarios').value.toLowerCase();
  var rol = document.getElementById('filter-rol').value;
  document.querySelectorAll('#tabla-usuarios tbody tr[data-search]').forEach(function(row) {
    var ok = (!q || row.dataset.search.includes(q)) && (!rol || row.dataset.rol === rol);
    row.style.display = ok ? '' : 'none';
  });
}

function showToast(msg, ok) {
  var t = document.getElementById('admin-toast');
  if (!t) { alert(msg); return; }
  t.textContent = msg;
  t.className = 'show ' + (ok ? 'success' : 'error');
  setTimeout(function(){ t.className = ''; }, 3000);
}
</script>
