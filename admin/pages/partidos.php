<?php
/**
 * Admin - Partidos
 * Lista partidos con filtros por liga/deporte.
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ─────────────────────────────
   Mensajes para Telegram (próximos partidos y en vivo)
───────────────────────────── */
function emojiDeporte(string $tipo): string {
    return match ($tipo) {
        'football', 'soccer' => '⚽️',
        'basketball'         => '🏀',
        'tennis'             => '🎾',
        'baseball'           => '⚾',
        default              => '📺',
    };
}

function primerCanalPartido(array $m): int {
    for ($i = 1; $i <= 10; $i++) {
        $cid = trim((string)($m["cnl{$i}"] ?? ''));
        if ($cid !== '') return (int)$cid;
    }
    return 0;
}

function generarMensajeTelegram(array $m, int $canalId, string $sitioNombre): string {
    $liga  = $m['leagueName'] ?? 'Evento';
    $emoji = emojiDeporte($m['tipo'] ?? '');
    $local = $m['homeTeam']['name'] ?? '';
    $visit = $m['awayTeam']['name'] ?? '';
    $link  = urlAbsolute('canal', ['id' => $canalId, 'partido' => (int)$m['id']]);

    return "✅ {$liga}\n\n"
         . "{$emoji} {$local} vs {$visit}\n\n"
         . "{$link}\n\n"
         . "------ Nota 👇\n\n"
         . "✅ Copia los links y ábrelos en el navegador de tu dispositivo. No presiones directamente desde Telegram.\n\n"
         . "✅ Debajo del reproductor encontraran mas opciones con regulador de calidad y para ios.\n\n"
         . "----- By 👇👇\n\n"
         . "☆*:.｡. {$sitioNombre} .｡.:*☆";
}

