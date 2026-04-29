/**
 * StreamHub Admin - JavaScript principal
 *
 * Funciones compartidas por todas las páginas del panel:
 *  - Abrir/llenar modales de cada entidad
 *  - Guardar cambios via fetch a admin/api/crud.php
 *  - Confirmar y ejecutar borrado
 *  - Toast de notificaciones
 */

const API = '../admin/api/crud.php'; // ruta relativa desde admin/

// ============================================================
// TOAST DE NOTIFICACIONES
// ============================================================
function adminToast(msg, type = 'success') {
  let t = document.getElementById('admin-toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'admin-toast';
    document.body.appendChild(t);
  }
  t.className = 'show ' + type;
  t.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${msg}`;
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 3200);
}

// ============================================================
// CONFIRMACIÓN DE BORRADO
// Usa un modal Bootstrap o confirm() nativo
// ============================================================
function confirmarBorrar(entity, id, nombre) {
  if (!confirm(`¿Borrar "${nombre}"?\nEsta acción no se puede deshacer.`)) return;

  fetch(API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', entity, id })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      adminToast(res.message, 'success');
      setTimeout(() => location.reload(), 800);
    } else {
      adminToast(res.message, 'error');
    }
  })
  .catch(() => adminToast('Error de conexión', 'error'));
}

// ============================================================
// ── CANALES ──────────────────────────────────────────────────
// ============================================================

/**
 * Abre el modal de canal.
 * @param {Object|null} data - Si se pasa, llena el formulario para editar.
 */
function abrirModalCanal(data = null) {
  document.getElementById('canal-id').value        = data?.id       ?? '';
  document.getElementById('canal-nombre').value    = data?.nombre   ?? '';
  document.getElementById('canal-imagen').value    = data?.imagen   ?? '';
  document.getElementById('canal-categoria').value = data?.categoria_id ?? '';
  document.getElementById('canal-activo').value    = data?.activo   ?? '1';
  document.getElementById('modalCanalTitulo').textContent = data ? 'Editar canal' : 'Nuevo canal';

  // Actualizar preview de imagen
  const prev = document.getElementById('canal-img-preview');
  if (data?.imagen) { prev.src = data.imagen; prev.style.display = 'inline-block'; }
  else              { prev.style.display = 'none'; }

  new bootstrap.Modal(document.getElementById('modalCanal')).show();
}

/** Envía el formulario del modal de canal al API */
function guardarCanal() {
  const data = {
    id:        document.getElementById('canal-id').value,
    nombre:    document.getElementById('canal-nombre').value.trim(),
    imagen:    document.getElementById('canal-imagen').value.trim(),
    categoria: document.getElementById('canal-categoria').value,
    activo:    document.getElementById('canal-activo').value,
  };

  if (!data.nombre || !data.categoria) {
    adminToast('Nombre y categoría son obligatorios.', 'error');
    return;
  }

  fetch(API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'save', entity: 'canal', data })
  })
  .then(r => r.json())
  .then(res => {
    adminToast(res.message, res.success ? 'success' : 'error');
    if (res.success) setTimeout(() => location.reload(), 800);
  })
  .catch(() => adminToast('Error de conexión', 'error'));
}

// ============================================================
// ── FUENTES ──────────────────────────────────────────────────
// ============================================================

function abrirModalFuente(data = null) {
  document.getElementById('fuente-id').value       = data?.id       ?? '';
  document.getElementById('fuente-nombre').value   = data?.nombre   ?? '';
  document.getElementById('fuente-canal').value    = data?.canal_id ?? '';
  document.getElementById('fuente-url').value      = data?.url      ?? '';
  document.getElementById('fuente-tipo').value     = data?.tipo_id  ?? '';
  document.getElementById('fuente-pais').value     = data?.pais_id  ?? '';
  document.getElementById('fuente-epg').value      = data?.epg      ?? '';
  document.getElementById('fuente-ck-key').value   = data?.ck_key   ?? '';
  document.getElementById('fuente-ck-keyid').value = data?.ck_keyid ?? '';
  document.getElementById('fuente-activo').value   = data?.activo   ?? '1';
  document.getElementById('modalFuenteTitulo').textContent = data ? 'Editar fuente' : 'Nueva fuente';

  new bootstrap.Modal(document.getElementById('modalFuente')).show();
}

function guardarFuente() {
  const data = {
    id:        document.getElementById('fuente-id').value,
    nombre:    document.getElementById('fuente-nombre').value.trim(),
    canal:     document.getElementById('fuente-canal').value,
    url:       document.getElementById('fuente-url').value.trim(),
    tipo:      document.getElementById('fuente-tipo').value,
    pais:      document.getElementById('fuente-pais').value,
    epg:       document.getElementById('fuente-epg').value.trim(),
    ck_key:    document.getElementById('fuente-ck-key').value.trim(),
    ck_keyid:  document.getElementById('fuente-ck-keyid').value.trim(),
    activo:    document.getElementById('fuente-activo').value,
  };

  if (!data.nombre || !data.canal || !data.url || !data.tipo) {
    adminToast('Nombre, canal, URL y tipo son obligatorios.', 'error');
    return;
  }

  fetch(API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'save', entity: 'fuente', data })
  })
  .then(r => r.json())
  .then(res => {
    adminToast(res.message, res.success ? 'success' : 'error');
    if (res.success) setTimeout(() => location.reload(), 800);
  })
  .catch(() => adminToast('Error de conexión', 'error'));
}

// ============================================================
// ── LIGAS ─────────────────────────────────────────────────────
// ============================================================

function abrirModalLiga(data = null) {
  document.getElementById('liga-id').value     = data?.id          ?? '';
  document.getElementById('liga-sf-id').value  = data?.id          ?? '';
  document.getElementById('liga-nombre').value = data?.ligaNombre  ?? '';
  document.getElementById('liga-pais').value   = data?.ligaPais    ?? '';
  document.getElementById('liga-tipo').value   = data?.tipo        ?? 'soccer';
  document.getElementById('modalLigaTitulo').textContent = data ? 'Editar liga' : 'Nueva liga';

  new bootstrap.Modal(document.getElementById('modalLiga')).show();
}

function guardarLiga() {
  const data = {
    id:     document.getElementById('liga-id').value,
    sf_id:  document.getElementById('liga-sf-id').value,
    nombre: document.getElementById('liga-nombre').value.trim(),
    pais:   document.getElementById('liga-pais').value.trim(),
    tipo:   document.getElementById('liga-tipo').value,
  };

  if (!data.sf_id || !data.nombre) {
    adminToast('ID y nombre son obligatorios.', 'error');
    return;
  }

  fetch(API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'save', entity: 'liga', data })
  })
  .then(r => r.json())
  .then(res => {
    adminToast(res.message, res.success ? 'success' : 'error');
    if (res.success) setTimeout(() => location.reload(), 800);
  })
  .catch(() => adminToast('Error de conexión', 'error'));
}

// ============================================================
// ── PARTIDOS ─────────────────────────────────────────────────
// ============================================================

function guardarPartido() {
  const id   = document.getElementById('partido-id').value;
  const data = { id };

  for (let i = 1; i <= 10; i++) {
    data['canal' + i] = document.getElementById('partido-canal' + i)?.value ?? '';
  }

  fetch(API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'save', entity: 'partido', data })
  })
  .then(r => r.json())
  .then(res => {
    adminToast(res.message, res.success ? 'success' : 'error');
    if (res.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalPartido'))?.hide();
    }
  })
  .catch(() => adminToast('Error de conexión', 'error'));
}
