<?php
/**
 * Admin - Gestión de accesos europeos
 * Permite aprobar o denegar solicitudes de acceso desde países de Europa.
 */

require_once __DIR__ . '/../../includes/eu_check.php';

try {
    $conn = getDBConnection();
    _euEnsureTable();

    $pendientes = $conn->query("
        SELECT ea.id, ea.user_id, ea.ip, ea.pais, ea.estado, ea.created_at, ea.updated_at,
               u.nombre, u.email
        FROM eu_access ea
        JOIN usuarios u ON u.id = ea.user_id
        WHERE ea.estado = 'pendiente'
        ORDER BY ea.created_at ASC
    ")->fetch_all(MYSQLI_ASSOC);

    $historial = $conn->query("
        SELECT ea.id, ea.user_id, ea.ip, ea.pais, ea.estado, ea.updated_at,
               u.nombre, u.email
        FROM eu_access ea
        JOIN usuarios u ON u.id = ea.user_id
        WHERE ea.estado != 'pendiente'
        ORDER BY ea.updated_at DESC
        LIMIT 200
    ")->fetch_all(MYSQLI_ASSOC);

} catch (Throwable $e) {
    $pendientes = [];
    $historial  = [];
    echo '<div style="padding:1rem;background:#991b1b;color:#fff;border-radius:12px;margin-bottom:1rem;">Error: '
       . htmlspecialchars($e->getMessage()) . '</div>';
}

$totalPendientes = count($pendientes);
?>

<div class="admin-section-header">
  <div class="admin-section-title">Accesos EU</div>
  <div style="font-size:.8rem; color:var(--text-muted);">
    <i class="fas fa-earth-europe me-1" style="color:var(--accent);"></i>
    Control de acceso para usuarios europeos
  </div>
</div>

<!-- ── SOLICITUDES PENDIENTES ──────────────────────────────────────── -->
<div class="admin-table-wrapper" style="margin-bottom:2rem;">
  <div style="padding:.75rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-hourglass-half" style="color:#fbbf24;"></i>
    <span style="font-family:'Space Mono',monospace; font-size:.85rem; font-weight:700; color:var(--text-primary);">
      Solicitudes pendientes
    </span>
    <?php if ($totalPendientes > 0): ?>
    <span style="margin-left:4px; background:#fbbf24; color:#000; font-size:.68rem; font-weight:700;
                 padding:1px 7px; border-radius:100px;">
      <?= $totalPendientes ?>
    </span>
    <?php endif; ?>
  </div>

  <?php if (empty($pendientes)): ?>
    <div class="admin-empty">
      <i class="fas fa-check-circle" style="color:#22c55e;"></i>
      <p>No hay solicitudes pendientes.</p>
    </div>
  <?php else: ?>
  <table class="admin-table" id="tabla-pendientes">
    <thead>
      <tr>
        <th style="width:44px;">#</th>
        <th>Usuario</th>
        <th style="width:140px;">IP</th>
        <th style="width:110px;">País</th>
        <th style="width:130px;">Solicitado</th>
        <th style="width:170px; text-align:center;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pendientes as $r): ?>
      <tr id="row-<?= $r['id'] ?>">
        <td style="color:var(--text-muted);font-size:.75rem;"><?= $r['id'] ?></td>

        <td>
          <div style="font-weight:600; color:var(--text-primary); line-height:1.3;">
            <?= htmlspecialchars($r['nombre']) ?>
          </div>
          <div style="font-size:.73rem; color:var(--text-muted);"><?= htmlspecialchars($r['email']) ?></div>
        </td>

        <td style="font-family:'Space Mono',monospace; font-size:.78rem; color:var(--text-secondary);">
          <?= htmlspecialchars($r['ip']) ?>
        </td>

        <td>
          <span style="font-size:.78rem; color:var(--text-secondary);">
            <?= htmlspecialchars($r['pais'] ?: '—') ?>
          </span>
        </td>

        <td style="font-size:.75rem; color:var(--text-muted); white-space:nowrap; font-family:'Space Mono',monospace;">
          <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
        </td>

        <td style="text-align:center;">
          <button class="btn-admin-edit" style="width:auto; padding:4px 12px; font-size:.72rem; gap:4px;"
                  onclick="accionAcceso(<?= $r['id'] ?>, 'aprobar')"
                  title="Aprobar acceso">
            <i class="fas fa-check"></i> Aprobar
          </button>
          <button class="btn-admin-delete" style="width:auto; padding:4px 10px; font-size:.72rem; margin-left:4px;"
                  onclick="accionAcceso(<?= $r['id'] ?>, 'denegar')"
                  title="Denegar acceso">
            <i class="fas fa-ban"></i> Denegar
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- ── HISTORIAL (aprobados / denegados) ──────────────────────────── -->
<div class="admin-table-wrapper">
  <div style="padding:.75rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-history" style="color:var(--accent);"></i>
    <span style="font-family:'Space Mono',monospace; font-size:.85rem; font-weight:700; color:var(--text-primary);">
      Historial de accesos
    </span>
    <span style="margin-left:auto; font-size:.75rem; color:var(--text-muted);">Últimos 200</span>
  </div>

  <?php if (empty($historial)): ?>
    <div class="admin-empty">
      <i class="fas fa-earth-europe"></i>
      <p>Aún no hay accesos procesados.</p>
    </div>
  <?php else: ?>
  <table class="admin-table" id="tabla-historial">
    <thead>
      <tr>
        <th style="width:44px;">#</th>
        <th>Usuario</th>
        <th style="width:140px;">IP</th>
        <th style="width:110px;">País</th>
        <th style="width:100px; text-align:center;">Estado</th>
        <th style="width:130px;">Actualizado</th>
        <th style="width:90px; text-align:center;">Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($historial as $r):
        $esAprobado = $r['estado'] === 'aprobado';
      ?>
      <tr id="row-<?= $r['id'] ?>">
        <td style="color:var(--text-muted);font-size:.75rem;"><?= $r['id'] ?></td>

        <td>
          <div style="font-weight:600; color:var(--text-primary); line-height:1.3;">
            <?= htmlspecialchars($r['nombre']) ?>
          </div>
          <div style="font-size:.73rem; color:var(--text-muted);"><?= htmlspecialchars($r['email']) ?></div>
        </td>

        <td style="font-family:'Space Mono',monospace; font-size:.78rem; color:var(--text-secondary);">
          <?= htmlspecialchars($r['ip']) ?>
        </td>

        <td style="font-size:.78rem; color:var(--text-secondary);">
          <?= htmlspecialchars($r['pais'] ?: '—') ?>
        </td>

        <td style="text-align:center;">
          <?php if ($esAprobado): ?>
            <span style="font-size:.7rem; background:rgba(34,197,94,.12); color:#22c55e;
                         border:1px solid rgba(34,197,94,.3); padding:2px 9px; border-radius:100px; font-weight:700;">
              <i class="fas fa-check me-1"></i>APROBADO
            </span>
          <?php else: ?>
            <span style="font-size:.7rem; background:rgba(239,68,68,.12); color:#ef4444;
                         border:1px solid rgba(239,68,68,.3); padding:2px 9px; border-radius:100px; font-weight:700;">
              <i class="fas fa-ban me-1"></i>DENEGADO
            </span>
          <?php endif; ?>
        </td>

        <td style="font-size:.75rem; color:var(--text-muted); white-space:nowrap; font-family:'Space Mono',monospace;">
          <?= date('d/m/Y H:i', strtotime($r['updated_at'])) ?>
        </td>

        <td style="text-align:center;">
          <?php if ($esAprobado): ?>
            <button class="btn-admin-delete" style="width:auto; padding:4px 8px; font-size:.7rem;"
                    onclick="accionAcceso(<?= $r['id'] ?>, 'denegar')"
                    title="Revocar y denegar">
              <i class="fas fa-ban"></i>
            </button>
          <?php else: ?>
            <button class="btn-admin-edit" style="width:auto; padding:4px 8px; font-size:.7rem;"
                    onclick="accionAcceso(<?= $r['id'] ?>, 'aprobar')"
                    title="Aprobar ahora">
              <i class="fas fa-check"></i>
            </button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<script>
async function accionAcceso(id, accion) {
  var etiqueta = accion === 'aprobar' ? 'aprobar' : 'denegar';
  if (!confirm('¿Seguro que deseas ' + etiqueta + ' este acceso?')) return;

  try {
    var res  = await fetch('api/accesos_eu.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ action: accion, id: id }),
    });
    var data = await res.json();
    if (data.success) {
      showToast(data.message, true);
      setTimeout(function() { location.reload(); }, 700);
    } else {
      showToast(data.message, false);
    }
  } catch (e) {
    showToast('Error de conexión', false);
  }
}
</script>
