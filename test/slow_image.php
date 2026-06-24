<?php
// Sirve una imagen real del sitio con un retraso artificial, solo para
// poder verificar visualmente el shimmer de carga de forma determinista.
usleep(2500000); // 2.5s
header('Content-Type: image/png');
header('Cache-Control: no-store');
readfile(__DIR__ . '/../assets/img/equipos/sf/6.png');
