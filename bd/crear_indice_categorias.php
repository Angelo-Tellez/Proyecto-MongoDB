<?php
/**
 * crear_indice_categorias.php
 *
 * Ejecutar UNA sola vez desde la raíz del proyecto:
 *   php bd/crear_indice_categorias.php
 *
 * Hace dos cosas:
 *   1. Rellena el campo nombre_normalizado en todos los documentos existentes
 *   2. Crea un índice único sobre nombre_normalizado en la colección categorias
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../orm/conexion.php';

// ── Función de normalización (igual que en categorias.php) ──────────────────
function normalizarNombre(string $texto): string {
    $texto = mb_strtolower($texto, 'UTF-8');
    $texto = str_replace(
        ['á','é','í','ó','ú','ü','ñ','à','è','ì','ò','ù','â','ê','î','ô','û'],
        ['a','e','i','o','u','u','n','a','e','i','o','u','a','e','i','o','u'],
        $texto
    );
    $texto = preg_replace('/\s+/', ' ', trim($texto));
    return $texto;
}

$db = obtenerConexion();

echo "=== Paso 1: Rellenando nombre_normalizado en documentos existentes ===\n";

$cursor     = $db->categorias->find([]);
$actualizados = 0;

foreach ($cursor as $doc) {
    $norm = normalizarNombre($doc['nombre']);
    $db->categorias->updateOne(
        ['_id' => $doc['_id']],
        ['$set' => ['nombre_normalizado' => $norm]]
    );
    echo "  ✔ {$doc['nombre']} → $norm\n";
    $actualizados++;
}

echo "\n  Total actualizados: $actualizados documentos\n\n";

echo "=== Paso 2: Creando índice único en nombre_normalizado ===\n";

try {
    $db->categorias->createIndex(
        ['nombre_normalizado' => 1],
        ['unique' => true, 'name' => 'unico_nombre_normalizado']
    );
    echo "  ✔ Índice creado correctamente.\n\n";
} catch (Exception $e) {
    echo "  ✘ Error al crear índice: " . $e->getMessage() . "\n\n";
}

echo "=== Listo. Ahora MongoDB rechazará duplicados por sí solo. ===\n";
