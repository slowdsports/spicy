<?php
/**
 * StreamHub - Página de partidos por liga
 * liga.php
 * Compatible con nueva estructura matches.json
 */

$ligaId = (string)((int)get('id', '0'));
$type   = get('type', 'soccer');

if ((int)$ligaId <= 0) {
    header('Location: ' . url('eventos', ['type' => $type]));
    exit;
}

/* ==========================================================
   JSON
========================================================== */
$jsonPath = __DIR__ . '/../data/matches.json';
$allPartidos = [];

if (file_exists($jsonPath)) {
    $allPartidos = json_decode(file_get_contents($jsonPath), true) ?? [];
}

/* ==========================================================
   FILTRAR
========================================================== */
$partidos = array_values(array_filter($allPartidos, function($p) use ($ligaId){
    return (string)($p['league'] ?? '') === $ligaId;
}));

/* ==========================================================
   ORDENAR
   1. live primero
   2. upcoming (futuros) por proximidad a ahora — más próximo primero
   3. pasados/finalizados al final
========================================================== */
usort($partidos, function($a, $b){
    $now = time();

    $getTs = function($item) {
        if (!empty($item['timestamp'])) {
            return (int)$item['timestamp'];
        }
        $raw = $item['fecha_hora'] ?? '';
        if ($raw !== '') {
            try {
                $dt = new DateTime($raw, new DateTimeZone('America/Tegucigalpa'));
                return $dt->getTimestamp();
            } catch (Exception $e) {}
        }
        return PHP_INT_MAX;
    };

    $getPeso = function($item) use ($now, $getTs) {
        $ts = $getTs($item);
        if ($ts <= $now && $ts > $now - 10800) return 0; // ventana en vivo (3 h)
        return $ts >= $now ? 1 : 2;
    };

    $pa = $getPeso($a);
    $pb = $getPeso($b);

    if ($pa !== $pb) return $pa <=> $pb;

    $ta = $getTs($a);
    $tb = $getTs($b);

    // upcoming: más próximo primero (ASC); pasados: más reciente primero (DESC)
    return $pa === 2 ? $tb <=> $ta : $ta <=> $tb;
});

/* ==========================================================
   META
========================================================== */
$ligaNombre = $partidos[0]['leagueName'] ?? "Liga {$ligaId}";
$ligaLogo   = BASE_URL . "assets/img/ligas/sf/{$ligaId}.png";

$sportsMeta = [
    'soccer'     => ['label'=>'Fútbol','icon'=>'fa-futbol'],
    'basketball' => ['label'=>'Básquet','icon'=>'fa-basketball'],
    'tennis'     => ['label'=>'Tenis','icon'=>'fa-table-tennis-paddle-ball'],
    'baseball'   => ['label'=>'Béisbol','icon'=>'fa-baseball'],
];

$meta = $sportsMeta[$type] ?? $sportsMeta['soccer'];

/* ==========================================================
   HELPERS
========================================================== */
function teamLogo($id){
    return BASE_URL . "assets/img/equipos/sf/{$id}.png";
}

function canalLogo($id){
    return BASE_URL . "assets/img/canales/{$id}.png";
}

function canalNombre($id){
    return "Canal {$id}";
}
?>

<!-- HERO -->
<section style="background:var(--bg-secondary); padding:2rem 0; border-bottom:1px solid var(--border);">
<div class="container">

<nav style="margin-bottom:1rem;">
<a href="<?= url('home') ?>" class="text-decoration-none" style="color:var(--text-muted); font-size:.8rem;">
<i class="fas fa-home me-1"></i> Inicio
</a>

<span style="margin:0 .4rem;color:var(--border);">›</span>

<a href="<?= url('eventos',['type'=>$type]) ?>" class="text-decoration-none" style="color:var(--text-muted); font-size:.8rem;">
<i class="fas <?= $meta['icon'] ?> me-1"></i> <?= $meta['label'] ?>
</a>

<span style="margin:0 .4rem;color:var(--border);">›</span>

<span style="color:var(--text-secondary); font-size:.8rem;">
<?= htmlspecialchars($ligaNombre) ?>
</span>
</nav>

<div class="d-flex align-items-center gap-3">

