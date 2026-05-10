/**
 * StreamHub Admin - JavaScript principal
 *
 * Funciones compartidas por todas las páginas del panel:
 *  - Abrir/llenar modales de cada entidad
 *  - Guardar cambios via fetch a admin/api/crud.php
 *  - Confirmar y ejecutar borrado
 *  - Toast de notificaciones
 */

const API      = '../admin/api/crud.php';         // ruta relativa desde admin/
const API_JSON = '../admin/api/generate_json.php'; // generación manual de JSON

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
// GENERAR JSON MANUALMENTE
// ============================================================
function generarJSON(entity) {
  const btnId = 'btn-json-' + entity;
  const btn   = document.getElementById(btnId);
  const label = { canales: 'channels.json', fuentes: 'fuentes.json', partidos: 'matches.json' };

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
  }

  fetch(API_JSON, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ entity }),
  })
  .then(r => r.json())
  .then(res => {
    adminToast(res.message, res.success ? 'success' : 'error');
  })
  .catch(() => adminToast('Error al generar ' + (label[entity] ?? 'JSON'), 'error'))
  .finally(() => {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-file-export"></i> Actualizar JSON';
    }
  });
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
  tsSet('canal-categoria', data?.categoria_id ?? '');
  tsSet('canal-activo',    data?.activo      ?? '1');
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
  tsSet('fuente-canal',      data?.canal_id  ?? '');
  document.getElementById('fuente-url').value      = data?.url      ?? '';
  tsSet('fuente-tipo',       data?.tipo_id   ?? '');
  tsSet('fuente-pais',       data?.pais_id   ?? '');
  document.getElementById('fuente-epg').value      = data?.epg      ?? '';
  document.getElementById('fuente-ck-key').value   = data?.ck_key   ?? '';
  document.getElementById('fuente-ck-keyid').value = data?.ck_keyid ?? '';
  tsSet('fuente-activo',     data?.activo     ?? '1');
  tsSet('fuente-mostrar-tv', data?.mostrar_tv ?? '1');
  document.getElementById('fuente-sandbox').checked = (data?.sandbox ?? 1) == 1;

  const repEl = document.getElementById('fuente-reproductor');
  if (repEl) repEl.value = data?.reproductor ?? 'bitmovin';

  document.getElementById('modalFuenteTitulo').textContent = data ? 'Editar fuente' : 'Nueva fuente';

  // Mostrar/ocultar campo reproductor según el tipo seleccionado
  actualizarCampoReproductor();

  new bootstrap.Modal(document.getElementById('modalFuente')).show();
}

function guardarFuente() {
  const data = {
    id:          document.getElementById('fuente-id').value,
    nombre:      document.getElementById('fuente-nombre').value.trim(),
    canal:       document.getElementById('fuente-canal').value,
    url:         document.getElementById('fuente-url').value.trim(),
    tipo:        document.getElementById('fuente-tipo').value,
    pais:        document.getElementById('fuente-pais').value,
    epg:         document.getElementById('fuente-epg').value.trim(),
    ck_key:      document.getElementById('fuente-ck-key').value.trim(),
    ck_keyid:    document.getElementById('fuente-ck-keyid').value.trim(),
    activo:      document.getElementById('fuente-activo').value,
    mostrar_tv:  document.getElementById('fuente-mostrar-tv').value,
    sandbox:     document.getElementById('fuente-sandbox').checked ? 1 : 0,
    reproductor: document.getElementById('fuente-reproductor')?.value || 'bitmovin',
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
  tsSet('liga-tipo', data?.tipo ?? 'soccer');
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

// ── Mostrar/ocultar el campo Reproductor según tipo de fuente ─
// Solo tiene sentido para tipos DASH (nombre contiene "dash").
function actualizarCampoReproductor() {
  const tipoEl = document.getElementById('fuente-tipo');
  const tipoText = (tipoEl?.options[tipoEl.selectedIndex]?.text ?? '').toLowerCase();
  const isDash = tipoText.includes('dash');
  const campo = document.getElementById('reproductor-field');
  if (campo) campo.style.display = isDash ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
  const tipoEl = document.getElementById('fuente-tipo');
  if (tipoEl) tipoEl.addEventListener('change', actualizarCampoReproductor);
});

// ============================================================
// ── TOM SELECT — select con búsqueda ─────────────────────────
// ============================================================

const _ts = {};

/**
 * Establece el valor de un select gestionado por Tom Select.
 * Si el instancia no existe aún, usa .value como fallback.
 */
function tsSet(id, val) {
  const v = val != null ? String(val) : '';
  if (_ts[id]) {
    _ts[id].setValue(v, true); // silent=true: no dispara change
  } else {
    const el = document.getElementById(id);
    if (el) el.value = v;
  }
}

/** Inicializa Tom Select en todos los <select> del DOM que tengan id,
 *  excepto los generados por DataTables (terminan en _length). */
function initAdminSelects() {
  document.querySelectorAll('select[id]').forEach(el => {
    if (_ts[el.id]) return;
    if (el.id.endsWith('_length')) return; // select de cantidad de filas de DataTables
    _ts[el.id] = new TomSelect(el, {
      allowEmptyOption: true,
      maxOptions: 500,
      dropdownParent: 'body', // evita recortes por overflow en modales Bootstrap
    });
  });
}

document.addEventListener('DOMContentLoaded', initAdminSelects);

// ============================================================
// ── DATATABLES — tablas con búsqueda, orden y paginación ─────
// ============================================================

const DT_LANG = {
  search:       'Buscar:',
  lengthMenu:   'Mostrar _MENU_',
  info:         '_START_–_END_ de _TOTAL_',
  infoEmpty:    'Sin resultados',
  infoFiltered: '(filtrado de _MAX_)',
  emptyTable:   'No hay datos disponibles',
  zeroRecords:  'Sin coincidencias',
  paginate:     { previous: '‹', next: '›' },
};

const DT_TABLES = [
  'tabla-canales',
  'tabla-fuentes',
  'tabla-ligas',
  'tabla-reportes',
  'tabla-partidos',
  'tabla-usuarios',
  'tabla-donaciones',
  'tabla-autodesactivadas',
];

function initAdminTables() {
  DT_TABLES.forEach(function (tableId) {
    var el = document.getElementById(tableId);
    if (!el) return;
    if ($.fn.DataTable.isDataTable(el)) return; // ya inicializada
    try {
      $(el).DataTable({
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: -1 }],
        language:   DT_LANG,
      });
    } catch (e) {
      console.warn('[DataTable]', tableId, e);
    }
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAdminTables);
} else {
  initAdminTables();
}
