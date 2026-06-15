<?php
/**
 * StreamHub - Error iOS: canal no disponible en Apple
 * Se muestra cuando el visitante accede desde iPhone/iPad/iPod
 * a un canal DASH (tipo 3) que no tiene fuente alternativa para iOS.
 */

if (!isset($fuenteData)) {
    die('Acceso denegado');
}

$nombre = htmlspecialchars($fuenteData['nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../includes/ads.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> - Tele Deportes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #000;
            overflow: hidden;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        #wrapper {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #status {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            padding: 32px;
            text-align: center;
        }

        .s-icon {
            font-size: 3rem;
            line-height: 1;
        }

        .s-title {
            font-size: 1.15em;
            font-weight: 600;
            color: #fff;
        }

        .s-subtitle {
            font-size: 0.88em;
            color: rgba(255, 255, 255, 0.55);
            max-width: 340px;
            line-height: 1.7;
        }

        .s-subtitle strong {
            color: rgba(255, 255, 255, 0.85);
        }

        .s-divider {
            width: 40px;
            height: 2px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 2px;
        }

        .s-hint {
            font-size: 0.78em;
            color: rgba(255, 255, 255, 0.35);
            max-width: 300px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <div id="status">
            <div class="s-icon">📵</div>
            <div class="s-title">No disponible en iOS</div>
            <div class="s-subtitle">
                <strong><?= $nombre ?></strong> usa un formato de video que Safari
                no puede reproducir de forma nativa.
            </div>
            <div class="s-divider"></div>
            <div class="s-hint">
                Para ver este canal, accede desde un dispositivo
                <strong style="color:rgba(255,255,255,.55);">Android</strong> o
                <strong style="color:rgba(255,255,255,.55);">Windows</strong>.
            </div>
        </div>
    </div>
</body>
</html>
