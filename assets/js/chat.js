/**
 * Chat en vivo — StreamHub
 * Polling cada 2 s (compatible con hosting compartido que no soporta SSE).
 * Completamente independiente del resto del JS del sitio.
 */
(function () {
  'use strict';

  /* ═══════════════════════════════════════════════════════════
     CONFIG — lee variables inyectadas por PHP en canal.php
  ═══════════════════════════════════════════════════════════ */
  var BASE      = (typeof BASE_URL      !== 'undefined') ? BASE_URL      : '/spicy/';
  var CANAL_ID  = parseInt((typeof CANAL !== 'undefined') ? CANAL        : 0, 10) || 0;
  var LOGGED_IN = (typeof IS_LOGGED_IN  !== 'undefined') ? IS_LOGGED_IN  : false;
  var USER_ROL  = (typeof CHAT_USER_ROL !== 'undefined') ? CHAT_USER_ROL : 'usuario';
  var MAX_MSGS  = 150;   // mensajes máximos en el DOM
  var RATE_MS   = 800;   // ms mínimo entre envíos (lado cliente)
  var POLL_MS   = 2000;  // intervalo de polling

  /* ═══════════════════════════════════════════════════════════
     ESTADO
  ═══════════════════════════════════════════════════════════ */
  var lastId       = -1;   // -1 = primera llamada (cargar historial)
  var lastSentAt   = 0;
  var pollTimer    = null;
  var polling      = false;
  var pendingCount = 0;

  /* ═══════════════════════════════════════════════════════════
     DOM REFS
  ═══════════════════════════════════════════════════════════ */
  var $msgs, $input, $sendBtn, $usersEl, $scrollBtn, $charCount;

  /* ═══════════════════════════════════════════════════════════
     INICIALIZACIÓN
  ═══════════════════════════════════════════════════════════ */
  function init() {
    if (!CANAL_ID) return;

    $msgs      = document.getElementById('chat-messages');
    $input     = document.getElementById('chat-input-field');
    $sendBtn   = document.getElementById('chat-send-btn');
    $usersEl   = document.getElementById('chat-users');
    $scrollBtn = document.getElementById('chat-scroll-btn');
    $charCount = document.getElementById('chat-char-count');

    if (!$msgs) return;

    // Neutralizar chat demo de channel.js
    window.startDemoChat  = function () {};
    window.addChatMessage = function () {};
    $msgs.innerHTML = '';

    setupScroll();
    setupInput();
    startPolling();

    // Pausar polling cuando la pestaña está en segundo plano
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        stopPolling();
      } else {
        startPolling();
      }
    });
  }

  /* ═══════════════════════════════════════════════════════════
     POLLING
  ═══════════════════════════════════════════════════════════ */
  function startPolling() {
    if (polling) return;
    polling = true;
    doPoll(); // primera llamada inmediata
  }

  function stopPolling() {
    polling = false;
    clearTimeout(pollTimer);
  }

  function doPoll() {
    if (!polling) return;

    var url = BASE + 'chat/poll.php?canal=' + CANAL_ID + '&last_id=' + lastId;

    fetch(url, { method: 'GET', cache: 'no-store' })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (data) {
        if (!data.ok) return;

        // Actualizar contador de usuarios
        if (typeof data.users === 'number') setUserCount(data.users);

        // Renderizar mensajes nuevos
        if (Array.isArray(data.messages) && data.messages.length > 0) {
          data.messages.forEach(function (msg) { appendMessage(msg); });
        }
      })
      .catch(function () { /* error de red — simplemente reintentar */ })
      .finally(function () {
        if (polling) {
          pollTimer = setTimeout(doPoll, POLL_MS);
        }
      });
  }

  /* ═══════════════════════════════════════════════════════════
     INPUT Y ENVÍO
  ═══════════════════════════════════════════════════════════ */
  function setupInput() {
    if (!LOGGED_IN || !$input || !$sendBtn) return;

    $input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        doSend();
      }
    });

    $input.addEventListener('input', onInputChange);
    $sendBtn.addEventListener('click', doSend);
  }

  function onInputChange() {
    if (!$charCount || !$input) return;
    var len = $input.value.length;
    $charCount.textContent = len + '/500';
    $charCount.className = 'chat-char-count' +
      (len >= 500 ? ' limit' : len >= 400 ? ' warn' : '');
  }

  function doSend() {
    if (!$input || !$sendBtn) return;
    var text = $input.value.trim();
    if (!text) return;

    var now = Date.now();
    if (now - lastSentAt < RATE_MS) return;
    lastSentAt = now;

    $input.value = '';
    if ($charCount) { $charCount.textContent = '0/500'; $charCount.className = 'chat-char-count'; }
    $sendBtn.disabled = true;

    var fd = new FormData();
    fd.append('canal', CANAL_ID);
    fd.append('msg',   text);

    fetch(BASE + 'chat/send.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        // Adelantar el próximo poll para que el mensaje propio aparezca al instante
        if (data.ok) {
          clearTimeout(pollTimer);
          pollTimer = setTimeout(doPoll, 150);
        }
      })
      .catch(function () {})
      .finally(function () {
        setTimeout(function () { if ($sendBtn) $sendBtn.disabled = false; }, 500);
      });
  }

  /* ═══════════════════════════════════════════════════════════
     RENDERIZADO DE MENSAJES
  ═══════════════════════════════════════════════════════════ */
  function appendMessage(msg) {
    if (!$msgs) return;

    var id = parseInt(msg.id, 10) || 0;

    // Evitar duplicados
    if (id > 0) {
      if (id <= lastId && lastId >= 0) return;
      lastId = Math.max(lastId, id);
    }

    var atBottom = isAtBottom();

    var el = document.createElement('div');
    el.className = 'chat-message';
    if (id) el.dataset.id = id;

    var rol  = msg.user_rol  || 'usuario';
    var name = esc(msg.user_name || 'Anon');
    var text = esc(msg.message   || '');

    var badge = '';
    if (rol === 'admin') {
      badge = '<span class="chat-badge chat-badge-admin">Admin</span>';
    } else if (rol === 'spicy') {
      badge = '<span class="chat-badge chat-badge-spicy">✦ Spicy</span>';
    }

    var userCls = 'chat-user';
    if      (rol === 'admin')  userCls += ' chat-user-admin';
    else if (rol === 'spicy')  userCls += ' chat-user-spicy';
    else                       userCls += ' chat-user-normal';

    var nameStyle = '';
    if (rol === 'usuario') {
      nameStyle = ' style="color:' + nameToColor(msg.user_name || 'x') + '"';
    }

    el.innerHTML =
      badge +
      '<span class="' + userCls + '"' + nameStyle + '>' + name + '</span>' +
      '<span class="chat-separator">:</span> ' +
      '<span class="chat-text">' + text + '</span>';

    $msgs.appendChild(el);

    while ($msgs.children.length > MAX_MSGS) {
      $msgs.removeChild($msgs.firstChild);
    }

    if (atBottom) {
      scrollToBottom();
      pendingCount = 0;
      hideScrollBtn();
    } else {
      pendingCount++;
      showScrollBtn(pendingCount);
    }
  }

  /* ═══════════════════════════════════════════════════════════
     SCROLL
  ═══════════════════════════════════════════════════════════ */
  function setupScroll() {
    $msgs.addEventListener('scroll', function () {
      if (isAtBottom()) {
        pendingCount = 0;
        hideScrollBtn();
      }
    });

    if ($scrollBtn) {
      $scrollBtn.addEventListener('click', function () {
        scrollToBottom();
        pendingCount = 0;
        hideScrollBtn();
      });
    }
  }

  function isAtBottom() {
    return $msgs.scrollHeight - $msgs.scrollTop <= $msgs.clientHeight + 80;
  }

  function scrollToBottom() {
    $msgs.scrollTop = $msgs.scrollHeight;
  }

  function showScrollBtn(count) {
    if (!$scrollBtn) return;
    var label = count > 1 ? count + ' mensajes nuevos' : 'Mensaje nuevo';
    $scrollBtn.innerHTML = '<i class="fas fa-arrow-down"></i> ' + label;
    $scrollBtn.classList.add('visible');
  }

  function hideScrollBtn() {
    if ($scrollBtn) $scrollBtn.classList.remove('visible');
  }

  /* ═══════════════════════════════════════════════════════════
     CONTADOR DE USUARIOS
  ═══════════════════════════════════════════════════════════ */
  function setUserCount(n) {
    if (!$usersEl) return;
    $usersEl.innerHTML =
      '<i class="fas fa-circle" style="font-size:0.45rem;color:#22c55e;margin-right:4px;"></i>' +
      n + ' viendo';
  }

  /* ═══════════════════════════════════════════════════════════
     UTILIDADES
  ═══════════════════════════════════════════════════════════ */
  function esc(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  var PALETTE = [
    '#ff6b6b','#ffa94d','#ffd43b','#69db7c','#38d9a9',
    '#4dabf7','#748ffc','#da77f2','#f783ac','#63e6be',
    '#ff8787','#74c0fc','#a9e34b','#f08c00','#cc5de8'
  ];

  function nameToColor(name) {
    var h = 0;
    for (var i = 0; i < name.length; i++) {
      h = (h * 31 + name.charCodeAt(i)) & 0xffffffff;
    }
    return PALETTE[Math.abs(h) % PALETTE.length];
  }

  /* ═══════════════════════════════════════════════════════════
     ARRANQUE
  ═══════════════════════════════════════════════════════════ */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
