<?php
/**
 * StreamHub - Conexión a base de datos (singleton simple)
 */
function getDBConnection(): mysqli {
    static $conn = null;
    if ($conn !== null) return $conn;

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
