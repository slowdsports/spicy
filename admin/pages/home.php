<?php
/**
 * Admin - Home / Dashboard
 * Muestra contadores de cada entidad disponible en el sistema.
 */

// Contadores — bloque independiente para que un error en upcoming no los borre
$counts = array_fill_keys(['canales','fuentes','ligas','equipos','partidos','usuarios','reportes'], '—');
try {
    $conn   = getDBConnection();
    $tables = [
        'canales'  => 'canales',
        'fuentes'  => 'fuentes',
        'ligas'    => 'ligas',
        'equipos'  => 'equipos',
        'partidos' => 'partidos',
        'usuarios' => 'usuarios',
        'reportes' => 'canal_reportes',
    ];
    foreach ($tables as $key => $table) {
        $res = $conn->query("SELECT COUNT(*) AS total FROM `{$table}`");
        $counts[$key] = $res ? (int)$res->fetch_assoc()['total'] : 0;
    }
} catch (Exception $e) { /* mantiene los '—' del array inicial */ }

// Próximos partidos — bloque separado para no afectar los contadores
$upcomingRows = [];
try {
    $conn     = getDBConnection();
    $upcoming = $conn->query("
        SELECT p.id, p.fecha_hora,
               l.nombre  AS local,
               v.nombre  AS visitante,
               li.ligaNombre AS liga
        FROM partidos p
        LEFT JOIN equipos l  ON p.local     = l.id
        LEFT JOIN equipos v  ON p.visitante = v.id
        LEFT JOIN ligas   li ON p.liga      = li.id
        WHERE p.fecha_hora >= NOW()
        ORDER BY p.fecha_hora ASC
        LIMIT 5
    ");
    $upcomingRows = $upcoming ? $upcoming->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) { /* $upcomingRows queda vacío */ }

// Configuración de las stat cards
$stats = [
    ['key' => 'canales',  'icon' => 'fa-tv',           'label' => 'Canales',   'page' => 'canales',  'color' => 'var(--accent)'],
    ['key' => 'fuentes',  'icon' => 'fa-broadcast-tower','label' => 'Fuentes',  'page' => 'fuentes',  'color' => '#06b6d4'],
    ['key' => 'ligas',    'icon' => 'fa-trophy',        'label' => 'Ligas',     'page' => 'ligas',    'color' => '#f59e0b'],
    ['key' => 'partidos', 'icon' => 'fa-futbol',        'label' => 'Partidos',  'page' => 'partidos', 'color' => '#22c55e'],
    ['key' => 'equipos',  'icon' => 'fa-shield-alt',    'label' => 'Equipos',   'page' => 'ligas',    'color' => '#ec4899'],
    ['key' => 'usuarios', 'icon' => 'fa-users',         'label' => 'Usuarios',  'page' => 'usuarios',   'color' => '#a78bfa'],
    ['key' => 'reportes', 'icon' => 'fa-flag',         'label' => 'Reportes',  'page' => 'reportes', 'color' => '#ef4444'],
];
?>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <?php foreach ($stats as $s): ?>
  <div class="col-6 col-md-4 col-xl-2">
    <a href="<?= BASE_URL ?>admin/?p=<?= $s['page'] ?>" class="admin-stat-card">
      <div class="admin-stat-icon" style="background:<?= $s['color'] ?>22; color:<?= $s['color'] ?>;">
        <i class="fas <?= $s['icon'] ?>"></i>
      </div>
      <div>
        <div class="admin-stat-number"><?= $counts[$s['key']] ?></div>
        <div class="admin-stat-label"><?= $s['label'] ?></div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- Próximos partidos -->
<div class="admin-table-wrapper">
  <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-calendar-alt" style="color:var(--accent);"></i>
    <span style="font-family:'Space Mono',monospace; font-size:0.9rem; font-weight:700; color:var(--text-primary);">
      Próximos partidos
    </span>
  </div>

  <?php if (empty($upcomingRows)): ?>
    <div class="admin-empty">
      <i class="fas fa-futbol"></i>
      <p>No hay partidos próximos registrados.</p>
    </div>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>Fecha / Hora</th>
          <th>Local</th>
          <th>Visitante</th>
          <th>Liga</th>
          <th style="width:70px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($upcomingRows as $row): ?>
        <tr>
          <td style="font-family:'Space Mono',monospace; font-size:0.78rem; color:var(--accent);">
            <?= date('d/m/Y H:i', strtotime($row['fecha_hora'])) ?>
          </td>
          <td><?= htmlspecialchars($row['local'] ?? '—') ?></td>
          <td><?= htmlspecialchars($row['visitante'] ?? '—') ?></td>
          <td><span style="font-size:0.78rem; color:var(--text-muted);"><?= htmlspecialchars($row['liga'] ?? '—') ?></span></td>
          <td>
            <a href="<?= BASE_URL ?>admin/?p=partidos&edit=<?= $row['id'] ?>" class="btn-admin-edit" title="Editar partido">
              <i class="fas fa-pen"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
