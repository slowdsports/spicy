<?php
/**
 * Admin — Partidos Destacados
 * Gestiona qué partidos aparecen en el banner de la home.
 */

// Crear tabla si no existe (seguro para hosting compartido)
try {
    $conn = getDBConnection();
    $conn->query("
        CREATE TABLE IF NOT EXISTS partidos_destacados (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            partido_id  INT NOT NULL,
            posicion    TINYINT UNSIGNED NOT NULL DEFAULT 0,
            activo      TINYINT(1) NOT NULL DEFAULT 1,
            creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_partido (partido_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Exception $e) { /* silencioso — la tabla puede ya existir */ }
?>

<div class="admin-section-header">
  <div class="admin-section-title">Partidos Destacados</div>
  <div class="d-flex gap-2 align-items-center">
    <button class="btn-sofa" onclick="abrirModalAgregar()">
      <i class="fas fa-plus"></i> Agregar partido
    </button>
  </div>
</div>

<p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:1.25rem;">
  Los partidos activos aparecen en el banner de la página principal ordenados por posición (menor = primero).
</p>

<!-- Tabla de destacados -->
<div class="admin-table-wrapper">
  <table class="admin-table" id="tabla-destacados">
    <thead>
      <tr>
        <th style="width:50px;">Pos.</th>
        <th>Partido</th>
        <th>Liga</th>
        <th>Fecha</th>
        <th style="width:90px;">Estado</th>
        <th style="width:100px;">Acciones</th>
      </tr>
    </thead>
    <tbody id="tbody-destacados">
      <tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:2rem;">
        <i class="fas fa-spinner fa-spin"></i> Cargando...
      </td></tr>
    </tbody>
  </table>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODAL: Agregar partido destacado
════════════════════════════════════════════════════════ -->
<div class="modal fade admin-modal" id="modalAgregar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agregar partido destacado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <input type="text" id="buscar-partido" class="form-control"
                 placeholder="Buscar por equipo o liga..."
                 style="background:var(--bg-input); border:1px solid var(--border); color:var(--text-primary); border-radius:10px;">
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size:0.8rem;">Posición en el banner</label>
          <input type="number" id="nueva-posicion" class="form-control" value="0" min="0" max="99"
                 style="background:var(--bg-input); border:1px solid var(--border); color:var(--text-primary); border-radius:10px; width:120px;">
        </div>
        <div id="lista-partidos" style="max-height:340px; overflow-y:auto; display:flex; flex-direction:column; gap:6px;">
          <p style="color:var(--text-muted); text-align:center; padding:1rem;">
            <i class="fas fa-spinner fa-spin"></i> Cargando partidos...
          </p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-interact" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const API = '<?= BASE_URL ?>admin/api/crud.php';
  let allPartidos = [];
  let selectedPartidoId = null;

  /* ─── Cargar tabla de destacados ─── */
  function cargarDestacados() {
    fetch(API + '?action=get_destacados')
      .then(r => r.json())
      .then(res => {
        const tbody = document.getElementById('tbody-destacados');
        if (!res.success || !res.data.length) {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:2rem;">No hay partidos destacados. Agrega uno con el botón de arriba.</td></tr>';
          return;
        }
        tbody.innerHTML = res.data.map(d => `
          <tr id="dest-row-${d.id}">
            <td style="font-family:'Space Mono',monospace; font-size:0.78rem; color:var(--text-muted);">${d.posicion}</td>
            <td style="font-weight:600;">${esc(d.homeTeam)} <span style="color:var(--text-muted); font-weight:400;">vs</span> ${esc(d.awayTeam)}</td>
            <td style="font-size:0.82rem; color:var(--text-muted);">${esc(d.leagueName)}</td>
            <td style="font-size:0.78rem; color:var(--text-muted);">${d.fecha_hora ? d.fecha_hora.substring(0,16) : '—'}</td>
            <td>
              <span class="badge-${d.activo ? 'activo' : 'inactivo'}" id="badge-${d.id}" style="cursor:pointer;"
                    onclick="toggleDestacado(${d.id})">
                ${d.activo ? 'Activo' : 'Inactivo'}
              </span>
            </td>
            <td>
              <button class="btn-admin-delete" title="Eliminar"
                      onclick="eliminarDestacado(${d.id}, '${esc(d.homeTeam)} vs ${esc(d.awayTeam)}')">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        `).join('');
      })
      .catch(() => adminToast('Error al cargar destacados', 'error'));
  }

  /* ─── Abrir modal de agregar ─── */
  window.abrirModalAgregar = function () {
    selectedPartidoId = null;
    document.getElementById('buscar-partido').value = '';
    const modal = new bootstrap.Modal(document.getElementById('modalAgregar'));
    modal.show();

    if (allPartidos.length === 0) {
      fetch(API + '?action=get_partidos_select')
        .then(r => r.json())
        .then(res => {
          allPartidos = res.data || [];
          renderListaPartidos(allPartidos);
        });
    } else {
      renderListaPartidos(allPartidos);
    }
  };

  function renderListaPartidos(lista) {
    const container = document.getElementById('lista-partidos');
    if (!lista.length) {
      container.innerHTML = '<p style="color:var(--text-muted); text-align:center; padding:1rem;">No hay partidos en matches.json</p>';
      return;
    }
    container.innerHTML = lista.map(p => `
      <div class="partido-opcion" data-id="${p.id}"
           onclick="seleccionarPartido(${p.id}, this)"
           style="padding:0.6rem 0.9rem; border-radius:10px; border:1px solid var(--border);
                  cursor:pointer; transition:0.15s; background:var(--bg-card);">
        <div style="font-weight:600; font-size:0.88rem;">${esc(p.homeTeam)} vs ${esc(p.awayTeam)}</div>
        <div style="font-size:0.75rem; color:var(--text-muted);">
          ${esc(p.leagueName)} &nbsp;·&nbsp; ${p.fecha_hora ? p.fecha_hora.substring(0,16) : 'Sin fecha'}
          &nbsp;·&nbsp; <span style="text-transform:capitalize;">${p.tipo}</span>
        </div>
      </div>
    `).join('');
  }

  window.seleccionarPartido = function (id, el) {
    document.querySelectorAll('.partido-opcion').forEach(e => {
      e.style.background = 'var(--bg-card)';
      e.style.borderColor = 'var(--border)';
      e.style.color = '';
    });
    el.style.background = 'var(--accent-soft)';
    el.style.borderColor = 'var(--accent)';
    el.style.color = 'var(--accent)';
    selectedPartidoId = id;
    guardarDestacadoSeleccionado();
  };

  function guardarDestacadoSeleccionado() {
    if (!selectedPartidoId) return;
    const posicion = parseInt(document.getElementById('nueva-posicion').value, 10) || 0;

    fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'save', entity: 'destacado', data: { partido_id: selectedPartidoId, posicion } })
    })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          bootstrap.Modal.getInstance(document.getElementById('modalAgregar'))?.hide();
          adminToast('Partido destacado agregado', 'success');
          cargarDestacados();
        } else {
          adminToast(res.message || 'Error al agregar', 'error');
        }
      })
      .catch(() => adminToast('Error de red', 'error'));
  }

  /* ─── Toggle activo ─── */
  window.toggleDestacado = function (id) {
    fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'toggle', entity: 'destacado', id })
    })
      .then(r => r.json())
      .then(res => {
        if (res.success) cargarDestacados();
        else adminToast(res.message || 'Error', 'error');
      });
  };

  /* ─── Eliminar ─── */
  window.eliminarDestacado = function (id, nombre) {
    if (!confirm('¿Quitar "' + nombre + '" de los destacados?')) return;
    fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', entity: 'destacado', id })
    })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          adminToast('Partido eliminado de destacados', 'success');
          cargarDestacados();
        } else {
          adminToast(res.message || 'Error', 'error');
        }
      });
  };

  /* ─── Buscador en modal ─── */
  document.getElementById('buscar-partido').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    const filtrados = q
      ? allPartidos.filter(p =>
          (p.homeTeam + p.awayTeam + p.leagueName).toLowerCase().includes(q))
      : allPartidos;
    renderListaPartidos(filtrados);
  });

  /* ─── Utilidad ─── */
  function esc(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  cargarDestacados();
})();
</script>
