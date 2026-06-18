<?php
/**
 * Admin - Proxies de geo-protección
 * Gestiona la lista de proxies usados aleatoriamente para fuentes marcadas.
 */

$conn = getDBConnection();

// Crear tabla si no existe
$conn->query("CREATE TABLE IF NOT EXISTS proxies (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre    VARCHAR(100) NOT NULL DEFAULT '',
    url       VARCHAR(500) NOT NULL,
    activo    TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_url (url(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

try {
    $proxies = $conn->query("SELECT * FROM proxies ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $proxies = [];
}

$activos = count(array_filter($proxies, fn($p) => $p['activo']));
?>

<div class="admin-section-header">
  <div>
    <div class="admin-section-title">Proxies de geo-protección</div>
    <p style="font-size:0.82rem; color:var(--text-muted); margin:0.15rem 0 0;">
      Se elige uno aleatoriamente para usuarios
      <strong style="color:var(--accent);">Spicy</strong> y
      <strong style="color:var(--accent);">Admin</strong>
      en fuentes con la opción "Usar proxy" activada.
    </p>
  </div>
  <button class="btn-admin-add" onclick="abrirModalProxy()">
    <i class="fas fa-plus"></i> Nuevo proxy
  </button>
</div>

<!-- Contadores -->
<div style="display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap;">
  <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:0.75rem 1.25rem; display:flex; align-items:center; gap:0.75rem; min-width:140px;">
    <i class="fas fa-shield-alt" style="color:var(--accent); font-size:1.25rem;"></i>
    <div>
      <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary); line-height:1;"><?= $activos ?></div>
      <div style="font-size:0.72rem; color:var(--text-muted);">Activos</div>
    </div>
  </div>
  <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:0.75rem 1.25rem; display:flex; align-items:center; gap:0.75rem; min-width:140px;">
    <i class="fas fa-server" style="color:var(--text-muted); font-size:1.25rem;"></i>
    <div>
      <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary); line-height:1;"><?= count($proxies) ?></div>
      <div style="font-size:0.72rem; color:var(--text-muted);">Total</div>
    </div>
  </div>
</div>

<!-- Ejemplo de cómo funciona el proxy -->
<div style="background:rgba(139,92,246,0.06); border:1px solid rgba(139,92,246,0.2); border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.5rem; font-size:0.81rem; color:var(--text-secondary); line-height:1.6;">
  <i class="fas fa-info-circle me-1" style="color:var(--accent);"></i>
  <strong style="color:var(--text-primary);">¿Cómo funciona?</strong>
  La URL del proxy se antepone a la URL del canal:
  <div style="margin-top:0.4rem; font-family:'Space Mono',monospace; font-size:0.73rem; color:var(--accent); word-break:break-all; background:var(--bg-body); padding:0.4rem 0.75rem; border-radius:6px;">
    https://mi-proxy.onrender.com/<span style="color:var(--text-muted);">https://canal.com/stream.mpd</span>
  </div>
</div>

<div class="admin-table-wrapper">
  <table class="admin-table" id="tabla-proxies">
    <thead>
      <tr>
        <th style="width:40px;">#</th>
        <th>Nombre</th>
        <th>URL del proxy</th>
        <th style="width:90px;">Estado</th>
        <th style="width:80px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($proxies)): ?>
        <tr><td colspan="5">
          <div class="admin-empty">
            <i class="fas fa-shield-alt"></i>
            <p>No hay proxies registrados.<br>Agrega el primero con el botón de arriba.</p>
          </div>
        </td></tr>
      <?php else: ?>
        <?php foreach ($proxies as $p): ?>
        <tr>
          <td style="color:var(--text-muted); font-size:0.75rem;"><?= $p['id'] ?></td>
          <td style="font-weight:600; color:var(--text-primary);">
            <?= htmlspecialchars($p['nombre'] ?: '—') ?>
          </td>
          <td style="font-family:'Space Mono',monospace; font-size:0.78rem; color:var(--text-secondary); max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
              title="<?= htmlspecialchars($p['url']) ?>">
            <?= htmlspecialchars($p['url']) ?>
          </td>
          <td>
            <span class="<?= $p['activo'] ? 'badge-activo' : 'badge-inactivo' ?>"
                  style="cursor:pointer;" title="Clic para cambiar estado"
                  onclick="toggleProxy(<?= $p['id'] ?>, this)">
              <?= $p['activo'] ? 'Activo' : 'Inactivo' ?>
            </span>
          </td>
          <td>
            <div class="d-flex gap-1">
              <button class="btn-admin-edit" title="Editar"
                onclick="abrirModalProxy(<?= htmlspecialchars(json_encode($p)) ?>)">
                <i class="fas fa-pen"></i>
              </button>
              <button class="btn-admin-delete" title="Borrar"
                onclick="eliminarProxy(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nombre'] ?: $p['url'], ENT_QUOTES) ?>')">
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

<!-- ── MODAL: Crear / Editar proxy ─────────────────────────────────── -->
<div class="modal fade admin-modal" id="modalProxy" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalProxyTitulo">Nuevo proxy</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="proxy-id">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">
              Nombre
              <span style="color:var(--text-muted); font-weight:400; font-size:0.75rem;">(opcional)</span>
            </label>
            <input type="text" id="proxy-nombre" class="form-control"
                   placeholder="Ej: Proxy US-1, Render EU…">
          </div>

          <div class="col-12">
            <label class="form-label">URL del proxy <span style="color:#ef4444;">*</span></label>
            <input type="text" id="proxy-url" class="form-control"
                   placeholder="https://mi-proxy.onrender.com/">
            <p style="font-size:0.72rem; color:var(--text-muted); margin:0.4rem 0 0; line-height:1.5;">
              La URL del canal se añadirá justo después, sin barra separadora adicional.<br>
              Ejemplo: <code style="color:var(--accent);">https://proxy.com/https://canal.com/stream.mpd</code>
            </p>
          </div>

          <div class="col-12">
            <label class="form-label">Estado</label>
            <select id="proxy-activo" class="form-select">
              <option value="1">Activo — se incluye en la selección aleatoria</option>
              <option value="0">Inactivo — ignorado</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-interact" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn-admin-add" onclick="guardarProxy()">
          <i class="fas fa-save me-1"></i> Guardar
        </button>
      </div>

    </div>
  </div>
</div>

<script>
function abrirModalProxy(data = null) {
  document.getElementById('proxy-id').value     = data?.id     ?? '';
  document.getElementById('proxy-nombre').value = data?.nombre ?? '';
  document.getElementById('proxy-url').value    = data?.url    ?? '';
  document.getElementById('proxy-activo').value = String(data?.activo ?? 1);
  document.getElementById('modalProxyTitulo').textContent = data ? 'Editar proxy' : 'Nuevo proxy';
  new bootstrap.Modal(document.getElementById('modalProxy')).show();
}

function guardarProxy() {
  const url = document.getElementById('proxy-url').value.trim();
  if (!url) { adminToast('La URL del proxy es obligatoria.', 'error'); return; }

  const data = {
    id:     document.getElementById('proxy-id').value,
    nombre: document.getElementById('proxy-nombre').value.trim(),
    url,
    activo: document.getElementById('proxy-activo').value,
  };

  fetch(API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'save', entity: 'proxy', data }),
  })
  .then(r => r.json())
  .then(res => {
    adminToast(res.message, res.success ? 'success' : 'error');
    if (res.success) setTimeout(() => location.reload(), 800);
  })
  .catch(() => adminToast('Error de conexión', 'error'));
}

function toggleProxy(id, el) {
  fetch(API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'toggle', entity: 'proxy', id }),
  })
  .then(r => r.json())
  .then(res => {
    if (!res.success) { adminToast(res.message, 'error'); return; }
    const nowActive = el.classList.contains('badge-inactivo');
    el.className   = nowActive ? 'badge-activo' : 'badge-inactivo';
    el.textContent = nowActive ? 'Activo' : 'Inactivo';
  })
  .catch(() => adminToast('Error de conexión', 'error'));
}

function eliminarProxy(id, label) {
  if (!confirm(`¿Eliminar el proxy "${label}"?`)) return;
  fetch(API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', entity: 'proxy', id }),
  })
  .then(r => r.json())
  .then(res => {
    adminToast(res.message, res.success ? 'success' : 'error');
    if (res.success) setTimeout(() => location.reload(), 800);
  })
  .catch(() => adminToast('Error de conexión', 'error'));
}
</script>
