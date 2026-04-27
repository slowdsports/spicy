<?php
/**
 * Admin - Ligas
 * Lista las ligas importadas desde Sofascore. CRUD + botón de importación.
 */

try {
    $conn   = getDBConnection();
    $ligas  = $conn->query("
        SELECT id, ligaNombre, ligaImg, ligaPais, tipo, season, activo
        FROM ligas ORDER BY ligaNombre ASC
    ")->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $ligas = [];
}
?>

<div class="admin-section-header">
  <div class="admin-section-title">Ligas deportivas</div>
  <div class="d-flex gap-2 align-items-center flex-wrap">

    <!-- Buscador -->
    <div style="position:relative;">
      <i class="fas fa-search" style="position:absolute; left:0.7rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:0.78rem;"></i>
      <input type="text" id="search-ligas" class="admin-search" placeholder="Buscar liga...">
    </div>

    <!-- Importar desde Sofascore -->
    <button class="btn-sofa" onclick="mostrarImportarLiga()">
      <i class="fas fa-cloud-download-alt"></i> Importar vía Sofascore
    </button>

    <!-- Crear manualmente -->
    <button class="btn-admin-add" onclick="abrirModalLiga()">
      <i class="fas fa-plus"></i> Nueva liga
    </button>
  </div>
</div>

<!-- Panel de importación Sofascore -->
<div id="panel-sofa-liga" style="display:none; margin-bottom:1.25rem;">
  <div style="background:var(--bg-card); border:1px solid rgba(34,197,94,0.3); border-radius:14px; padding:1.25rem;">
    <p style="font-size:0.82rem; font-weight:700; color:#22c55e; margin:0 0 0.75rem;">
      <i class="fas fa-cloud-download-alt me-1"></i>
      Importar partidos desde Sofascore
    </p>
    <p style="font-size:0.78rem; color:var(--text-muted); margin:0 0 1rem;">
      Introduce el ID de la liga en Sofascore. Los partidos, equipos e imágenes se importarán automáticamente.
      <a href="https://www.sofascore.com" target="_blank" style="color:var(--accent);">¿Cómo encontrar el ID?</a>
    </p>
    <div class="d-flex gap-2 flex-wrap align-items-end">
      <div>
        <label style="font-size:0.75rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:4px;">ID de liga Sofascore</label>
        <input type="number" id="sofa-liga-id" class="form-control"
               style="background:var(--bg-input); border:1px solid var(--border); color:var(--text-primary); border-radius:10px; width:160px;"
               placeholder="Ej: 17">
      </div>
      <button class="btn-sofa" onclick="importarLiga()" id="btn-importar-liga">
        <i class="fas fa-play"></i> Importar
      </button>
      <button class="btn-interact" style="border-radius:10px;" onclick="document.getElementById('panel-sofa-liga').style.display='none'">
        Cancelar
      </button>
    </div>
    <!-- Resultado de la importación -->
    <div id="sofa-resultado" style="margin-top:0.75rem; display:none; font-size:0.82rem; padding:0.6rem 1rem; border-radius:8px;"></div>
  </div>
</div>

<!-- Tabla de ligas -->
<div class="admin-table-wrapper">
  <table class="admin-table" id="tabla-ligas">
    <thead>
      <tr>
        <th style="width:60px;">ID</th>
        <th style="width:50px;">Logo</th>
        <th>Nombre</th>
        <th>País</th>
        <th>Deporte</th>
        <th>Temporada</th>
        <th style="width:80px;">Estado</th>
        <th style="width:80px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($ligas)): ?>
        <tr><td colspan="8">
          <div class="admin-empty"><i class="fas fa-trophy"></i><p>No hay ligas. Importa una desde Sofascore.</p></div>
        </td></tr>
      <?php else: ?>
        <?php foreach ($ligas as $l): ?>
        <tr data-nombre="<?= strtolower(htmlspecialchars($l['ligaNombre'] . ' ' . $l['ligaPais'])) ?>">
          <td style="font-family:'Space Mono',monospace; font-size:0.75rem; color:var(--text-muted);"><?= $l['id'] ?></td>
          <td>
            <?php
            // Las imágenes las guarda sofa.php en assets/img/ligas/sf/{id}.png
            $imgPath = BASE_URL . "assets/img/ligas/sf/{$l['id']}.png";
            ?>
            <img src="<?= $imgPath ?>" alt="" class="table-logo"
                 onerror="this.src=''; this.style.opacity='0.2';">
          </td>
          <td style="font-weight:600; color:var(--text-primary);"><?= htmlspecialchars($l['ligaNombre']) ?></td>
          <td style="font-size:0.78rem; color:var(--text-muted);"><?= htmlspecialchars($l['ligaPais'] ?? '—') ?></td>
          <td>
            <span style="font-size:0.72rem; background:var(--accent-soft); color:var(--accent); padding:2px 8px; border-radius:100px;">
              <?= htmlspecialchars($l['tipo'] ?? 'soccer') ?>
            </span>
          </td>
          <td style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($l['season'] ?? '—') ?></td>
          <td><span class="<?= $l['activo'] ? 'badge-activo' : 'badge-inactivo' ?>"><?= $l['activo'] ? 'Activa' : 'Inactiva' ?></span></td>
          <td>
            <div class="d-flex gap-1">
              <button class="btn-admin-edit" title="Editar"
                onclick="abrirModalLiga(<?= htmlspecialchars(json_encode($l)) ?>)">
                <i class="fas fa-pen"></i>
              </button>
              <button class="btn-admin-delete" title="Borrar"
                onclick="confirmarBorrar('liga', <?= $l['id'] ?>, '<?= htmlspecialchars($l['ligaNombre'], ENT_QUOTES) ?>')">
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
     MODAL: Crear / Editar liga
     ============================================================ -->
<div class="modal fade admin-modal" id="modalLiga" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLigaTitulo">Nueva liga</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="liga-id">
        <div class="mb-3">
          <label class="form-label">ID Sofascore <span style="color:#ef4444;">*</span></label>
          <input type="number" id="liga-sf-id" class="form-control" placeholder="Ej: 17">
        </div>
        <div class="mb-3">
          <label class="form-label">Nombre <span style="color:#ef4444;">*</span></label>
          <input type="text" id="liga-nombre" class="form-control" placeholder="UEFA Champions League">
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">País</label>
            <input type="text" id="liga-pais" class="form-control" placeholder="Europa">
          </div>
          <div class="col-6">
            <label class="form-label">Deporte</label>
            <select id="liga-tipo" class="form-select">
              <option value="soccer">Fútbol (soccer)</option>
              <option value="basketball">Básquet</option>
              <option value="tennis">Tenis</option>
              <option value="baseball">Béisbol</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-interact" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn-admin-add" onclick="guardarLiga()">
          <i class="fas fa-save me-1"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function mostrarImportarLiga() {
  document.getElementById('panel-sofa-liga').style.display = 'block';
  document.getElementById('sofa-liga-id').focus();
}

// Llama a sofa.php pasando el ID de la liga
function importarLiga() {
  const ligaId = document.getElementById('sofa-liga-id').value.trim();
  if (!ligaId) { adminToast('Introduce un ID de liga válido.', 'error'); return; }

  const btn = document.getElementById('btn-importar-liga');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importando...';
  btn.disabled = true;

  const res = document.getElementById('sofa-resultado');
  res.style.display = 'none';

  // Llamada al sofa.php del admin con el ID de liga
  fetch(`<?= BASE_URL ?>admin/sofa.php?filtrarLiga=${ligaId}`)
    .then(r => r.text())
    .then(text => {
      res.style.display = 'block';
      res.style.background = 'rgba(34,197,94,0.1)';
      res.style.border = '1px solid rgba(34,197,94,0.3)';
      res.style.color = '#22c55e';
      res.textContent = text || 'Importación completada.';
      // Recargar la página para ver los cambios
      setTimeout(() => location.reload(), 1500);
    })
    .catch(err => {
      res.style.display = 'block';
      res.style.background = 'rgba(239,68,68,0.1)';
      res.style.border = '1px solid rgba(239,68,68,0.3)';
      res.style.color = '#ef4444';
      res.textContent = 'Error al importar: ' + err.message;
    })
    .finally(() => {
      btn.innerHTML = '<i class="fas fa-play"></i> Importar';
      btn.disabled = false;
    });
}

// Filtro búsqueda
document.getElementById('search-ligas').addEventListener('input', function () {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#tabla-ligas tbody tr[data-nombre]').forEach(r => {
    r.style.display = r.dataset.nombre.includes(q) ? '' : 'none';
  });
});
</script>
