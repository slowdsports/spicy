<?php
/**
 * StreamHub - Sincronización diaria de canales TDTChannels (lists/tv.json)
 *
 * Por qué existe: cron/tdt_channels_import.sql hizo la carga inicial (canales
 * + fuentes), pero las URLs m3u8 de un agregador externo rotan con el tiempo.
 * Si las guardáramos "crudas" y nunca las tocáramos de nuevo, quedarían
 * obsoletas en cuanto TDTChannels cambie un CDN. Este cron resuelve eso:
 * cada vez que corre, vuelve a leer la lista completa y:
 *
 *   - Actualiza la URL (y el epg_id) de cada fuente que ya importamos antes.
 *   - Crea canales/fuentes nuevos que aparezcan por primera vez en la lista.
 *   - Desactiva (activo=0, nunca borra) las fuentes cuyo mirror ya no existe
 *     en la lista — no se eliminan filas para no romper historial/likes.
 *
 * Cómo reconoce "esta fuente ya existe": cada fuente importada de aquí
 * guarda un identificador propio en fuentes.tdt_ref, con el formato
 * "{nombre del canal en TDTChannels}#{índice del mirror}" (ej. "La 1#0").
 * Ese campo es SOLO para que este script encuentre la fila de nuevo en la
 * próxima corrida — no tiene relación con fuentes.epg (que es el epg_id que
 * usa la guía de programación, ver cron/tdt_epg.php).
 *
 * Si TDTChannels renombra un canal, este script no lo detecta como "el
 * mismo" (no hay un ID más estable que el nombre — la mayoría de canales de
 * esta lista ni siquiera tienen epg_id) y lo trataría como nuevo, dejando el
 * canal viejo huérfano con su última URL conocida. Limitación conocida y
 * aceptada: los renombres en esta lista son poco frecuentes.
 *
 * Recomendado en cron diario:
 *   0 6 * * * /usr/bin/php /ruta/al/sitio/cron/tdt_channels_sync.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/cache.php';

const TDT_URL = 'https://www.tdtchannels.com/lists/tv.json';

function normalizeNombre(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u']);
    $s = preg_replace('/\(.*?\)/', '', $s);
    $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
}

/**
 * Busca un canal ya existente para reutilizar su id (ej. el TDT "Teledeporte"
 * debe reusar el canal "Teledeporte ES" que ya existía antes de esta
 * integración). Primero exacto contra TODOS los canales; si no hay, una
 * coincidencia parcial (uno es el otro + una palabra más, ej. "Teledeporte"
 * vs "Teledeporte ES") pero SOLO contra $fuzzyEligible — el catálogo curado
 * que nunca vino de este mismo import.
 *
 * Por qué la restricción: la lista de TDTChannels tiene muchos nombres que
 * son prefijo unos de otros (ej. "101TV Málaga" / "101TV Málaga Cofrade",
 * "La 1" / "La 1 Canarias", "TV Perú" / "TV Perú Noticias") y SON canales
 * distintos. Si el fuzzy-match se permitiera entre canales del propio
 * import, se fusionarían por error. $fuzzyEligible excluye exactamente los
 * canales cuyo nombre coincide EXACTO con algún canal de la lista de hoy
 * (esos ya se resuelven por match exacto); todo lo demás —incluido un canal
 * reutilizado como "Teledeporte ES"— sigue disponible para el fuzzy-match
 * en cada corrida, sin depender de si ya tiene fuentes con tdt_ref.
 */
function resolverCanalId(string $norm, array $canalesPorNombre, array $fuzzyEligible): ?int {
    if (isset($canalesPorNombre[$norm])) return $canalesPorNombre[$norm];
    if (mb_strlen($norm) < 4) return null;
    foreach ($fuzzyEligible as $existenteNorm => $id) {
        if (mb_strlen($existenteNorm) < 4) continue;
        if (str_starts_with($existenteNorm, $norm . ' ') || str_starts_with($norm, $existenteNorm . ' ')) {
            return $id;
        }
    }
    return null;
}

