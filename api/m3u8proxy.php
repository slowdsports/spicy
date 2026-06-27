<?php
/**
 * api/m3u8proxy.php — Proxy de HLS para fuentes externas con bloqueo
 * anti-hotlinking (zicotv.cc devuelve 403 cuando el manifest/segmentos se
 * piden directo desde el navegador en otro dominio, pero sí responde a
 * peticiones server-side). Reescribe cada línea del manifest para que el
 * sub-playlist y los segmentos se vuelvan a pedir a través de este mismo
 * proxy, así el navegador nunca toca el CDN de origen directamente.
 *
 * Solo reenvía a hosts *.zicotv.cc (allowlist) para no convertirse en un
 * proxy abierto hacia cualquier URL.
 */

function m3uIsAllowedHost(string $url): bool
{
    $host = parse_url($url, PHP_URL_HOST);
    return $host !== null && (bool)preg_match('/(^|\.)zicotv\.cc$/i', $host);
}

function m3uResolveUrl(string $base, string $rel): string
{
    if (preg_match('#^https?://#i', $rel)) {
        return $rel;
    }

    $parts  = parse_url($base);
    $scheme = $parts['scheme'] ?? 'https';
    $host   = $parts['host'] ?? '';
    $port   = isset($parts['port']) ? ':' . $parts['port'] : '';

    if (str_starts_with($rel, '//')) {
        return $scheme . ':' . $rel;
    }
    if (str_starts_with($rel, '/')) {
        return "{$scheme}://{$host}{$port}{$rel}";
    }

    $path = $parts['path'] ?? '/';
    $dir  = substr($path, 0, strrpos($path, '/') + 1);
    return "{$scheme}://{$host}{$port}{$dir}{$rel}";
}

function m3uProxyUrl(string $absoluteUrl): string
{
    return 'm3u8proxy.php?url=' . rawurlencode($absoluteUrl);
}

$target = $_GET['url'] ?? '';
if (!$target || !m3uIsAllowedHost($target)) {
    http_response_code(400);
    exit('URL no permitida');
}

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $target,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 5,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
        'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
]);
$body         = curl_exec($ch);
$httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType  = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: '';
$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $target;
curl_close($ch);

if ($body === false || $httpCode < 200 || $httpCode >= 300) {
    http_response_code($httpCode >= 400 ? $httpCode : 502);
    exit;
}

header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache');

$isPlaylist = stripos($contentType, 'mpegurl') !== false
    || str_starts_with(ltrim($body), '#EXTM3U');

if (!$isPlaylist) {
    header('Content-Type: ' . ($contentType ?: 'application/octet-stream'));
    echo $body;
    exit;
}

header('Content-Type: application/vnd.apple.mpegurl');

$out = [];
foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
    $trimmed = trim($line);

    if ($trimmed === '') {
        $out[] = $line;
        continue;
    }

    if ($trimmed[0] === '#') {
        // Tags con un atributo URI="..." (claves AES-128, EXT-X-MAP de fMP4)
        if (preg_match('/^(#EXT-X-(?:KEY|MAP):.*URI=")([^"]+)(".*)$/', $trimmed, $m)) {
            $abs   = m3uResolveUrl($effectiveUrl, $m[2]);
            $out[] = $m[1] . m3uProxyUrl($abs) . $m[3];
        } else {
            $out[] = $line;
        }
        continue;
    }

    // Línea de URI: sub-playlist o segmento
    $abs   = m3uResolveUrl($effectiveUrl, $trimmed);
    $out[] = m3uProxyUrl($abs);
}

echo implode("\n", $out);
