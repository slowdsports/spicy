<?php
/**
 * Admin - Configuración del Chat
 * Permite alternar entre el chat propio (polling) y el chat de Twitch.
 */

$configFile = __DIR__ . '/../../data/chat-config.json';
$defaults   = ['mode' => 'custom', 'twitch_channel' => ''];
$config     = file_exists($configFile)
    ? (json_decode(file_get_contents($configFile), true) ?? $defaults)
    : $defaults;

$mode          = $config['mode']           ?? 'custom';
$twitchChannel = $config['twitch_channel'] ?? '';
?>

<div class="section-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
  <div>
    <h2 style="font-family:'Space Mono',monospace;font-size:1.1rem;font-weight:700;color:var(--text-primary);margin:0;">
      <i class="fas fa-comments me-2" style="color:var(--accent);"></i>Configuración del Chat
    </h2>
    <p style="font-size:0.8rem;color:var(--text-muted);margin:4px 0 0;">
      Activa el chat propio durante baja carga, o cambia a Twitch cuando el tráfico sea alto.
    </p>
  </div>
  <!-- Indicador de estado -->
  <div id="chat-status-badge" style="
    display:inline-flex;align-items:center;gap:6px;
    padding:5px 14px;border-radius:30px;font-size:0.75rem;font-weight:700;
    <?= $mode === 'custom'
        ? 'background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid rgba(34,197,94,0.3);'
        : 'background:rgba(145,71,255,0.15);color:#9147ff;border:1px solid rgba(145,71,255,0.3);' ?>">
    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;"></span>
    <?= $mode === 'custom' ? 'Chat propio activo' : 'Twitch chat activo' ?>
  </div>
</div>

<div style="max-width:720px;display:flex;flex-direction:column;gap:1.25rem;">

  <!-- Selector de modo -->
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
    <div style="padding:0.85rem 1.25rem;background:var(--bg-secondary);border-bottom:1px solid var(--border);">
      <span style="font-family:'Space Mono',monospace;font-size:0.82rem;font-weight:700;color:var(--text-primary);">Modo del chat</span>
    </div>
    <div style="padding:1.25rem;display:flex;flex-direction:column;gap:0.75rem;">

      <!-- Opción: Chat propio -->
      <label id="card-custom" style="
        display:flex;align-items:center;gap:1rem;padding:1rem 1.1rem;
        border-radius:10px;border:2px solid <?= $mode === 'custom' ? 'var(--accent)' : 'var(--border)' ?>;
        background:<?= $mode === 'custom' ? 'var(--accent-soft)' : 'transparent' ?>;
        cursor:pointer;transition:border-color .2s,background .2s;" onclick="selectMode('custom')">
        <input type="radio" name="chat_mode" value="custom" <?= $mode === 'custom' ? 'checked' : '' ?>
               style="accent-color:var(--accent);width:16px;height:16px;">
        <div style="flex:1;">
          <div style="font-size:0.88rem;font-weight:700;color:var(--text-primary);">
            <i class="fas fa-comments me-1" style="color:var(--accent);"></i> Chat propio
          </div>
          <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">
            Chat en vivo integrado con polling cada 2 s. Recomendado cuando el tráfico sea normal.
          </div>
        </div>
        <span style="font-size:0.7rem;padding:3px 10px;border-radius:20px;background:rgba(34,197,94,0.1);color:#22c55e;font-weight:700;white-space:nowrap;">
          Menos carga
        </span>
      </label>

      <!-- Opción: Twitch -->
      <label id="card-twitch" style="
        display:flex;align-items:center;gap:1rem;padding:1rem 1.1rem;
        border-radius:10px;border:2px solid <?= $mode === 'twitch' ? '#9147ff' : 'var(--border)' ?>;
        background:<?= $mode === 'twitch' ? 'rgba(145,71,255,0.08)' : 'transparent' ?>;
        cursor:pointer;transition:border-color .2s,background .2s;" onclick="selectMode('twitch')">
        <input type="radio" name="chat_mode" value="twitch" <?= $mode === 'twitch' ? 'checked' : '' ?>
               style="accent-color:#9147ff;width:16px;height:16px;">
        <div style="flex:1;">
          <div style="font-size:0.88rem;font-weight:700;color:var(--text-primary);">
            <i class="fab fa-twitch me-1" style="color:#9147ff;"></i> Chat de Twitch
          </div>
          <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">
            Embed del chat de Twitch. Sin polling — cero carga en el servidor. Ideal para partidos con mucho tráfico.
          </div>
        </div>
        <span style="font-size:0.7rem;padding:3px 10px;border-radius:20px;background:rgba(239,68,68,0.1);color:#ef4444;font-weight:700;white-space:nowrap;">
          Sin polling
        </span>
      </label>

    </div>
  </div>

  <!-- Canal de Twitch (solo visible si modo = twitch) -->
  <div id="twitch-config" style="
    background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;
    <?= $mode === 'twitch' ? '' : 'display:none;' ?>">
    <div style="padding:0.85rem 1.25rem;background:var(--bg-secondary);border-bottom:1px solid var(--border);">
      <span style="font-family:'Space Mono',monospace;font-size:0.82rem;font-weight:700;color:var(--text-primary);">
        <i class="fab fa-twitch me-1" style="color:#9147ff;"></i> Canal de Twitch
      </span>
    </div>
    <div style="padding:1.25rem;">
      <label style="font-size:0.75rem;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px;">
        Nombre del canal (sin @)
      </label>
      <div style="display:flex;gap:0.5rem;align-items:center;">
        <span style="font-size:0.85rem;color:var(--text-muted);white-space:nowrap;">twitch.tv/</span>
        <input type="text" id="twitch-channel-input"
               value="<?= htmlspecialchars($twitchChannel) ?>"
               placeholder="ej: espn, tnt_sports, directvsports"
               maxlength="50"
               style="flex:1;background:var(--bg-input);border:1px solid var(--border);border-radius:10px;
                      padding:0.5rem 0.85rem;color:var(--text-primary);font-size:0.85rem;outline:none;
                      font-family:'DM Sans',sans-serif;transition:border-color .2s;"
               onfocus="this.style.borderColor='#9147ff'"
               onblur="this.style.borderColor='var(--border)'">
      </div>
      <p style="font-size:0.71rem;color:var(--text-muted);margin:6px 0 0;">
        El chat de Twitch se mostrará embebido en el reproductor. El canal debe tener el chat habilitado para embeds.
      </p>
    </div>
  </div>

  <!-- Botón guardar -->
  <div style="display:flex;justify-content:flex-end;">
    <button id="btn-save-chat" onclick="saveChatConfig()"
            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.75rem;
                   border-radius:10px;background:var(--accent);color:#fff;border:none;
                   font-size:0.88rem;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;
                   transition:background .2s;"
            onmouseover="this.style.background='var(--accent-hover)'"
            onmouseout="this.style.background='var(--accent)'">
      <i class="fas fa-save"></i> Guardar configuración
    </button>
  </div>

