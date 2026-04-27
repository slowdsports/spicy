<?php
/**
 * Admin - Fuentes
 * Lista todas las fuentes con su canal asociado. CRUD con modal.
 */

try {
    $conn = getDBConnection();

    // Fuentes con nombres de canal, país y tipo
    $fuentes = $conn->query("
        SELECT f.id, f.nombre, f.url, f.ck_key, f.ck_keyid, f.epg, f.activo,
               c.nombre  AS canal_nombre,  f.canal  AS canal_id,
               p.paisNombre AS pais_nombre, f.pais  AS pais_id,
               t.nombre  AS tipo_nombre,   f.tipo   AS tipo_id
        FROM fuentes f
        LEFT JOIN canales     c ON f.canal = c.id
        LEFT JOIN paises      p ON f.pais  = p.id
        LEFT JOIN tipos_fuente t ON f.tipo  = t.id
        ORDER BY c.nombre ASC, f.nombre ASC
    ")->fetch_all(MYSQLI_ASSOC);

    // Datos para selects del modal
    $canales = $conn->query("SELECT id, nombre FROM canales WHERE activo=1 ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
    $paises  = $conn->query("SELECT id, paisNombre FROM paises ORDER BY paisNombre ASC")->fetch_all(MYSQLI_ASSOC);
    $tipos   = $conn->query("SELECT id, nombre FROM tipos_fuente ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $fuentes = $canales = $paises = $tipos = [];
}
?>

<div class="admin-section-header">
  <div class="admin-section-title">Fuentes de transmisión</div>
  <div class="d-flex gap-2 align-items-center">
    <div style="position:relative;">
      <i class="fas fa-search" style="position:absolute; left:0.7rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:0.78rem;"></i>
      <input type="text" id="search-fuentes" class="admin-search" placeholder="Buscar fuente...">
    </div>
    <button class="btn-admin-add" onclick="abrirModalFuente()">
      <i class="fas fa-plus"></i> Nueva fuente
    </button>
  </div>
</div>

<div class="admin-table-wrapper">
  <table class="admin-table" id="tabla-fuentes">
    <thead>
      <tr>
        <th style="width:40px;">#</th>
        <th>Nombre</th>
        <th>Canal</th>
        <th>Tipo</th>
        <th>País</th>
        <th>DRM</th>
        <th style="width:80px;">Estado</th>
        <th style="width:80px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($fuentes)): ?>
        <tr><td colspan="8">
          <div class="admin-empty"><i class="fas fa-broadcast-tower"></i><p>No hay fuentes registradas.</p></div>
        </td></tr>
      <?php else: ?>
        <?php foreach ($fuentes as $f): ?>
        <tr data-nombre="<?= strtolower(htmlspecialchars($f['nombre'] . ' ' . $f['canal_nombre'])) ?>">
          <td style="color:var(--text-muted); font-size:0.75rem;"><?= $f['id'] ?></td>
          <td style="font-weight:600; color:var(--text-primary); max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
            <?= htmlspecialchars($f['nombre']) ?>
          </td>
          <td>
            <span style="font-size:0.78rem; background:var(--accent-soft); color:var(--accent); padding:2px 8px; border-radius:100px;">
              <?= htmlspecialchars($f['canal_nombre'] ?? '—') ?>
            </span>
          </td>
          <td style="font-size:0.78rem;"><?= htmlspecialchars($f['tipo_nombre'] ?? '—') ?></td>
          <td style="font-size:0.78rem; color:var(--text-muted);"><?= htmlspecialchars($f['pais_nombre'] ?? '—') ?></td>
          <!-- Indicador DRM -->
          <td>
            <?php if ($f['ck_key']): ?>
              <span style="font-size:0.68rem; background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.3); padding:1px 6px; border-radius:4px; font-weight:700;">
                <i class="fas fa-lock me-1"></i>DRM
              </span>
            <?php else: ?>
              <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="<?= $f['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
              <?= $f['activo'] ? 'Activa' : 'Inactiva' ?>
            </span>
          </td>
          <td>
            <div class="d-flex gap-1">
              <button class="btn-admin-edit" title="Editar"
                onclick="abrirModalFuente(<?= htmlspecialchars(json_encode($f)) ?>)">
                <i class="fas fa-pen"></i>
              </button>
              <button class="btn-admin-delete" title="Borrar"
                onclick="confirmarBorrar('fuente', <?= $f['id'] ?>, '<?= htmlspecialchars($f['nombre'], ENT_QUOTES) ?>')">
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
     MODAL: Crear / Editar fuente
     ============================================================ -->
<div class="modal fade admin-modal" id="modalFuente" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalFuenteTitulo">Nueva fuente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="fuente-id">

        <div class="row g-3">
          <!-- Nombre -->
          <div class="col-12 col-md-6">
            <label class="form-label">Nombre <span style="color:#ef4444;">*</span></label>
            <input type="text" id="fuente-nombre" class="form-control" placeholder="Ej: ESPN HD - Latinoamérica">
          </div>

          <!-- Canal -->
          <div class="col-12 col-md-6">
            <label class="form-label">Canal <span style="color:#ef4444;">*</span></label>
            <select id="fuente-canal" class="form-select">
              <option value="">-- Seleccionar canal --</option>
              <?php foreach ($canales as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- URL -->
          <div class="col-12">
            <label class="form-label">URL del stream <span style="color:#ef4444;">*</span></label>
            <input type="text" id="fuente-url" class="form-control" placeholder="https://... o m3u8://...">
          </div>

          <!-- Tipo -->
          <div class="col-12 col-md-4">
            <label class="form-label">Tipo <span style="color:#ef4444;">*</span></label>
            <select id="fuente-tipo" class="form-select" onchange="toggleDRM()">
              <option value="">-- Seleccionar --</option>
              <?php foreach ($tipos as $t): ?>
                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- País -->
          <div class="col-12 col-md-4">
            <label class="form-label">País</label>
            <select id="fuente-pais" class="form-select">
              <option value="">-- Internacional --</option>
              <?php foreach ($paises as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['paisNombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- EPG -->
          <div class="col-12 col-md-4">
            <label class="form-label">EPG ID</label>
            <input type="text" id="fuente-epg" class="form-control" placeholder="epg.channel.id">
          </div>

          <!-- Campos DRM (solo visibles si tipo = dash-drm) -->
          <div class="col-12" id="drm-fields" style="display:none;">
            <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:1rem;">
              <p style="font-size:0.78rem; font-weight:700; color:#ef4444; margin:0 0 0.75rem;">
                <i class="fas fa-lock me-1"></i> Configuración DRM
              </p>
              <div class="row g-2">
                <div class="col-12 col-md-6">
                  <label class="form-label">CK Key</label>
                  <input type="text" id="fuente-ck-key" class="form-control" placeholder="Clave de descifrado">
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">CK Key ID</label>
                  <input type="text" id="fuente-ck-keyid" class="form-control" placeholder="ID de la clave">
                </div>
              </div>
            </div>
          </div>

          <!-- Estado -->
          <div class="col-12 col-md-4">
            <label class="form-label">Estado</label>
            <select id="fuente-activo" class="form-select">
              <option value="1">Activa</option>
              <option value="0">Inactiva</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-interact" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn-admin-add" onclick="guardarFuente()">
          <i class="fas fa-save me-1"></i> Guardar
        </button>
      </div>

    </div>
  </div>
</div>

<script>
// Mostrar / ocultar campos DRM según el tipo seleccionado
function toggleDRM() {
  const select = document.getElementById('fuente-tipo');
  const opt    = select.options[select.selectedIndex];
  const esDRM  = opt && opt.text.toLowerCase().includes('drm');
  document.getElementById('drm-fields').style.display = esDRM ? 'block' : 'none';
}

// Filtro búsqueda
document.getElementById('search-fuentes').addEventListener('input', function () {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#tabla-fuentes tbody tr[data-nombre]').forEach(r => {
    r.style.display = r.dataset.nombre.includes(q) ? '' : 'none';
  });
});
</script>
