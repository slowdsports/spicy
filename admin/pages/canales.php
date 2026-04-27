<?php
/**
 * Admin - Canales
 * Lista canales + genera channels.json
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = getDBConnection();
    $conn->set_charset("utf8mb4");

    /* =====================================================
       CONSULTA
    ===================================================== */
    $canales = $conn->query("
        SELECT
            c.id,
            c.nombre,
            c.logo,
            c.category,
            c.views,
            c.activo
        FROM canales c
        ORDER BY c.nombre ASC
    ")->fetch_all(MYSQLI_ASSOC);

    /* =====================================================
       GENERAR JSON
       Ruta: /now/data/channels.json
    ===================================================== */
    $jsonChannels = [];

    foreach ($canales as $c) {

        $jsonChannels[] = [
            'id'          => (int)$c['id'],
            'name'        => $c['nombre'] ?? '',
            'category'    => $c['category'] ?? '',
            'logo'        => $c['logo'] ?? '',
            'description' => '',
            'views'       => $c['views'] ?: '0',
            'active'      => (int)$c['activo']
        ];
    }

    $dir = __DIR__ . '/../../data';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents(
        $dir . '/channels.json',
        json_encode(
            $jsonChannels,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        )
    );

} catch (Throwable $e) {

    $canales = [];

    echo "
    <div style='padding:20px;background:#991b1b;color:#fff;border-radius:12px;margin-bottom:20px;'>
        Error: " . htmlspecialchars($e->getMessage()) . "
    </div>";
}
?>

<div class="admin-section-header">
  <div class="admin-section-title">Canales</div>

  <div class="d-flex gap-2 align-items-center">

    <!-- Buscar -->
    <div style="position:relative;">
      <i class="fas fa-search"
         style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.78rem;"></i>

      <input type="text"
             id="search-canales"
             class="admin-search"
             placeholder="Buscar canal...">
    </div>

    <!-- Nuevo -->
    <button class="btn-admin-add" onclick="abrirModalCanal()">
      <i class="fas fa-plus"></i> Nuevo canal
    </button>

  </div>
</div>

<!-- TABLA -->
<div class="admin-table-wrapper">

<table class="admin-table" id="tabla-canales">

<thead>
<tr>
  <th style="width:55px;">#</th>
  <th style="width:60px;">Logo</th>
  <th>Nombre</th>
  <th>Categoría</th>
  <th style="width:90px;">Views</th>
  <th style="width:90px;">Estado</th>
  <th style="width:90px;">Acciones</th>
</tr>
</thead>

<tbody>

<?php if (empty($canales)): ?>

<tr>
<td colspan="7">
<div class="admin-empty">
<i class="fas fa-tv"></i>
<p>No hay canales registrados.</p>
</div>
</td>
</tr>

<?php else: ?>

<?php foreach ($canales as $c): ?>

<tr data-nombre="<?= strtolower(htmlspecialchars($c['nombre'])) ?>">

<td style="color:var(--text-muted);font-size:.75rem;">
<?= $c['id'] ?>
</td>

<td>
<?php if (!empty($c['logo'])): ?>

<img src="<?= htmlspecialchars($c['logo']) ?>"
     class="table-imagen"
     style="width:36px"
     onerror="this.style.opacity='.25'">

<?php else: ?>

<div style="width:34px;height:34px;border-radius:8px;background:var(--bg-secondary);display:flex;align-items:center;justify-content:center;border:1px solid var(--border);">
<i class="fas fa-tv" style="color:var(--text-muted);font-size:.8rem;"></i>
</div>

<?php endif; ?>
</td>

<td style="font-weight:600;color:var(--text-primary);">
<?= htmlspecialchars($c['nombre']) ?>
</td>

<td>
<span style="font-size:.75rem;background:var(--accent-soft);color:var(--accent);padding:2px 8px;border-radius:100px;">
<?= htmlspecialchars($c['category'] ?: '—') ?>
</span>
</td>

<td style="color:var(--text-secondary);">
<?= htmlspecialchars($c['views'] ?: '0') ?>
</td>

<td>
<span class="<?= $c['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
<?= $c['activo'] ? 'Activo' : 'Inactivo' ?>
</span>
</td>

<td>
<div class="d-flex gap-1">

<button class="btn-admin-edit"
        title="Editar"
        onclick='abrirModalCanal(<?= json_encode($c, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'>
<i class="fas fa-pen"></i>
</button>

<button class="btn-admin-delete"
        title="Borrar"
        onclick="confirmarBorrar('canal', <?= $c['id'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>')">
<i class="fas fa-trash"></i>
</button>

</div>
</td>

</tr>

<?php endforeach; ?>
<?php endif; ?>

</tbody>
</table>
</div>

<!-- ==========================================================
MODAL
========================================================== -->
<div class="modal fade admin-modal" id="modalCanal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title" id="modalCanalTitulo">Nuevo canal</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" id="canal-id">

<div class="mb-3">
<label class="form-label">Nombre</label>
<input type="text" id="canal-nombre" class="form-control">
</div>

<div class="mb-3">
<label class="form-label">Logo URL</label>
<input type="url" id="canal-logo" class="form-control">
<img id="canal-logo-preview"
     style="height:36px;margin-top:8px;display:none;border-radius:6px;background:var(--bg-secondary);padding:4px;border:1px solid var(--border);">
</div>

<div class="mb-3">
<label class="form-label">Categoría</label>
<input type="text" id="canal-category" class="form-control">
</div>

<div class="mb-3">
<label class="form-label">Views</label>
<input type="text" id="canal-views" class="form-control">
</div>

<div class="mb-1">
<label class="form-label">Estado</label>
<select id="canal-activo" class="form-select">
<option value="1">Activo</option>
<option value="0">Inactivo</option>
</select>
</div>

</div>

<div class="modal-footer">
<button type="button" class="btn-interact" data-bs-dismiss="modal">Cancelar</button>

<button type="button" class="btn-admin-add" onclick="guardarCanal()">
<i class="fas fa-save me-1"></i> Guardar
</button>
</div>

</div>
</div>
</div>

<!-- Preview de imagen al escribir la URL -->
<script>
document.getElementById('canal-imagen').addEventListener('input', function () {
  const prev = document.getElementById('canal-img-preview');
  if (this.value) { prev.src = this.value; prev.style.display = 'inline-block'; }
  else { prev.style.display = 'none'; }
});

// Filtro de búsqueda en tabla
document.getElementById('search-canales').addEventListener('input', function () {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#tabla-canales tbody tr[data-nombre]').forEach(row => {
    row.style.display = row.dataset.nombre.includes(q) ? '' : 'none';
  });
});
</script>