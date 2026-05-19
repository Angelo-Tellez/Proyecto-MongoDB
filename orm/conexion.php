<?php
/**
 * conexion.php — Conexión a MongoDB
 *
 * Requiere:
 *   - Extensión PHP mongodb  (pecl install mongodb)
 *   - Librería oficial       (composer require mongodb/mongodb)
 *
 * Cambia MONGO_URI si usas Atlas u otro host.
 */

define('MONGO_URI',  'mongodb://localhost:27017'); // URI de conexión
define('DB_NAME',    'cursos');                    // Nombre de la base de datos

/**
 * Devuelve la instancia de la base de datos MongoDB.
 * Equivalente al antiguo obtenerConexion() con mysqli.
 */
function obtenerConexion(): MongoDB\Database {
    static $cliente = null;

    if ($cliente === null) {
        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (!file_exists($autoload)) {
            die(json_encode([
                'exito'   => false,
                'mensaje' => 'No se encontró vendor/autoload.php. Ejecuta: composer require mongodb/mongodb'
            ]));
        }
        require_once $autoload;

        try {
            $cliente = new MongoDB\Client(MONGO_URI);
        } catch (Exception $e) {
            die(json_encode([
                'exito'   => false,
                'mensaje' => 'Error de conexión MongoDB: ' . $e->getMessage()
            ]));
        }
    }

    return $cliente->selectDatabase(DB_NAME);
}

/**
 * Convierte un MongoDB\BSON\ObjectId a string de forma segura.
 */
function idStr(mixed $id): string {
    return (string) $id;
}