try {

    $conn = getDBConnection();
    $conn->set_charset("utf8mb4");

    /* ─────────────────────────────
       Filtros
    ───────────────────────────── */
    $filtroLiga = (int)($_GET['liga'] ?? 0);
    $filtroTipo = trim($_GET['tipo'] ?? '');

    /* ─────────────────────────────
       Ligas para select
    ───────────────────────────── */
    $ligas = $conn->query("
        SELECT id, ligaNombre, tipo
        FROM ligas
        WHERE activo = 1
        ORDER BY tipo, ligaNombre
    ")->fetch_all(MYSQLI_ASSOC);

    /* ─────────────────────────────
       Proveedor de datos configurado (sofascore | fotmob)
    ───────────────────────────── */
    $stmt = $conn->prepare("SELECT valor FROM config_sitio WHERE clave = 'api_partidos'");
    $stmt->execute();
    $apiPartidos = $stmt->get_result()->fetch_assoc()['valor'] ?? 'sofascore';
    $stmt->close();
    if ($apiPartidos !== 'fotmob') $apiPartidos = 'sofascore';

    /* ─────────────────────────────
       Mensajes para Telegram (próximos + en vivo, desde matches.json)
    ───────────────────────────── */
    $stmt = $conn->prepare("SELECT valor FROM config_sitio WHERE clave = 'sitio_nombre'");
    $stmt->execute();
    $sitioNombre = $stmt->get_result()->fetch_assoc()['valor'] ?? 'Tele Deportes';
    $stmt->close();

    $mensajesPartidos = [];
    $matchesJsonPath  = __DIR__ . '/../../data/matches.json';

    if (is_file($matchesJsonPath)) {
        $allMatches = json_decode(file_get_contents($matchesJsonPath), true) ?? [];

        foreach ($allMatches as $m) {
            if (!in_array($m['status'] ?? '', ['upcoming', 'live'], true)) continue;

            $canalId = primerCanalPartido($m);
            if (!$canalId) continue; // sin fuente asignada, no hay link que dar

            $mensajesPartidos[] = [
                'id'      => (int)($m['id'] ?? 0),
                'label'   => ($m['homeTeam']['name'] ?? '') . ' vs ' . ($m['awayTeam']['name'] ?? ''),
                'liga'    => $m['leagueName'] ?? '',
                'status'  => $m['status'],
                'mensaje' => generarMensajeTelegram($m, $canalId, $sitioNombre),
            ];
        }
    }

    /* ─────────────────────────────
       WHERE dinámico
    ───────────────────────────── */
    $where  = [];
    $params = [];
    $types  = '';

    if ($filtroLiga > 0) {
        $where[] = "p.liga = ?";
        $params[] = $filtroLiga;
        $types .= "i";
    }

    if ($filtroTipo !== '') {
        $where[] = "p.tipo = ?";
        $params[] = $filtroTipo;
        $types .= "s";
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    /* ─────────────────────────────
       Consulta partidos
    ───────────────────────────── */
    $sql = "
        SELECT
            p.id,
            p.fecha_hora,
            p.tipo,
            p.liga,
        
            p.canal1,
            p.canal2,
            p.canal3,
            p.canal4,
            p.canal5,
            p.canal6,
            p.canal7,
            p.canal8,
            p.canal9,
            p.canal10,
        
            l.nombre AS equipo_local,
            l.id     AS id_local,
        
            v.nombre AS equipo_visitante,
            v.id     AS id_visitante,
        
            li.ligaNombre AS nombre_liga,
        
            /* FUENTES */
            f1.nombre  AS fuente1_nombre,
            f2.nombre  AS fuente2_nombre,
            f3.nombre  AS fuente3_nombre,
            f4.nombre  AS fuente4_nombre,
            f5.nombre  AS fuente5_nombre,
            f6.nombre  AS fuente6_nombre,
            f7.nombre  AS fuente7_nombre,
            f8.nombre  AS fuente8_nombre,
            f9.nombre  AS fuente9_nombre,
            f10.nombre AS fuente10_nombre,
        
            /* LOGOS DE CANALES */
            c1.logo  AS canal1_logo,
            c2.logo  AS canal2_logo,
            c3.logo  AS canal3_logo,
            c4.logo  AS canal4_logo,
            c5.logo  AS canal5_logo,
            c6.logo  AS canal6_logo,
            c7.logo  AS canal7_logo,
            c8.logo  AS canal8_logo,
            c9.logo  AS canal9_logo,
            c10.logo AS canal10_logo
        
        FROM partidos p
        
        LEFT JOIN equipos l
            ON p.`local` = l.id
        
        LEFT JOIN equipos v
            ON p.visitante = v.id
        
        LEFT JOIN ligas li
            ON p.liga = li.id
        
        /* FUENTES */
        LEFT JOIN fuentes f1  ON p.canal1  = f1.id
        LEFT JOIN fuentes f2  ON p.canal2  = f2.id
        LEFT JOIN fuentes f3  ON p.canal3  = f3.id
        LEFT JOIN fuentes f4  ON p.canal4  = f4.id
        LEFT JOIN fuentes f5  ON p.canal5  = f5.id
        LEFT JOIN fuentes f6  ON p.canal6  = f6.id
        LEFT JOIN fuentes f7  ON p.canal7  = f7.id
        LEFT JOIN fuentes f8  ON p.canal8  = f8.id
        LEFT JOIN fuentes f9  ON p.canal9  = f9.id
        LEFT JOIN fuentes f10 ON p.canal10 = f10.id
        
        /* CANALES (relacionados con fuentes.canal) */
        LEFT JOIN canales c1  ON f1.canal  = c1.id
        LEFT JOIN canales c2  ON f2.canal  = c2.id
        LEFT JOIN canales c3  ON f3.canal  = c3.id
        LEFT JOIN canales c4  ON f4.canal  = c4.id
        LEFT JOIN canales c5  ON f5.canal  = c5.id
        LEFT JOIN canales c6  ON f6.canal  = c6.id
        LEFT JOIN canales c7  ON f7.canal  = c7.id
        LEFT JOIN canales c8  ON f8.canal  = c8.id
        LEFT JOIN canales c9  ON f9.canal  = c9.id
        LEFT JOIN canales c10 ON f10.canal = c10.id

        {$whereSQL}

        ORDER BY p.fecha_hora ASC
        LIMIT 200
    ";

    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $partidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $partidos = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    /* ─────────────────────────────
       Canales modal
    ───────────────────────────── */
    $canalesOpts = $conn->query("
        SELECT id, nombre
        FROM fuentes
        ORDER BY nombre
    ")->fetch_all(MYSQLI_ASSOC);

} catch (Throwable $e) {

    die("
    <div style='padding:20px;color:#fff;background:#b91c1c;border-radius:10px'>
        <b>Error:</b><br>" . htmlspecialchars($e->getMessage()) . "
    </div>
    ");

}
?>

<div class="admin-section-header">
    <div class="admin-section-title">Partidos</div>

    <div class="d-flex gap-2 align-items-center flex-wrap">

        <button class="btn-admin-json" onclick="generarJSON('partidos')" id="btn-json-partidos">
            <i class="fas fa-file-export"></i> Actualizar JSON
        </button>

        <!-- filtro deporte -->
        <select id="filtro-tipo" class="form-select"
            style="width:auto"
            onchange="aplicarFiltros()">

            <option value="" <?= $filtroTipo === '' ? 'selected' : '' ?>>
                Todos los deportes
            </option>

            <option value="football" <?= $filtroTipo === 'football' ? 'selected' : '' ?>>
                Fútbol
            </option>

            <option value="basketball" <?= $filtroTipo === 'basketball' ? 'selected' : '' ?>>
                Básquet
            </option>

            <option value="tennis" <?= $filtroTipo === 'tennis' ? 'selected' : '' ?>>
                Tenis
            </option>

        </select>

        <!-- filtro liga -->
        <select id="filtro-liga" class="form-select"
            style="width:auto"
            onchange="aplicarFiltros()">

            <option value="0">Todas las ligas</option>

            <?php foreach ($ligas as $lig): ?>
                <option value="<?= $lig['id'] ?>"
                    <?= $filtroLiga == $lig['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lig['ligaNombre']) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <!-- importar -->
        <button class="btn-sofa" onclick="mostrarImportarPartidos()">
            <i class="fas fa-cloud-download-alt"></i>
            Importar liga
        </button>

        <!-- pegar JSON manual -->
        <button class="btn-sofa" onclick="mostrarPegarJSON()">
            <i class="fas fa-paste"></i>
            Pegar JSON
        </button>

        <!-- borrar partidos antiguos -->
        <button class="btn-sofa-danger" id="btn-borrar-antiguos" onclick="borrarPartidosAntiguos()">
            <i class="fas fa-broom"></i>
            Borrar partidos &gt; 5 días
        </button>

        <!-- mensajes para telegram -->
        <button class="btn-admin-json" onclick="mostrarMensajesTelegram()">
            <i class="fab fa-telegram"></i>
            Generar mensajes
        </button>

    </div>
</div>

<!-- panel mensajes telegram (próximos + en vivo) -->
<div id="panel-mensajes-telegram" style="display:none;margin-bottom:1rem;">

    <div class="card p-3">

        <div class="mb-2 fw-bold">
            Mensajes para Telegram — próximos partidos y en vivo
            (<?= count($mensajesPartidos) ?>)
        </div>

        <?php if (empty($mensajesPartidos)): ?>

        <div style="opacity:.8;font-size:.9em;">
            No hay partidos próximos o en vivo con una fuente asignada.
            Verifica que <code>matches.json</code> esté actualizado
            (botón "Actualizar JSON").
        </div>

        <?php else: ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($mensajesPartidos as $mp): ?>
            <div class="card p-2" style="background:var(--bg-secondary);">

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span style="font-size:.85em;">
                        <?= $mp['status'] === 'live' ? '🔴 EN VIVO' : '🕒 Próximo' ?>
                        — <?= htmlspecialchars($mp['liga']) ?>
                        — <?= htmlspecialchars($mp['label']) ?>
                    </span>
                    <button class="btn-admin-json" style="padding:.3rem .8rem;font-size:.8em;"
                        onclick="copiarMensajeTelegram(this)">
                        <i class="fas fa-copy"></i> Copiar
                    </button>
                </div>

                <textarea class="form-control" rows="4" readonly
                    style="font-size:.8em;"><?= htmlspecialchars($mp['mensaje']) ?></textarea>

            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>

    </div>
</div>

<!-- panel pegar JSON manual (mientras Sofascore bloquea al servidor) -->
<div id="panel-pegar-json" style="display:none;margin-bottom:1rem;">

    <div class="card p-3">

        <div class="mb-2 fw-bold">
            Importar partidos pegando el JSON de Sofascore
        </div>

        <div class="mb-2" style="opacity:.8;font-size:.9em;">
            Abre en tu navegador, por ejemplo,
            <code>api.sofascore.com/api/v1/unique-tournament/{LIGA}/season/{TEMPORADA}/events/next/0</code>,
            copia todo el JSON de la respuesta y pégalo aquí.
        </div>

        <textarea id="json-pegado" class="form-control" rows="6"
            placeholder='{"events":[ ... ]}'></textarea>

        <div class="d-flex gap-2 mt-2">
            <button class="btn-sofa" id="btn-importar-json-pegado" onclick="importarJSONPegado()">
                Importar
            </button>
        </div>

        <div id="json-pegado-resultado" style="display:none;margin-top:10px;"></div>

    </div>
</div>

<!-- panel importar -->
<div id="panel-sofa-partidos" style="display:none;margin-bottom:1rem;">

    <div class="card p-3">

        <div class="mb-2 fw-bold">
            Importar partidos desde <?= $apiPartidos === 'fotmob' ? 'FotMob' : 'Sofascore' ?>
        </div>

        <?php if ($apiPartidos === 'fotmob'): ?>

        <div class="mb-2" style="opacity:.8;font-size:.9em;">
            Escribe el ID de la liga en FotMob (lo ves en la URL, ej.
            <code>fotmob.com/leagues/<b>77</b>/overview/world-cup</code> → 77).
        </div>

        <div class="d-flex gap-2">

            <input type="number" id="fotmob-liga-id" class="form-control"
                style="width:auto" placeholder="ID de liga en FotMob, ej. 77">

            <button class="btn-sofa"
                id="btn-importar-partidos"
                onclick="importarPartidos()">
                Importar
            </button>

        </div>

        <?php else: ?>

        <div class="d-flex gap-2">

            <select id="sofa-partidos-id" class="form-select">
                <option value="">-- Selecciona liga --</option>
                <?php foreach ($ligas as $lig): ?>
                <option value="<?= $lig['id'] ?>">
                    [<?= $lig['id'] ?>]
                    <?= htmlspecialchars($lig['ligaNombre']) ?>
                    (<?= htmlspecialchars($lig['tipo']) ?>)
                </option>
                <?php endforeach; ?>
            </select>

            <button class="btn-sofa"
                id="btn-importar-partidos"
                onclick="importarPartidos()">
                Importar
            </button>

        </div>

        <?php endif; ?>

        <div id="sofa-partidos-resultado"
            style="display:none;margin-top:10px;"></div>

    </div>
</div>

<!-- tabla -->
<div class="admin-table-wrapper">

<table id="tabla-partidos" class="admin-table">

<thead>
<tr>
    <th>ID</th>
    <th>Fecha</th>
    <th>Local</th>
    <th>Visitante</th>
    <th>Liga</th>
    <th>Tipo</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php if (empty($partidos)): ?>

<tr>
<td colspan="7" class="text-center p-4">
    No hay partidos cargados.
</td>
</tr>

<?php else: ?>

<?php foreach ($partidos as $p): ?>

<tr>

<td><?= $p['id'] ?></td>

<td>
<?= $p['fecha_hora']
    ? date('d/m/Y H:i', strtotime($p['fecha_hora']))
    : '—'; ?>
</td>

<td><?= htmlspecialchars($p['equipo_local'] ?? '—') ?></td>

<td><?= htmlspecialchars($p['equipo_visitante'] ?? '—') ?></td>

<td><?= htmlspecialchars($p['nombre_liga'] ?? '—') ?></td>

<td><?= htmlspecialchars($p['tipo'] ?? '—') ?></td>

<td>
<div class="d-flex gap-1">

<button class="btn-admin-edit"
onclick="abrirModalPartido(<?= $p['id'] ?>)">
<i class="fas fa-pen"></i>
</button>

<button class="btn-admin-delete"
onclick="confirmarBorrar(
'partido',
<?= $p['id'] ?>,
'<?= htmlspecialchars(($p['equipo_local'] ?? '') . ' vs ' . ($p['equipo_visitante'] ?? ''), ENT_QUOTES) ?>'
)">
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

<!-- modal -->
<div class="modal fade" id="modalPartido" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Editar Canales</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" id="partido-id">

<div class="row g-2">

<?php for ($i=1;$i<=10;$i++): ?>

<div class="col-md-6">

<label class="form-label">Canal <?= $i ?></label>

<select id="partido-canal<?= $i ?>" class="form-select">

<option value="">Sin canal</option>

<?php foreach ($canalesOpts as $c): ?>
<option value="<?= $c['id'] ?>">
<?= htmlspecialchars($c['nombre']) ?>
</option>
<?php endforeach; ?>

</select>
</div>

<?php endfor; ?>

</div>
</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">
Cerrar
</button>

<button class="btn btn-primary" onclick="guardarPartido()">
Guardar
</button>
</div>

</div>
</div>
</div>

<script>
const apiPartidos = '<?= $apiPartidos ?>'; // 'sofascore' | 'fotmob' — definido en Configuración

function aplicarFiltros() {
    const tipo = document.getElementById('filtro-tipo').value;
    const liga = document.getElementById('filtro-liga').value;

    let url = '<?= BASE_URL ?>admin/?p=partidos';

    if (tipo) url += '&tipo=' + encodeURIComponent(tipo);
    if (liga && liga !== '0') url += '&liga=' + liga;

    location.href = url;
}

function mostrarImportarPartidos() {
    const panel = document.getElementById('panel-sofa-partidos');
    panel.style.display = 'block';

    // Pre-seleccionar la liga activa en el filtro
    const ligaActiva = document.getElementById('filtro-liga').value;
    if (ligaActiva && ligaActiva !== '0') {
        tsSet('sofa-partidos-id', ligaActiva);
    }
}

function importarPartidos() {

    const ligaId = apiPartidos === 'fotmob'
        ? document.getElementById('fotmob-liga-id').value
        : document.getElementById('sofa-partidos-id').value;

    if (!ligaId) {
        alert(apiPartidos === 'fotmob' ? 'Escribe el ID de la liga en FotMob primero.' : 'Selecciona una liga primero.');
        return;
    }

    const btn = document.getElementById('btn-importar-partidos');
    btn.disabled = true;
    btn.innerHTML = 'Importando...';

    const endpoint = apiPartidos === 'fotmob' ? 'fotmob.php' : 'sofa.php';

    fetch(`<?= BASE_URL ?>admin/${endpoint}?filtrarLiga=${ligaId}`)
    .then(r => r.text())
    .then(t => {
        const box = document.getElementById('sofa-partidos-resultado');
        box.style.display = 'block';
        box.innerHTML = t;
        if (t.startsWith('✓')) generarJSON('partidos');
        setTimeout(()=>location.reload(),1200);
    })
    .catch(e => alert(e))
    .finally(()=>{
        btn.disabled = false;
        btn.innerHTML = 'Importar';
    });
}

function mostrarPegarJSON() {
    const panel = document.getElementById('panel-pegar-json');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function mostrarMensajesTelegram() {
    const panel = document.getElementById('panel-mensajes-telegram');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function copiarMensajeTelegram(btn) {
    const textarea = btn.closest('.card').querySelector('textarea');
    navigator.clipboard.writeText(textarea.value).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copiado';
        setTimeout(() => { btn.innerHTML = orig; }, 1500);
    }).catch(() => adminToast('No se pudo copiar', 'error'));
}

function importarJSONPegado() {

    const json = document.getElementById('json-pegado').value.trim();

    if (!json) {
        alert('Pega el JSON primero.');
        return;
    }

    const btn = document.getElementById('btn-importar-json-pegado');
    btn.disabled = true;
    btn.innerHTML = 'Importando...';

    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'importar_json_partidos', json })
    })
    .then(r => r.json())
    .then(res => {
        const box = document.getElementById('json-pegado-resultado');
        box.style.display = 'block';
        box.textContent = res.message;
        if (res.success) {
            document.getElementById('json-pegado').value = '';
            if (res.message.startsWith('Se agregaron')) generarJSON('partidos');
            setTimeout(() => location.reload(), 1200);
        }
    })
    .catch(() => adminToast('Error de conexión', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Importar';
    });
}

