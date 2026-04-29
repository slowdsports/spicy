<?php
/**
 * Admin - Partidos
 * Lista partidos con filtros por liga/deporte.
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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
    
    /* =====================================================
       GENERAR MATCHES.JSON
    ===================================================== */
    $jsonMatches = [];
    $now = time();

    foreach ($partidos as $p) {

        $ts = strtotime($p['fecha_hora']);

        if ($ts <= $now) {
            $status = 'live';
            $timeTxt = 'EN VIVO';
        } else {
            $status = 'upcoming';
            $timeTxt = date('H:i', $ts);
        }

        $match = [
        'id' => (int)$p['id'],
    
        'league'     => $p['liga'] ?? '',
        'leagueName' => $p['nombre_liga'] ?? '',
        'leagueLogo' => BASE_URL . 'assets/img/ligas/sf/' . ($p['liga'] ?? '') . '.png',
    
        'status'     => $status,
        'time'       => $timeTxt,
        'fecha_hora' => $p['fecha_hora'] ?? '',
        'tipo'       => $p['tipo'] ?? '',
    
        'homeTeam' => [
            'name'  => $p['equipo_local'] ?? '',
            'logo'  => $p['id_local'] ?? '',
            'score' => 0
        ],
    
        'awayTeam' => [
            'name'  => $p['equipo_visitante'] ?? '',
            'logo'  => $p['id_visitante'] ?? '',
            'score' => 0
        ]
    ];
    
    /* CANALES DINÁMICOS */
    for ($i = 1; $i <= 10; $i++) {
        $match["cnl{$i}"]     = $p["canal{$i}"] ?? '';
        $match["cnl{$i}Name"] = $p["fuente{$i}_nombre"] ?? '';
        $match["cnl{$i}Logo"] = $p["canal{$i}_logo"] ?? '';
    }
    
    $jsonMatches[] = $match;
    }

    /* =====================================================
       ORDENAR: próximos primero, luego pasados
    ===================================================== */
    usort($jsonMatches, function ($a, $b) use ($now) {
        $ta   = strtotime($a['fecha_hora']);
        $tb   = strtotime($b['fecha_hora']);
        $aUp  = $ta >= $now;
        $bUp  = $tb >= $now;

        // Un futuro siempre antes que un pasado
        if ($aUp !== $bUp) return $aUp ? -1 : 1;

        // Ambos futuros: el más próximo primero
        if ($aUp) return $ta - $tb;

        // Ambos pasados/en vivo: el más reciente primero
        return $tb - $ta;
    });

    /* =====================================================
       GUARDAR JSON
    ===================================================== */
    $dir = __DIR__ . '/../../data';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents(
        $dir . '/matches.json',
        json_encode(
            $jsonMatches,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        )
    );

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

    </div>
</div>

<!-- panel importar -->
<div id="panel-sofa-partidos" style="display:none;margin-bottom:1rem;">

    <div class="card p-3">

        <div class="mb-2 fw-bold">
            Importar partidos desde Sofascore
        </div>

        <div class="d-flex gap-2">

            <select id="sofa-partidos-id" class="form-select">
                <option value="">-- Selecciona liga --</option>
                <?php foreach ($ligas as $lig): ?>
                <option value="<?= $lig['id'] ?>">
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

        <div id="sofa-partidos-resultado"
            style="display:none;margin-top:10px;"></div>

    </div>
</div>

<!-- tabla -->
<div class="admin-table-wrapper">

<table class="admin-table">

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
        document.getElementById('sofa-partidos-id').value = ligaActiva;
    }
}

function importarPartidos() {

    const ligaId = document.getElementById('sofa-partidos-id').value;

    if (!ligaId) {
        alert('Selecciona una liga primero.');
        return;
    }

    const btn = document.getElementById('btn-importar-partidos');
    btn.disabled = true;
    btn.innerHTML = 'Importando...';

    fetch(`<?= BASE_URL ?>admin/sofa.php?filtrarLiga=${ligaId}`)
    .then(r => r.text())
    .then(t => {
        const box = document.getElementById('sofa-partidos-resultado');
        box.style.display = 'block';
        box.innerHTML = t;
        setTimeout(()=>location.reload(),1200);
    })
    .catch(e => alert(e))
    .finally(()=>{
        btn.disabled = false;
        btn.innerHTML = 'Importar';
    });
}

function abrirModalPartido(id) {

    document.getElementById('partido-id').value = id;

    fetch(`<?= BASE_URL ?>admin/api/crud.php?action=get_partido&id=${id}`)
    .then(r => r.json())
    .then(data => {

        if (!data.success) return;

        for (let i=1;i<=10;i++) {
            const el = document.getElementById('partido-canal'+i);
            if (el) el.value = data.data['canal'+i] ?? '';
        }

        new bootstrap.Modal(
            document.getElementById('modalPartido')
        ).show();

    });
}
</script>