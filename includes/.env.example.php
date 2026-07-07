<?php
/**
 * Plantilla de configuración para producción.
 *
 * INSTRUCCIONES:
 *  1. Copia este archivo al servidor como:  includes/.env.php
 *  2. Rellena los valores con los datos reales de tu hosting cPanel.
 *  3. NUNCA subas .env.php al repositorio Git.
 *
 * En cPanel → MySQL Databases encontrarás el usuario y nombre de BD.
 * El formato suele ser:  u123456_nombrebd  /  u123456_usuario
 */
return [
    'DB_HOST' => 'localhost',
    'DB_USER' => 'u5869826_XXXXX',   // usuario MySQL de cPanel
    'DB_PASS' => 'CAMBIA_ESTO',
    'DB_NAME' => 'u5869826_XXXXX', // nombre de la base de datos

    // URL base: '/' si la web está en la raíz del dominio,
    // '/spicy/' si está en un subdirectorio.
    'BASE_URL' => '/',

    // Bridge Playwright para importar de Sofascore (admin/sofa.php) — ver
    // tools/sofascore-bridge/README.md. URL del servicio desplegado en Render,
    // sin slash final, ej: 'https://tu-app.onrender.com'.
    'SOFA_BRIDGE_URL'    => 'https://tu-app.onrender.com',
    'SOFA_BRIDGE_SECRET' => 'CAMBIA_ESTO_MISMO_VALOR_QUE_BRIDGE_SECRET_EN_RENDER',
];
