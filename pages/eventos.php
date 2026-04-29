<?php
/**
 * StreamHub - Página de eventos deportivos (eventos.php)
 * Adaptado a matches.json nuevo formato
 *
 * URL:
 * ?p=eventos&type=football
 */

$type = get('type', 'football');

/* ==========================================================
   META DEPORTES
========================================================== */
$sportsMeta = [
    'football'   => [
        'label' => 'Fútbol',
        'icon'  => 'fa-futbol',
        'hero'  => 'Partidos de Fútbol'
    ],
    'basketball' => [
        'label' => 'Básquet',
        'icon'  => 'fa-basketball',
        'hero'  => 'Partidos de Básquet'
    ],
    'tennis' => [
        'label' => 'Tenis',
        'icon'  => 'fa-table-tennis-paddle-ball',
        'hero'  => 'Torneos de Tenis'
    ],
    'baseball' => [
        'label' => 'Béisbol',
        'icon'  => 'fa-baseball',
        'hero'  => 'Partidos de Béisbol'
    ],
];

$meta = $sportsMeta[$type] ?? $sportsMeta['football'];

/* ==========================================================
   LEER JSON
========================================================== */
$jsonPath = __DIR__ . '/../data/matches.json';

$partidos = [];

if (file_exists($jsonPath)) {
    $partidos = json_decode(file_get_contents($jsonPath), true) ?? [];
}

/* ==========================================================
   FILTRAR POR TIPO
========================================================== */
$partidos = array_values(array_filter($partidos, function ($p) use ($type) {
    return ($p['tipo'] ?? '') === $type;
}));

/* ==========================================================
   AGRUPAR POR LIGA
========================================================== */
$ligasMap = [];

foreach ($partidos as $partido) {

    $ligaId = (int)($partido['league'] ?? 0);

    if ($ligaId <= 0) {
        continue;
    }

    if (!isset($ligasMap[$ligaId])) {

        $ligasMap[$ligaId] = [
            'id'    => $ligaId,
            'count' => 0,
            'name'  => $partido['leagueName'] ?? "Liga {$ligaId}",
            'logo'  => $partido['leagueLogo'] ?? ''
        ];
    }

    $ligasMap[$ligaId]['count']++;
}

/* ==========================================================
   REINDEXAR + ORDENAR
========================================================== */
$ligasMap = array_values($ligasMap);

usort($ligasMap, function ($a, $b) {

    if ($a['count'] === $b['count']) {
        return strcmp($a['name'], $b['name']);
    }

    return $b['count'] <=> $a['count'];
});
?>

<!-- HERO -->
<section style="background:var(--bg-secondary); padding:2rem 0; border-bottom:1px solid var(--border);">
<div class="container">

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

<div>
<h1 style="font-family:'Space Mono',monospace; font-size:1.4rem; font-weight:700; color:var(--text-primary); margin:0 0 .25rem;">
<i class="fas <?= $meta['icon'] ?> me-2" style="color:var(--accent);"></i>
<?= $meta['hero'] ?>
</h1>

<p style="color:var(--text-muted); font-size:.88rem; margin:0;">
<?= count($ligasMap) ?>
<?= count($ligasMap) === 1 ? ' liga disponible' : ' ligas disponibles' ?>
·
<?= count($partidos) ?>
<?= count($partidos) === 1 ? ' partido' : ' partidos' ?>
</p>
</div>

<!-- PILLS -->
<div class="d-flex gap-2 flex-wrap">
<?php foreach ($sportsMeta as $key => $sport): ?>
<a href="<?= url('eventos', ['type' => $key]) ?>"
   class="pill <?= $key === $type ? 'active' : '' ?>">
<i class="fas <?= $sport['icon'] ?> me-1"></i>
<?= $sport['label'] ?>
</a>
<?php endforeach; ?>
</div>

</div>
</div>
</section>

<!-- GRID -->
<section style="padding:2.5rem 0; background:var(--bg-primary);">
<div class="container">

<?php if (empty($ligasMap)): ?>

<div style="text-align:center; padding:4rem 0; color:var(--text-muted);">
<i class="fas <?= $meta['icon'] ?>" style="font-size:3rem; opacity:.2; display:block; margin-bottom:1rem;"></i>
<p style="font-size:.95rem;">No hay partidos disponibles para este deporte.</p>
</div>

<?php else: ?>

<div class="section-title" style="margin-bottom:1.5rem;">
<span>Ligas disponibles</span>
<span class="section-subtitle">Haz clic para ver los partidos</span>
</div>

<div class="channels-grid" id="leagues-grid">

<?php foreach ($ligasMap as $i => $liga):

$ligaId = $liga['id'];
$count  = $liga['count'];
$nombre = $liga['name'];
$logo   = $liga['logo'];

?>

<a href="<?= url('liga', ['id' => $ligaId, 'type' => $type]) ?>"
   class="channel-card fade-in"
   style="animation-delay:<?= $i * 0.04 ?>s; opacity:0; position:relative;">

<!-- LOGO -->
<div class="channel-logo-wrapper" style="width:72px; height:72px;">

<?php if (!empty($logo)): ?>
<img src="<?= htmlspecialchars($logo) ?>"
     alt="<?= htmlspecialchars($nombre) ?>"
     class="channel-logo">
<?php else: ?>
<i class="fas <?= $meta['icon'] ?>"
   style="font-size:1.8rem; color:var(--accent); opacity:.7;"></i>
<?php endif; ?>

</div>

<!-- NOMBRE -->
<span class="channel-name">
<?= htmlspecialchars($nombre) ?>
</span>

<!-- CONTADOR -->
<span class="channel-category-label">
<i class="fas fa-calendar-alt me-1"></i>
<?= $count ?>
<?= $count === 1 ? 'partido' : 'partidos' ?>
</span>

</a>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>
</section>