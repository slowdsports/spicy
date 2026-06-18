<?php
/**
 * Admin - Fuentes
 * Lista todas las fuentes con su canal asociado. CRUD con modal.
 */

// Migraciones: usa SHOW COLUMNS para compatibilidad total con MySQL 5.7 / 8.x.
// query() nunca lanza excepciones, así que no hay try/catch aquí.
$_conn_mig = getDBConnection();
$_migcols = [
    'sandbox'     => "ALTER TABLE fuentes ADD COLUMN sandbox     TINYINT(1)  NOT NULL DEFAULT 1",
    'mostrar_tv'  => "ALTER TABLE fuentes ADD COLUMN mostrar_tv  TINYINT(1)  NOT NULL DEFAULT 1",
    'reproductor' => "ALTER TABLE fuentes ADD COLUMN reproductor VARCHAR(20) NOT NULL DEFAULT 'bitmovin'",
    'url_ios'     => "ALTER TABLE fuentes ADD COLUMN url_ios     VARCHAR(2000) DEFAULT NULL",
    'tipo_ios'    => "ALTER TABLE fuentes ADD COLUMN tipo_ios    VARCHAR(20) NOT NULL DEFAULT 'hls'",
    'usar_proxy'  => "ALTER TABLE fuentes ADD COLUMN usar_proxy  TINYINT(1)  NOT NULL DEFAULT 0",
];
foreach ($_migcols as $_col => $_sql) {
    $_r = $_conn_mig->query("SHOW COLUMNS FROM fuentes LIKE '{$_col}'");
    if ($_r && $_r->num_rows === 0) $_conn_mig->query($_sql);
}
unset($_conn_mig, $_migcols, $_col, $_sql, $_r);