<div class="league-box">
<img src="<?= $ligaLogo ?>" alt="">
</div>

<div>
<h1 class="league-title"><?= htmlspecialchars($ligaNombre) ?></h1>
<p class="league-sub"><?= count($partidos) ?> partidos</p>
</div>

</div>
</div>
</section>

<!-- PARTIDOS -->
<section style="padding:2rem 0;">
<div class="container">

<?php if(empty($partidos)): ?>

<div class="empty-box">
<i class="fas <?= $meta['icon'] ?>"></i>
<p>No hay partidos disponibles.</p>
</div>

<?php else: ?>

<div class="accordion sh-accordion" id="matchesAccordion">

<?php foreach($partidos as $i => $p):

$id = "match".$p['id'];

$local = $p['homeTeam']['name'] ?? '';
$visit = $p['awayTeam']['name'] ?? '';

$localLogo = teamLogo($p['homeTeam']['logo'] ?? '');
$visitLogo = teamLogo($p['awayTeam']['logo'] ?? '');

$status = $p['status'] ?? 'upcoming';
$time   = $p['time'] ?? '--:--';

/* canales */
$canalesPartido = [];

for($x=1;$x<=10;$x++){
    $key     = "cnl{$x}";
    $keyName = "cnl{$x}Name";
    $keyLogo = "cnl{$x}Logo";
    if(!empty($p[$key])){
        $cid  = trim($p[$key]);
        $logo = !empty($p[$keyLogo]) ? $p[$keyLogo] : canalLogo($cid);
        $canalesPartido[] = [
            'id'    => $cid,
            'nombre'=> $p[$keyName] ?? "Canal {$cid}",
            'logo'  => $logo
        ];
    }
}

$primerCanal = $canalesPartido[0] ?? null;
?>

<div class="accordion-item sh-item">

<h2 class="accordion-header">
<button class="accordion-button sh-btn <?= $i>0?'collapsed':'' ?>"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#<?= $id ?>">

<div class="match-grid">

<!-- izquierda -->
<div class="match-left">

<div class="mini-league">
<img src="<?= $ligaLogo ?>">
</div>

<?php if($status==='live'): ?>
<span class="badge-live">
<span class="dot-live"></span> EN VIVO
</span>
<?php else: ?>
<span class="badge-time">
<i class="fas fa-clock"></i> <span class="match-countdown" data-time="<?= htmlspecialchars($p['fecha_hora'] ?? $time) ?>" data-ts="<?= (int)($p['timestamp'] ?? 0) ?>"><?= htmlspecialchars($time) ?></span>
</span>
<?php endif; ?>

</div>

<!-- centro -->
<div class="match-center">

<div class="team-box">
<img src="<?= $localLogo ?>" class="team-logo">
<span><?= htmlspecialchars($local) ?></span>
</div>

<div class="vs-box">
<strong>vs</strong>
<small><?= htmlspecialchars($time) ?></small>
</div>

<div class="team-box">
<img src="<?= $visitLogo ?>" class="team-logo">
<span><?= htmlspecialchars($visit) ?></span>
</div>

</div>

<!-- derecha -->
<div class="match-right">

<?php if($primerCanal): ?>
<div class="first-channel">
<img src="<?= $primerCanal['logo'] ?>">
<small><?= htmlspecialchars($primerCanal['nombre']) ?></small>
</div>
<?php else: ?>
<div class="no-channel">
<i class="fas fa-tv"></i>
<small>Sin canal</small>
</div>
<?php endif; ?>

</div>

</div>
</button>
</h2>

<div id="<?= $id ?>" class="accordion-collapse collapse <?= $i===0?'show':'' ?>"
     data-bs-parent="#matchesAccordion">

<div class="accordion-body sh-body">

<h6 class="channels-title">
<i class="fas fa-broadcast-tower me-2"></i>
Canales de cobertura
</h6>

<?php if(empty($canalesPartido)): ?>

<div class="no-list-channel">
No hay canales disponibles.
</div>

<?php else: ?>

<div class="channel-list">

<?php foreach($canalesPartido as $canal): ?>

<a href="<?= url('canal',['id'=>$canal['id'],'partido'=>$p['id']]) ?>" class="channel-row">

<img src="<?= $canal['logo'] ?>" class="channel-row-logo">