function borrarPartidosAntiguos() {

    if (!confirm('¿Borrar todos los partidos con más de 5 días de antigüedad?\nEsta acción no se puede deshacer.')) return;

    const btn = document.getElementById('btn-borrar-antiguos');
    btn.disabled = true;
    btn.innerHTML = 'Borrando...';

    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_old_partidos', dias: 5 })
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
    .catch(() => adminToast('Error de conexión', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-broom"></i> Borrar partidos &gt; 5 días';
    });
}

function abrirModalPartido(id) {

    document.getElementById('partido-id').value = id;

    fetch(`<?= BASE_URL ?>admin/api/crud.php?action=get_partido&id=${id}`)
    .then(r => r.json())
    .then(data => {

        if (!data.success) return;

        for (let i=1;i<=10;i++) {
            tsSet('partido-canal'+i, data.data['canal'+i] ?? '');
        }

        new bootstrap.Modal(
            document.getElementById('modalPartido')
        ).show();

    });
}

// Auto-abrir modal si la URL contiene ?edit=ID (ej: llegando desde el dashboard)
const _editId = parseInt(new URLSearchParams(window.location.search).get('edit'), 10);
if (_editId) {
    document.addEventListener('DOMContentLoaded', () => abrirModalPartido(_editId));
}
</script>