// Datos para selects del modal (se cargan independientemente)
try {
    $conn    = getDBConnection();
    $canales = $conn->query("SELECT id, nombre FROM canales WHERE activo=1 ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
    $paises  = $conn->query("SELECT id, paisNombre FROM paises ORDER BY paisNombre ASC")->fetch_all(MYSQLI_ASSOC);
    $tipos   = $conn->query("SELECT id, nombre FROM tipos_fuente ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $canales = $paises = $tipos = [];
}

// Lista de fuentes (puede fallar si faltan columnas en la DB)
try {
    $conn    = getDBConnection();
    $fuentes = $conn->query("
        SELECT f.id, f.nombre, f.url, f.url_ios, f.tipo_ios, f.ck_key, f.ck_keyid, f.epg, f.activo, f.sandbox, f.mostrar_tv, f.reproductor, f.usar_proxy,
               c.nombre  AS canal_nombre,  f.canal  AS canal_id,
               p.paisNombre AS pais_nombre, f.pais  AS pais_id,
               t.nombre  AS tipo_nombre,   f.tipo   AS tipo_id
        FROM fuentes f
        LEFT JOIN canales     c ON f.canal = c.id
        LEFT JOIN paises      p ON f.pais  = p.id
        LEFT JOIN tipos_fuente t ON f.tipo  = t.id
        ORDER BY c.nombre ASC, f.nombre ASC
    ")->fetch_all(MYSQLI_ASSOC);

} catch (Throwable $e) {
    $fuentes = [];
}
?>

<div class="admin-section-header">
  <div class="admin-section-title">Fuentes de transmisión</div>
  <div class="d-flex gap-2 align-items-center flex-wrap">
    <button class="btn-interact" onclick="generarJSON('fuentes')" id="btn-json-fuentes">
      <i class="fas fa-file-export"></i> Actualizar JSON
    </button>
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
        <th style="width:70px;">Sandbox</th>
        <th style="width:65px;">En TV</th>
        <th style="width:80px;">Estado</th>
        <th style="width:80px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($fuentes)): ?>
        <tr><td colspan="10">
          <div class="admin-empty"><i class="fas fa-broadcast-tower"></i><p>No hay fuentes registradas.</p></div>
        </td></tr>
      <?php else: ?>
        <?php foreach ($fuentes as $f): ?>
        <tr>
          <td style="color:var(--text-muted); font-size:0.75rem;"><?= $f['id'] ?></td>
          <td style="font-weight:600; color:var(--text-primary); max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
            <?= htmlspecialchars($f['nombre']) ?>
          </td>
          <td>
            <span style="font-size:0.78rem; background:var(--accent-soft); color:var(--accent); padding:2px 8px; border-radius:100px;">
              <?= htmlspecialchars($f['canal_nombre'] ?? '—') ?>
            </span>
          </td>
          <td style="font-size:0.78rem;">
            <?= htmlspecialchars($f['tipo_nombre'] ?? '—') ?>
            <?php if (($f['reproductor'] ?? 'bitmovin') !== 'bitmovin'): ?>
              <span style="margin-left:4px; font-size:0.65rem; background:rgba(99,102,241,0.12); color:#818cf8; border:1px solid rgba(99,102,241,0.3); padding:1px 5px; border-radius:4px; font-weight:700; vertical-align:middle;">
                <?= strtoupper(htmlspecialchars($f['reproductor'])) ?>
              </span>
            <?php endif; ?>
          </td>
          <td style="font-size:0.78rem; color:var(--text-muted);"><?= htmlspecialchars($f['pais_nombre'] ?? '—') ?></td>
          <!-- Indicadores: DRM / iOS / Proxy -->
          <td>
            <?php if ($f['ck_key']): ?>
              <span style="font-size:0.68rem; background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.3); padding:1px 6px; border-radius:4px; font-weight:700;">
                <i class="fas fa-lock me-1"></i>DRM
              </span>
            <?php endif; ?>
            <?php if (!empty($f['url_ios'])): ?>
              <span style="font-size:0.68rem; background:rgba(34,197,94,0.12); color:#22c55e; border:1px solid rgba(34,197,94,0.3); padding:1px 6px; border-radius:4px; font-weight:700; margin-left:2px;">
                <i class="fab fa-apple me-1"></i>iOS
              </span>
            <?php endif; ?>
            <?php if (!empty($f['usar_proxy'])): ?>
              <span style="font-size:0.68rem; background:rgba(139,92,246,0.12); color:#a78bfa; border:1px solid rgba(139,92,246,0.3); padding:1px 6px; border-radius:4px; font-weight:700; margin-left:2px;">
                <i class="fas fa-shield-alt me-1"></i>PROXY
              </span>
            <?php endif; ?>
            <?php if (!$f['ck_key'] && empty($f['url_ios']) && empty($f['usar_proxy'])): ?>
              <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
            <?php endif; ?>
          </td>
          <!-- Indicador Sandbox -->
          <td>
            <?php if (($f['sandbox'] ?? 1) == 0): ?>
              <span style="font-size:0.68rem; background:rgba(234,179,8,0.12); color:#ca8a04; border:1px solid rgba(234,179,8,0.3); padding:1px 6px; border-radius:4px; font-weight:700;">
                Sin SB
              </span>
            <?php else: ?>
              <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
            <?php endif; ?>
          </td>
          <!-- Indicador En TV -->
          <td>
            <?php if (($f['mostrar_tv'] ?? 1) == 0): ?>
              <span style="font-size:0.68rem; background:rgba(139,92,246,0.12); color:var(--accent); border:1px solid rgba(139,92,246,0.3); padding:1px 6px; border-radius:4px; font-weight:700;">
                Oculta
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

          <!-- URL alternativa iOS -->
          <div class="col-12">
            <div style="background:rgba(34,197,94,0.06); border:1px solid rgba(34,197,94,0.2); border-radius:10px; padding:1rem;">
              <p style="font-size:0.78rem; font-weight:700; color:#22c55e; margin:0 0 0.75rem;">
                <i class="fab fa-apple me-1"></i> Fuente alternativa para iOS
                <span style="font-weight:400; color:var(--text-muted);">(opcional)</span>
              </p>
              <div class="row g-2">
                <div class="col-12 col-md-8">
                  <label class="form-label" style="font-size:0.78rem; color:var(--text-muted);">URL de la fuente iOS</label>
                  <input type="text" id="fuente-url-ios" class="form-control" placeholder="https://... ó https://embed.ejemplo.com/canal">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" style="font-size:0.78rem; color:var(--text-muted);">Tipo de fuente</label>
                  <select id="fuente-tipo-ios" class="form-select">
                    <option value="hls">HLS / M3U8</option>
                    <option value="iframe">iFrame</option>
                  </select>
                </div>
              </div>
              <p style="font-size:0.72rem; color:var(--text-muted); margin:0.6rem 0 0; line-height:1.5;">
                Se carga automáticamente en iPhone, iPad o iPod. <strong style="color:var(--text-secondary);">HLS/M3U8</strong> usa Clappr; <strong style="color:var(--text-secondary);">iFrame</strong> incrusta la URL directamente.
              </p>
            </div>
          </div>

          <!-- Tipo -->
          <div class="col-12 col-md-4">
            <label class="form-label">Tipo <span style="color:#ef4444;">*</span></label>
            <select id="fuente-tipo" class="form-select">
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

          <!-- Campos DRM -->
          <div class="col-12" id="drm-fields">
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

          <!-- Reproductor (visible solo para tipos DASH) -->
          <div class="col-12" id="reproductor-field" style="display:none;">
            <div style="background:rgba(99,102,241,0.06); border:1px solid rgba(99,102,241,0.2); border-radius:10px; padding:1rem;">
              <p style="font-size:0.78rem; font-weight:700; color:#818cf8; margin:0 0 0.75rem;">
                <i class="fas fa-play-circle me-1"></i> Reproductor de video
              </p>
              <select id="fuente-reproductor" class="form-select">
                <option value="bitmovin">Bitmovin (por defecto)</option>
                <option value="clappr">Clappr</option>
                <option value="jwplayer">JW Player</option>
              </select>
              <p style="font-size:0.72rem; color:var(--text-muted); margin:0.5rem 0 0;">
                Elige el reproductor según la compatibilidad del stream. Bitmovin es el predeterminado para DASH.
              </p>
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

          <!-- Visible en TV/Home -->
          <div class="col-12 col-md-4">
            <label class="form-label">Visible en TV / Home</label>
            <select id="fuente-mostrar-tv" class="form-select">
              <option value="1">Sí — aparece en listados</option>
              <option value="0">No — solo para partidos</option>
            </select>
          </div>

          <!-- Sandbox (solo aplica en tipo iframe) -->
          <div class="col-12 col-md-4">
            <label class="form-label">Sandbox</label>
            <div style="display:flex; align-items:center; gap:10px; padding:8px 12px; background:rgba(234,179,8,0.06); border:1px solid rgba(234,179,8,0.2); border-radius:8px;">
              <input type="checkbox" id="fuente-sandbox" style="width:16px; height:16px; accent-color:#ca8a04; cursor:pointer;" checked>
              <label for="fuente-sandbox" style="margin:0; cursor:pointer; font-size:0.85rem; color:var(--text-primary);">
                Usar atributo <code>sandbox</code> en el iframe
              </label>
            </div>
          </div>

          <!-- Proxy geo-protección -->
          <div class="col-12">
            <div style="background:rgba(139,92,246,0.06); border:1px solid rgba(139,92,246,0.2); border-radius:10px; padding:1rem;">
              <p style="font-size:0.78rem; font-weight:700; color:#a78bfa; margin:0 0 0.6rem;">
                <i class="fas fa-shield-alt me-1"></i> Geo-protección via proxy
              </p>
              <div style="display:flex; align-items:center; gap:10px;">
                <input type="checkbox" id="fuente-usar-proxy" style="width:16px; height:16px; accent-color:#8b5cf6; cursor:pointer;">
                <label for="fuente-usar-proxy" style="margin:0; cursor:pointer; font-size:0.85rem; color:var(--text-primary);">
                  Enrutar por proxy aleatorio (solo usuarios Spicy / Admin)
                </label>
              </div>
              <p style="font-size:0.72rem; color:var(--text-muted); margin:0.5rem 0 0; line-height:1.5;">
                Si está activo, la URL se enruta a través de un proxy de los configurados en la sección <strong style="color:var(--text-secondary);">Proxies</strong>. Para el resto de usuarios la URL se sirve sin proxy.
              </p>
            </div>
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