/** Mapeo de "ambit" (categoría de TDTChannels) a categorias_canal.id del sitio */
function categoriaParaAmbit(string $ambit): int {
    static $map = [
        'Informativos'    => 2, // Noticias
        'Deportivos'      => 1, // Deportes
        'Infantiles'      => 5, // Infantil
        'Deportivos Int.' => 1,
        'Musicales'       => 4, // Música
    ];
    return $map[$ambit] ?? 3; // Entretenimiento por defecto
}

/** Mejor esfuerzo: país por sufijo del nombre, solo para el grupo "International" */
function paisPorSufijoNombre(string $nombre): ?int {
    static $map = [
        'Francia' => 692, 'Alemania' => 723, 'Italia' => 408,
        'USA' => 542, 'Mexico' => 936, 'México' => 936, 'Argentina' => 896,
    ];
    foreach ($map as $suf => $id) {
        if (mb_substr($nombre, -mb_strlen($suf) - 1) === ' ' . $suf) return $id;
    }
    return null;
}

// ── Descargar la lista ──────────────────────────────────────────────────────
$json = @file_get_contents(TDT_URL);
if (!$json) {
    fwrite(STDERR, "Error al descargar " . TDT_URL . "\n");
    exit(1);
}
$data = json_decode($json, true);
if (!is_array($data) || empty($data['countries'])) {
    fwrite(STDERR, "Error al parsear el JSON de TDTChannels.\n");
    exit(1);
}

// ── Agrupar canales con >=1 opción m3u8 (mismo criterio que el import inicial) ──
// "geo" por URL: true si esa opción trae geo2 (restricción de región conocida,
// ej. "SP"/"CAT") o si el canal trae la etiqueta extra_info "GEO" (TDTChannels
// avisa que el canal está geo-restringido aunque no precise la región exacta
// en esa opción). Cualquiera de las dos basta para marcarlo.
$grouped = [];
foreach ($data['countries'] as $country) {
    foreach ($country['ambits'] as $ambit) {
        foreach ($ambit['channels'] as $ch) {
            $m3u8 = array_values(array_filter($ch['options'] ?? [], fn($o) => ($o['format'] ?? '') === 'm3u8'));
            if (!$m3u8) continue;

            $name = $ch['name'];
            $tieneGeoTag = in_array('GEO', $ch['extra_info'] ?? [], true);
            if (!isset($grouped[$name])) {
                $grouped[$name] = [
                    'name'    => $name,
                    'logo'    => $ch['logo'] ?? '',
                    'epg_id'  => trim((string)($ch['epg_id'] ?? '')),
                    'country' => $country['name'],
                    'ambit'   => $ambit['name'],
                    'urls'    => [],
                    'geo'     => [],
                ];
            }
            foreach ($m3u8 as $o) {
                if (!in_array($o['url'], $grouped[$name]['urls'], true)) {
                    $grouped[$name]['urls'][] = $o['url'];
                    $grouped[$name]['geo'][]  = $tieneGeoTag || $o['geo2'] !== null;
                }
            }
        }
    }
}

$conn = getDBConnection();

// Auto-migración de tdt_ref / geo_bloqueado (mismo patrón que admin/pages/fuentes.php)
$col = $conn->query("SHOW COLUMNS FROM fuentes LIKE 'tdt_ref'");
if ($col && $col->num_rows === 0) {
    $conn->query("ALTER TABLE fuentes ADD COLUMN tdt_ref VARCHAR(180) DEFAULT NULL");
    $conn->query("ALTER TABLE fuentes ADD UNIQUE INDEX idx_fuentes_tdt_ref (tdt_ref)");
}
$col = $conn->query("SHOW COLUMNS FROM fuentes LIKE 'geo_bloqueado'");
if ($col && $col->num_rows === 0) {
    $conn->query("ALTER TABLE fuentes ADD COLUMN geo_bloqueado TINYINT(1) NOT NULL DEFAULT 0");
}

// ── Índices en memoria para resolver canal/fuente existentes ───────────────
$canalesPorNombre = [];
$res = $conn->query("SELECT id, nombre FROM canales");
while ($r = $res->fetch_assoc()) $canalesPorNombre[normalizeNombre($r['nombre'])] = (int)$r['id'];

