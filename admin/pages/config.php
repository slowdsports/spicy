<?php
/**
 * Admin - Configuración del sitio
 * Muestra y permite editar los valores de la tabla config_sitio.
 */

$saved = false;
$error = '';

try {
    $conn = getDBConnection();

    // Guardar cambios si hay POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['config'])) {
        foreach ($_POST['config'] as $clave => $valor) {
            $clave = preg_replace('/[^a-z0-9_]/', '', $clave); // sanitizar clave
            $stmt  = $conn->prepare("UPDATE config_sitio SET valor = ? WHERE clave = ?");
            $stmt->bind_param('ss', $valor, $clave);
            $stmt->execute();
            $stmt->close();
        }
        $saved = true;
    }

    // Leer configuración actual
    $configs = $conn->query("SELECT clave, valor, descripcion FROM config_sitio ORDER BY clave")->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $configs = [];
    $error   = 'Error al conectar con la base de datos.';
}

// Agrupación visual de las opciones
$grupos = [
    'Sitio'       => ['sitio_nombre', 'sitio_descripcion', 'sitio_logo'],
    'Acceso'      => ['mantenimiento', 'registro_abierto'],
    'Apariencia'  => ['color_acento'],
    'Zona horaria'=> ['timezone', 'sofascore_timezone'],
    'Limits'      => ['max_fuentes_canal'],
];

// Indexar configs por clave para fácil acceso
$cfgMap = array_column($configs, null, 'clave');
?>

<?php if ($saved): ?>
  <div style="background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3); color:#22c55e; padding:0.75rem 1.25rem; border-radius:10px; font-size:0.85rem; margin-bottom:1.25rem;">
    <i class="fas fa-check me-1"></i> Configuración guardada correctamente.
  </div>
<?php endif; ?>

<?php if ($error): ?>
  <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444; padding:0.75rem 1.25rem; border-radius:10px; font-size:0.85rem; margin-bottom:1.25rem;">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<form method="POST" action="">

  <div class="row g-3">
    <?php foreach ($grupos as $grupo => $claves): ?>
    <div class="col-12 col-lg-6">
      <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:14px; overflow:hidden;">

        <!-- Cabecera del grupo -->
        <div style="background:var(--bg-secondary); border-bottom:1px solid var(--border); padding:0.85rem 1.25rem;">
          <span style="font-family:'Space Mono',monospace; font-size:0.85rem; font-weight:700; color:var(--text-primary);">
            <?= htmlspecialchars($grupo) ?>
          </span>
        </div>

        <!-- Campos -->
        <div style="padding:1.25rem; display:flex; flex-direction:column; gap:1rem;">
          <?php foreach ($claves as $clave):
            $cfg = $cfgMap[$clave] ?? ['clave' => $clave, 'valor' => '', 'descripcion' => ''];
            $val = $cfg['valor'] ?? '';
            $desc = $cfg['descripcion'] ?? '';
          ?>
          <div>
            <label style="font-size:0.75rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:4px; letter-spacing:0.3px;">
              <?= htmlspecialchars($clave) ?>
            </label>

            <?php if ($clave === 'color_acento'): ?>
              <!-- Campo de color especial -->
              <div class="d-flex gap-2 align-items-center">
                <input type="color" name="config[<?= $clave ?>]" value="<?= htmlspecialchars($val) ?>"
                       style="width:44px; height:36px; border-radius:8px; border:1px solid var(--border); cursor:pointer; background:var(--bg-input); padding:2px;">
                <input type="text" value="<?= htmlspecialchars($val) ?>" readonly
                       style="background:var(--bg-input); border:1px solid var(--border); border-radius:8px; padding:0.4rem 0.75rem; color:var(--text-muted); font-size:0.82rem; width:120px; font-family:'Space Mono',monospace;">
              </div>

            <?php elseif (in_array($clave, ['mantenimiento', 'registro_abierto'])): ?>
              <!-- Toggle booleano -->
              <select name="config[<?= $clave ?>]"
                      style="background:var(--bg-input); border:1px solid var(--border); border-radius:10px; padding:0.45rem 0.75rem; color:var(--text-primary); font-size:0.85rem; width:100%;">
                <option value="0" <?= $val == '0' ? 'selected' : '' ?>>No / Desactivado</option>
                <option value="1" <?= $val == '1' ? 'selected' : '' ?>>Sí / Activado</option>
              </select>

            <?php else: ?>
              <!-- Texto normal -->
              <input type="text" name="config[<?= $clave ?>]" value="<?= htmlspecialchars($val) ?>"
                     style="background:var(--bg-input); border:1px solid var(--border); border-radius:10px; padding:0.45rem 0.75rem; color:var(--text-primary); font-family:'DM Sans',sans-serif; font-size:0.85rem; width:100%; outline:none; transition:var(--transition);"
                     onfocus="this.style.borderColor='var(--accent)'"
                     onblur="this.style.borderColor='var(--border)'">
            <?php endif; ?>

            <?php if ($desc): ?>
              <p style="font-size:0.71rem; color:var(--text-muted); margin:4px 0 0;">
                <?= htmlspecialchars($desc) ?>
              </p>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>

      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Botón guardar -->
  <div style="margin-top:1.5rem; display:flex; justify-content:flex-end;">
    <button type="submit" class="btn-admin-add" style="padding:0.65rem 1.75rem; font-size:0.9rem;">
      <i class="fas fa-save me-2"></i> Guardar cambios
    </button>
  </div>

</form>