</div>

<script>
var _chatMode = <?= json_encode($mode) ?>;

function selectMode(mode) {
  _chatMode = mode;

  // Actualizar radios
  document.querySelectorAll('input[name="chat_mode"]').forEach(function(r) {
    r.checked = (r.value === mode);
  });

  // Estilos tarjeta custom
  var cardC = document.getElementById('card-custom');
  cardC.style.borderColor  = mode === 'custom' ? 'var(--accent)' : 'var(--border)';
  cardC.style.background   = mode === 'custom' ? 'var(--accent-soft)' : 'transparent';

  // Estilos tarjeta twitch
  var cardT = document.getElementById('card-twitch');
  cardT.style.borderColor  = mode === 'twitch' ? '#9147ff' : 'var(--border)';
  cardT.style.background   = mode === 'twitch' ? 'rgba(145,71,255,0.08)' : 'transparent';

  // Mostrar/ocultar config de Twitch
  document.getElementById('twitch-config').style.display = mode === 'twitch' ? '' : 'none';
}

function saveChatConfig() {
  var btn = document.getElementById('btn-save-chat');
  var mode    = _chatMode;
  var channel = (document.getElementById('twitch-channel-input') || {}).value || '';

  if (mode === 'twitch' && !channel.trim()) {
    adminToast('Ingresa el nombre del canal de Twitch', 'error');
    document.getElementById('twitch-channel-input').focus();
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

  fetch('../admin/api/chat_config.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ mode: mode, twitch_channel: channel.trim() })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.ok) {
      // Actualizar badge de estado
      var badge = document.getElementById('chat-status-badge');
      if (mode === 'custom') {
        badge.style.cssText = 'display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:30px;font-size:0.75rem;font-weight:700;background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid rgba(34,197,94,0.3);';
        badge.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:currentColor;"></span> Chat propio activo';
      } else {
        badge.style.cssText = 'display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:30px;font-size:0.75rem;font-weight:700;background:rgba(145,71,255,0.15);color:#9147ff;border:1px solid rgba(145,71,255,0.3);';
        badge.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:currentColor;"></span> Twitch chat activo';
      }
      adminToast(data.msg || 'Guardado', 'success');
    } else {
      adminToast(data.msg || 'Error al guardar', 'error');
    }
  })
  .catch(function() { adminToast('Error de conexión', 'error'); })
  .finally(function() {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Guardar configuración';
  });
}
</script>