<div class="channel-row-name">
<?= htmlspecialchars($canal['nombre']) ?>
</div>

<i class="fas fa-play-circle"></i>

</a>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>
</div>
</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>
</section>

<style>
.league-box{
width:60px;height:60px;
background:var(--bg-card);
border:1px solid var(--border);
border-radius:14px;
display:flex;
align-items:center;
justify-content:center;
padding:8px;
}
.league-box img{
width:100%;
height:100%;
object-fit:contain;
}
.league-title{
font-size:1.35rem;
font-family:'Space Mono',monospace;
margin:0;
font-weight:700;
}
.league-sub{
margin:0;
color:var(--text-muted);
font-size:.85rem;
}

.sh-accordion{
display:flex;
flex-direction:column;
gap:1rem;
}

.sh-item{
border:1px solid var(--border)!important;
border-radius:14px!important;
overflow:hidden;
background:var(--bg-card)!important;
}

.sh-btn{
padding:1rem 1.2rem!important;
background:var(--bg-card)!important;
box-shadow:none!important;
}

.match-grid{
width:100%;
display:grid;
grid-template-columns:100px 1fr 120px;
gap:1rem;
align-items:center;
}

.match-left{
display:flex;
flex-direction:column;
align-items:center;
gap:.5rem;
}

.mini-league{
width:54px;height:54px;
border-radius:12px;
background:var(--bg-secondary);
display:flex;
align-items:center;
justify-content:center;
padding:8px;
border:1px solid var(--border);
}
.mini-league img{
width:100%;
height:100%;
object-fit:contain;
}

.badge-live,.badge-time{
font-size:.72rem;
font-weight:700;
padding:4px 10px;
border-radius:30px;
white-space:nowrap;
}

.badge-live{
background:rgba(239,68,68,.15);
color:#ef4444;
}

.badge-time{
background:var(--accent-soft);
color:var(--accent);
}

.dot-live{
width:6px;height:6px;
background:#ef4444;
border-radius:50%;
display:inline-block;
margin-right:6px;
}

.match-center{
display:flex;
align-items:center;
justify-content:center;
gap:1rem;
}

.team-box{
display:flex;
flex-direction:column;
align-items:center;
gap:.5rem;
width:130px;
text-align:center;
font-weight:700;
font-size:.82rem;
}

.team-logo{
width:44px;
height:44px;
object-fit:contain;
}

.vs-box{
display:flex;
flex-direction:column;
align-items:center;
font-family:'Space Mono',monospace;
}

.vs-box small{
color:var(--text-muted);
font-size:.72rem;
}

.match-right{
display:flex;
justify-content:flex-end;
}

.first-channel{
display:flex;
flex-direction:column;
align-items:center;
gap:.4rem;
background:var(--bg-secondary);
border:1px solid var(--border);
padding:.6rem;
border-radius:10px;
min-width:90px;
}

.first-channel img{
height:30px;
max-width:80px;
object-fit:contain;
}

.no-channel{
color:var(--text-muted);
text-align:center;
}

.sh-body{
background:var(--bg-secondary)!important;
}

.channels-title{
font-size:.8rem;
font-weight:700;
margin-bottom:1rem;
color:var(--text-muted);
}

.channel-list{
display:flex;
flex-direction:column;
gap:.6rem;
}

.channel-row{
display:flex;
align-items:center;
gap:.75rem;
padding:.7rem .9rem;
border:1px solid var(--border);
border-radius:12px;
text-decoration:none;
color:var(--text-primary);
background:var(--bg-card);
transition:.2s;
}

.channel-row:hover{
border-color:var(--accent);
background:var(--accent-soft);
color:var(--accent);
}

.channel-row-logo{
height:26px;
width:auto;
max-width:60px;
object-fit:contain;
}

.channel-row-name{
font-weight:600;
font-size:.85rem;
}

.channel-row i{
margin-left:auto;
}

.empty-box{
text-align:center;
padding:4rem 0;
color:var(--text-muted);
}

@media(max-width:768px){

.match-grid{
grid-template-columns:1fr;
text-align:center;
}

.match-right{
justify-content:center;
}

.match-center{
flex-direction:column;
}

.team-box{
width:100%;
}

}
</style>