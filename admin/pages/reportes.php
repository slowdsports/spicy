<?php
/**
 * Admin - Reportes de usuarios
 * Lista todos los reportes recibidos y el log de fuentes auto-desactivadas.
 */

try {
    $conn = getDBConnection();

    $reportes = $conn->query("
        SELECT r.id,
               r.fuente_id,
               f.nombre  AS fuente_nombre,
               r.user_id,
               u.nombre  AS usuario_nombre,
               r.comentario,
               r.dispositivo,
               r.pais,
               r.ip,
               r.created_at
        FROM canal_reportes r
        LEFT JOIN fuentes   f ON f.id = r.fuente_id
        LEFT JOIN usuarios  u ON u.id = r.user_id
        ORDER BY r.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);

    $autodesactivados = $conn->query("
        SELECT a.id,
               a.fuente_id,
               f.nombre AS fuente_nombre,
               a.total_reportes,
               a.razon,
               a.desactivado_at
        FROM canal_autodesactivados a
        LEFT JOIN fuentes f ON f.id = a.fuente_id
        ORDER BY a.desactivado_at DESC
    ")->fetch_all(MYSQLI_ASSOC);

    // Conteo de reportes por fuente (para badge en la tabla)
    $cuentas = [];
    foreach ($reportes as $r) {
        $cuentas[$r['fuente_id']] = ($cuentas[$r['fuente_id']] ?? 0) + 1;
    }

} catch (Throwable $e) {
    $reportes        = [];
    $autodesactivados = [];
    $cuentas          = [];
    echo '<div style="padding:1rem;background:#991b1b;color:#fff;border-radius:12px;margin-bottom:1rem;">Error: '
       . htmlspecialchars($e->getMessage()) . '</div>';
}

// IDs de fuentes auto-desactivadas (para destacarlas)
$idsAutoDesactivados = array_column($autodesactivados, 'fuente_id');
?>

<div class="admin-section-header">
  <div class="admin-section-title">Reportes de usuarios</div>
  <div style="font-size:.8rem; color:var(--text-muted);">
    <i class="fas fa-flag me-1" style="color:#ef4444;"></i>
    <?= count($reportes) ?> reporte<?= count($reportes) !== 1 ? 's' : '' ?> recibidos
  </div>
</div>

<!-- ── TABLA DE REPORTES ─────────────────────────────────────── -->
<div class="admin-table-wrapper" style="margin-bottom:2rem;">

  <div style="padding:.75rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-flag" style="color:#ef4444;"></i>
    <span style="font-family:'Space Mono',monospace; font-size:.85rem; font-weight:700; color:var(--text-primary);">
      Todos los reportes
    </span>
    <!-- Buscador -->
    <div style="margin-left:auto; position:relative;">
      <i class="fas fa-search" style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.72rem;"></i>
      <input type="text" id="search-reportes" class="admin-search" placeholder="Buscar fuente o usuario..."
             style="padding-left:1.9rem;">
    </div>
  </div>

  <?php if (empty($reportes)): ?>
    <div class="admin-empty">
      <i class="fas fa-flag"></i>
      <p>No hay reportes aún.</p>
    </div>
  <?php else: ?>
    <table class="admin-table" id="tabla-reportes">
      <thead>
        <tr>
          <th style="width:50px;">#</th>
          <th>Fuente</th>
          <th>Usuario</th>
          <th>Comentario</th>
          <th style="width:90px;">Dispositivo</th>
          <th style="width:100px;">País</th>
          <th style="width:110px;">IP</th>
          <th style="width:130px;">Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reportes as $r):
          $esAutoDesactivada = in_array($r['fuente_id'], $idsAutoDesactivados);
          $nombreFuente      = $r['fuente_nombre'] ?? 'Fuente #' . $r['fuente_id'];
          $nombreUsuario     = $r['usuario_nombre'] ?? 'Usuario #' . $r['user_id'];
          $totalFuente       = $cuentas[$r['fuente_id']] ?? 0;
        ?>
        <tr data-search="<?= strtolower(htmlspecialchars($nombreFuente . ' ' . $nombreUsuario)) ?>">
          <td style="color:var(--text-muted);font-size:.75rem;"><?= $r['id'] ?></td>

          <td>
            <div style="display:flex; align-items:center; gap:.4rem; flex-wrap:wrap;">
              <span style="font-weight:600; color:var(--text-primary);">
                <?= htmlspecialchars($nombreFuente) ?>
              </span>
              <?php if ($totalFuente >= 5 || $esAutoDesactivada): ?>
                <span style="font-size:.65rem; background:rgba(239,68,68,.15); color:#ef4444; border:1px solid rgba(239,68,68,.3); padding:1px 6px; border-radius:4px; font-weight:700;">
                  <?= $totalFuente ?> reportes
                </span>
              <?php elseif ($totalFuente > 1): ?>
                <span style="font-size:.65rem; background:rgba(251,191,36,.12); color:#fbbf24; border:1px solid rgba(251,191,36,.3); padding:1px 6px; border-radius:4px;">
                  <?= $totalFuente ?> reportes
                </span>
              <?php endif; ?>
              <?php if ($esAutoDesactivada): ?>
                <span title="Auto-desactivada por acumulación de reportes"
                      style="font-size:.65rem; background:rgba(239,68,68,.2); color:#ef4444; border:1px solid rgba(239,68,68,.4); padding:1px 7px; border-radius:4px; font-weight:700;">
                  ⛔ Desactivada
                </span>
              <?php endif; ?>
            </div>
          </td>

          <td style="color:var(--text-secondary); font-size:.82rem;">
            <?= htmlspecialchars($nombreUsuario) ?>
          </td>

          <td style="color:var(--text-muted); font-size:.8rem; max-width:220px;">
            <?php if (!empty($r['comentario'])): ?>
              <span title="<?= htmlspecialchars($r['comentario']) ?>">
                <?= htmlspecialchars(mb_strimwidth($r['comentario'], 0, 80, '…')) ?>
              </span>
            <?php else: ?>
              <span style="opacity:.4;">—</span>
            <?php endif; ?>
          </td>

          <td>
            <span style="font-size:.75rem; background:var(--accent-soft); color:var(--accent); padding:2px 8px; border-radius:100px;">
              <?= htmlspecialchars($r['dispositivo'] ?: '—') ?>
            </span>
          </td>

          <td style="font-size:.8rem; color:var(--text-secondary);">
            <?= htmlspecialchars($r['pais'] ?: '—') ?>
          </td>

          <td style="font-family:'Space Mono',monospace; font-size:.7rem; color:var(--text-muted);">
            <?= htmlspecialchars($r['ip'] ?: '—') ?>
          </td>

          <td style="font-size:.75rem; color:var(--text-muted); white-space:nowrap;">
            <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- ── FUENTES AUTO-DESACTIVADAS ─────────────────────────────── -->
