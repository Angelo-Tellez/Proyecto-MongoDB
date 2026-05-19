<?php
require_once 'conexion.php';

try {
    $db = obtenerConexion();
    // Ping para verificar la conexión
    $db->command(['ping' => 1]);
    echo 'Conexión exitosa a MongoDB. Base de datos: <strong>' . DB_NAME . '</strong>';
} catch (Exception $e) {
    echo 'Error de conexión: ' . $e->getMessage();
}
