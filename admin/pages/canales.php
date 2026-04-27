<?php
/**
 * Admin - Canales
 * Lista todos los canales. Crear / Editar / Borrar con modal.
 */

try {
    $conn = getDBConnection();

    // Obtener canales con nombre de categoría (JOIN)
    $canales = $conn->query("
        SELECT c.id, c.nombre, c.imagen, c.activo,
               cat.nombre AS categoria_nombre, cat.id AS categoria_id
        FROM canales c
        LEFT JOIN categorias_canal cat ON c.categoria = cat.id
        ORDER BY c.nombre ASC
    ")->fetch_all(MYSQLI_ASSOC);

    // Obtener categorías para el select del modal
    $categorias = $conn->query("SELECT id, nombre FROM categorias_canal ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $canales    = [];
    $categorias = [];
}
?>

<div class="admin-section-header">
  <div class="admin-section-title">Canales</div>
  <div class="d-flex gap-2 align-items-center">
    <!-- Búsqueda rápida -->
    <div style="position:relative;">
      <i class="fas fa-search" style="position:absolute; left:0.7rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:0.78rem;"></i>
      <input type="text" id="search-canales" class="admin-search" placeholder="Buscar canal...">
    </div>
    <!-- Botón nuevo canal -->
    <button class="btn-admin-add" onclick="abrirModalCanal()">
      <i class="fas fa-plus"></i> Nuevo canal
    </button>
  </div>
</div>

<!-- Tabla de canales -->
<div class="admin-table-wrapper">
  <table class="admin-table" id="tabla-canales">
    <thead>
      <tr>
        <th style="width:40px;">#</th>
        <th style="width:50px;">imagen</th>
        <th>Nombre</th>
        <th>Categoría</th>
        <th style="width:90px;">Estado</th>
        <th style="width:80px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($canales)): ?>
        <tr><td colspan="6">
          <div class="admin-empty"><i class="fas fa-tv"></i><p>No hay canales registrados.</p></div>
        </td></tr>
      <?php else: ?>
        <?php foreach ($canales as $c): ?>
        <tr data-nombre="<?= strtolower(htmlspecialchars($c['nombre'])) ?>">
          <td style="color:var(--text-muted); font-size:0.75rem;"><?= $c['id'] ?></td>
          <td>
            <?php if ($c['imagen']): ?>
              <img src="<?= htmlspecialchars($c['imagen']) ?>" alt="" class="table-imagen"
                   onerror="this.style.opacity='0.3'">
            <?php else: ?>
              <div style="width:32px; height:32px; background:var(--bg-secondary); border-radius:6px; border:1px solid var(--border); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-tv" style="color:var(--text-muted); font-size:0.8rem;"></i>
              </div>
            <?php endif; ?>
          </td>
          <td style="font-weight:600; color:var(--text-primary);"><?= htmlspecialchars($c['nombre']) ?></td>
          <td>
            <span style="font-size:0.75rem; background:var(--accent-soft); color:var(--accent); padding:2px 8px; border-radius:100px;">
              <?= htmlspecialchars($c['categoria_nombre'] ?? '—') ?>
            </span>
          </td>
          <td>
            <span class="<?= $c['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
              <?= $c['activo'] ? 'Activo' : 'Inactivo' ?>
            </span>
          </td>
          <td>
            <div class="d-flex gap-1">
              <!-- Editar -->
              <button class="btn-admin-edit" title="Editar"
                onclick="abrirModalCanal(<?= htmlspecialchars(json_encode($c)) ?>)">
                <i class="fas fa-pen"></i>
              </button>
              <!-- Borrar -->
              <button class="btn-admin-delete" title="Borrar"
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

<!-- ============================================================
     MODAL: Crear / Editar canal
     ============================================================ -->
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
          <label class="form-label">Nombre del canal <span style="color:#ef4444;">*</span></label>
          <input type="text" id="canal-nombre" class="form-control" placeholder="Ej: ESPN HD">
        </div>

        <div class="mb-3">
          <label class="form-label">URL del imagen (imagen)</label>
          <input type="url" id="canal-imagen" class="form-control" placeholder="https://...">
          <!-- Preview del imagen -->
          <img id="canal-img-preview" src="" alt="" style="height:36px; margin-top:8px; display:none; border-radius:6px; background:var(--bg-secondary); padding:4px; border:1px solid var(--border);">
        </div>

        <div class="mb-3">
          <label class="form-label">Categoría <span style="color:#ef4444;">*</span></label>
          <select id="canal-categoria" class="form-select">
            <option value="">-- Seleccionar --</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
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