// Catálogo elegible para fuzzy-match: todos los canales EXCEPTO los que
// coinciden exacto con un nombre de la lista de TDT de hoy (esos ya se
// resuelven por match exacto). Ver docblock de resolverCanalId.
$tdtNormNames = [];
foreach ($grouped as $nombreTdt => $_) $tdtNormNames[normalizeNombre($nombreTdt)] = true;

$fuzzyEligible = array_diff_key($canalesPorNombre, $tdtNormNames);

$fuentesPorRef = [];
$res = $conn->query("SELECT id, url, epg, geo_bloqueado, tdt_ref FROM fuentes WHERE tdt_ref IS NOT NULL");
while ($r = $res->fetch_assoc()) $fuentesPorRef[$r['tdt_ref']] = $r;

$creados = 0; $actualizados = 0; $sinCambios = 0; $canalesNuevos = 0; $desactivados = 0;
$refsVistos = [];

foreach ($grouped as $name => $ch) {
    $norm    = normalizeNombre($name);
    $canalId = resolverCanalId($norm, $canalesPorNombre, $fuzzyEligible);

    $pais      = $ch['country'] === 'Spain' ? 441 : paisPorSufijoNombre($name);
    $categoria = categoriaParaAmbit($ch['ambit']);

    if ($canalId === null) {
        $stmt = $conn->prepare("INSERT INTO canales (nombre, logo, category, activo) VALUES (?, ?, ?, 1)");
        $stmt->bind_param('ssi', $name, $ch['logo'], $categoria);
        $stmt->execute();
        $canalId = $stmt->insert_id;
        $stmt->close();
        $canalesPorNombre[$norm] = $canalId;
        $canalesNuevos++;
    }

    foreach ($ch['urls'] as $i => $url) {
        $tdtRef  = "{$name}#{$i}";
        $refsVistos[$tdtRef] = true;
        $fNombre = count($ch['urls']) > 1 ? "{$name} " . ($i + 1) : $name;
        $epg     = $ch['epg_id'] !== '' ? $ch['epg_id'] : null;
        $geo     = $ch['geo'][$i] ? 1 : 0;

        if (isset($fuentesPorRef[$tdtRef])) {
            $existing = $fuentesPorRef[$tdtRef];
            if ($existing['url'] !== $url || $existing['epg'] !== $epg || (int)$existing['geo_bloqueado'] !== $geo) {
                $stmt = $conn->prepare("UPDATE fuentes SET url = ?, epg = ?, geo_bloqueado = ?, activo = 1 WHERE id = ?");
                $stmt->bind_param('ssii', $url, $epg, $geo, $existing['id']);
                $stmt->execute();
                $stmt->close();
                $actualizados++;
            } else {
                $sinCambios++;
            }
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO fuentes (nombre, canal, url, pais, tipo, epg, activo, mostrar_tv, tdt_ref, geo_bloqueado)
                 VALUES (?, ?, ?, ?, 1, ?, 1, 1, ?, ?)"
            );
            $stmt->bind_param('sisissi', $fNombre, $canalId, $url, $pais, $epg, $tdtRef, $geo);
            $stmt->execute();
            $stmt->close();
            $creados++;
        }
    }
}

// ── Desactivar mirrors que ya no están en la lista (sin borrar nada) ───────
$res = $conn->query("SELECT id, tdt_ref FROM fuentes WHERE tdt_ref IS NOT NULL AND activo = 1");
while ($r = $res->fetch_assoc()) {
    if (!isset($refsVistos[$r['tdt_ref']])) {
        $conn->query("UPDATE fuentes SET activo = 0 WHERE id = " . (int)$r['id']);
        $desactivados++;
    }
}

// ── Regenerar los cachés que lee el sitio público ───────────────────────────
regenerateCanalesCache();
regenerateFuentesCache();

echo "TDTChannels sync — canales nuevos: {$canalesNuevos} | fuentes creadas: {$creados} | actualizadas: {$actualizados} | sin cambios: {$sinCambios} | desactivadas: {$desactivados}\n";