<div class="admin-table-wrapper">
  <div style="padding:.75rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-robot" style="color:#f59e0b;"></i>
    <span style="font-family:'Space Mono',monospace; font-size:.85rem; font-weight:700; color:var(--text-primary);">
      Fuentes auto-desactivadas
    </span>
    <span style="margin-left:auto; font-size:.75rem; color:var(--text-muted);">
      Se desactivan al acumular 5 reportes
    </span>
  </div>

  <?php if (empty($autodesactivados)): ?>
    <div class="admin-empty">
      <i class="fas fa-robot"></i>
      <p>Ninguna fuente ha sido auto-desactivada.</p>
    </div>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width:50px;">#</th>
          <th>Fuente</th>
          <th style="width:100px;">Reportes</th>
          <th>Razón</th>
          <th style="width:140px;">Desactivada</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($autodesactivados as $a): ?>
        <tr>
          <td style="color:var(--text-muted);font-size:.75rem;"><?= $a['id'] ?></td>
          <td style="font-weight:600; color:var(--text-primary);">
            <?= htmlspecialchars($a['fuente_nombre'] ?? 'Fuente #' . $a['fuente_id']) ?>
            <a href="<?= BASE_URL ?>admin/?p=fuentes" style="font-size:.72rem; color:var(--accent); margin-left:.4rem; text-decoration:none;">
              ver fuentes →
            </a>
          </td>
          <td>
            <span style="font-size:.8rem; background:rgba(239,68,68,.15); color:#ef4444; border:1px solid rgba(239,68,68,.3); padding:2px 10px; border-radius:100px; font-weight:700;">
              <?= (int)$a['total_reportes'] ?>
            </span>
          </td>
          <td style="font-size:.8rem; color:var(--text-muted);">
            <?= htmlspecialchars($a['razon'] ?? '—') ?>
          </td>
          <td style="font-size:.75rem; color:var(--text-muted); white-space:nowrap; font-family:'Space Mono',monospace;">
            <?= date('d/m/Y H:i', strtotime($a['desactivado_at'])) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script>
document.getElementById('search-reportes').addEventListener('input', function () {
  var q = this.value.toLowerCase();
  document.querySelectorAll('#tabla-reportes tbody tr[data-search]').forEach(function (row) {
    row.style.display = row.dataset.search.includes(q) ? '' : 'none';
  });
});
</script>
