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
];
