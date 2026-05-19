<?php
/**
 * categorias.php — Lógica del módulo de Categorías (MongoDB)
 *
 * Funciones:
 *   obtenerTodasCategorias()   → find() completo con conteo de cursos
 *   obtenerCategoriaPorId()    → findOne()
 *   crearCategoria()           → insertOne()
 *   editarCategoria()          → updateOne() en categorias + updateMany() en cursos
 *   cambiarEstatusCategoria()  → updateOne() toggle estatus
 */

function obtenerTodasCategorias(): array {
    $db = obtenerConexion();

    // Traer todas las categorias y contar cursos con aggregate + lookup
    $pipeline = [
        ['$lookup' => [
            'from'         => 'cursos',
            'localField'   => '_id_str',   // campo auxiliar
            'foreignField' => 'categoria.id',
            'as'           => 'cursos_rel',
        ]],
        // Como _id es ObjectId pero categoria.id es string, usamos $addFields para convertir
        ['$addFields' => [
            '_id_str' => ['$toString' => '$_id'],
        ]],
        ['$lookup' => [
            'from' => 'cursos',
            'let'  => ['cat_id' => ['$toString' => '$_id']],
            'pipeline' => [
                ['$match' => ['$expr' => ['$eq' => ['$categoria.id', '$$cat_id']]]],
            ],
            'as' => 'cursos_rel',
        ]],
        ['$project' => [
            'ID_CATEGORIA'  => ['$toString' => '$_id'],
            'NOMBRE'        => '$nombre',
            'DESCRIPCION'   => '$descripcion',
            'ESTATUS'       => '$estatus',
            'FECHA_CREACION'=> '$fecha_creacion',
            'TOTAL_CURSOS'  => ['$size' => '$cursos_rel'],
        ]],
        ['$sort' => ['NOMBRE' => 1]],
    ];

    $resultado = iterator_to_array($db->categorias->aggregate($pipeline));
    return array_map(fn($r) => iterator_to_array($r), $resultado);
}

function obtenerCategoriaPorId(string $id): ?array {
    $db = obtenerConexion();

    try {
        $oid = new MongoDB\BSON\ObjectId($id);
    } catch (Exception $e) {
        return null;
    }

    $doc = $db->categorias->findOne(['_id' => $oid]);
    if (!$doc) return null;

    return [
        'ID_CATEGORIA' => idStr($doc['_id']),
        'NOMBRE'       => $doc['nombre'],
        'DESCRIPCION'  => $doc['descripcion'] ?? '',
        'ESTATUS'      => $doc['estatus'],
    ];
}

/**
 * Normaliza un string para comparación:
 * 1. Convierte a minúsculas
 * 2. Elimina acentos y diacríticos (á→a, é→e, ñ→n, ü→u, etc.)
 * 3. Elimina espacios extra al inicio, fin y entre palabras
 */
function normalizarNombre(string $texto): string {
    // Minúsculas
    $texto = mb_strtolower($texto, 'UTF-8');
    // Reemplazar caracteres con acento por su equivalente sin acento
    $texto = str_replace(
        ['á','é','í','ó','ú','ü','ñ','à','è','ì','ò','ù','â','ê','î','ô','û'],
        ['a','e','i','o','u','u','n','a','e','i','o','u','a','e','i','o','u'],
        $texto
    );
    // Eliminar espacios múltiples y recortar
    $texto = preg_replace('/\s+/', ' ', trim($texto));
    return $texto;
}

function crearCategoria(array $datos): array {
    $db = obtenerConexion();

    $nombre      = trim($datos['nombre']      ?? '');
    $descripcion = trim($datos['descripcion'] ?? '');

    if (empty($nombre))
        return ['exito' => false, 'mensaje' => 'El nombre de la categoría es obligatorio.'];

    // Unicidad de nombre — consulta directa sobre el campo ya normalizado en BD
    $nombreNorm = normalizarNombre($nombre);
    $existe = $db->categorias->findOne(['nombre_normalizado' => $nombreNorm]);
    if ($existe)
        return ['exito' => false, 'mensaje' => "Ya existe una categoría con el nombre \"$nombre\"."];

    try {
        $db->categorias->insertOne([
            'nombre'             => $nombre,
            'nombre_normalizado' => $nombreNorm,
            'descripcion'        => $descripcion !== '' ? $descripcion : null,
            'estatus'            => 'activo',
            'fecha_creacion'     => new MongoDB\BSON\UTCDateTime(),
        ]);
        return ['exito' => true, 'mensaje' => 'Categoría creada correctamente.'];
    } catch (Exception $e) {
        return ['exito' => false, 'mensaje' => 'Error al crear: ' . $e->getMessage()];
    }
}

function editarCategoria(array $datos): array {
    $db = obtenerConexion();

    $id          = trim($datos['id_categoria'] ?? '');
    $nombre      = trim($datos['nombre']       ?? '');
    $descripcion = trim($datos['descripcion']  ?? '');

    if (empty($id))     return ['exito' => false, 'mensaje' => 'ID de categoría inválido.'];
    if (empty($nombre)) return ['exito' => false, 'mensaje' => 'El nombre es obligatorio.'];

    try {
        $oid = new MongoDB\BSON\ObjectId($id);
    } catch (Exception $e) {
        return ['exito' => false, 'mensaje' => 'ID de categoría inválido.'];
    }

    // Verificar unicidad (excluyendo la propia categoría) — consulta directa sobre nombre_normalizado
    $nombreNorm = normalizarNombre($nombre);
    $existe = $db->categorias->findOne([
        'nombre_normalizado' => $nombreNorm,
        '_id'                => ['$ne' => $oid],
    ]);
    if ($existe)
        return ['exito' => false, 'mensaje' => "Ya existe otra categoría con el nombre \"$nombre\"."];

    try {
        // Actualizar la colección categorias
        $res = $db->categorias->updateOne(
            ['_id' => $oid],
            ['$set' => [
                'nombre'             => $nombre,
                'nombre_normalizado' => $nombreNorm,
                'descripcion'        => $descripcion !== '' ? $descripcion : null,
            ]]
        );

        if ($res->getMatchedCount() === 0)
            return ['exito' => false, 'mensaje' => 'Categoría no encontrada.'];

        // Sincronizar el nombre embebido en todos los cursos de esa categoría
        $db->cursos->updateMany(
            ['categoria.id' => $id],
            ['$set' => ['categoria.nombre' => $nombre]]
        );

        return ['exito' => true, 'mensaje' => 'Categoría actualizada correctamente.'];
    } catch (Exception $e) {
        return ['exito' => false, 'mensaje' => 'Error al editar: ' . $e->getMessage()];
    }
}

function eliminarCategoria(string $id): array {
    $db = obtenerConexion();

    try {
        $oid = new MongoDB\BSON\ObjectId($id);
    } catch (Exception $e) {
        return ['exito' => false, 'mensaje' => 'ID de categoría inválido.'];
    }

    // Verificar que la categoría existe
    $cat = $db->categorias->findOne(['_id' => $oid]);
    if (!$cat)
        return ['exito' => false, 'mensaje' => 'Categoría no encontrada.'];

    // Contar cursos asignados
    $totalCursos = $db->cursos->countDocuments(['categoria.id' => $id]);

    // Si tiene cursos y no se confirmó la eliminación en cascada, avisar
    if ($totalCursos > 0 && empty($_POST['confirmar_cascada']))
        return [
            'exito'        => false,
            'cascada'      => true,
            'total_cursos' => $totalCursos,
            'mensaje'      => "La categoría tiene $totalCursos curso(s) asignado(s). Si la eliminas, también se eliminarán esos cursos.",
        ];

    try {
        // Eliminar cursos de esta categoría primero
        if ($totalCursos > 0)
            $db->cursos->deleteMany(['categoria.id' => $id]);

        // Eliminar la categoría
        $db->categorias->deleteOne(['_id' => $oid]);

        $msg = $totalCursos > 0
            ? "Categoría eliminada junto con $totalCursos curso(s)."
            : 'Categoría eliminada correctamente.';

        return ['exito' => true, 'mensaje' => $msg];
    } catch (Exception $e) {
        return ['exito' => false, 'mensaje' => 'Error al eliminar: ' . $e->getMessage()];
    }
}

function cambiarEstatusCategoria(string $id): array {
    $db = obtenerConexion();

    try {
        $oid = new MongoDB\BSON\ObjectId($id);
    } catch (Exception $e) {
        return ['exito' => false, 'mensaje' => 'ID de categoría inválido.'];
    }

    $doc = $db->categorias->findOne(['_id' => $oid]);
    if (!$doc)
        return ['exito' => false, 'mensaje' => 'Categoría no encontrada.'];

    $nuevo  = $doc['estatus'] === 'activo' ? 'inactivo' : 'activo';
    $accion = $nuevo === 'inactivo' ? 'desactivada' : 'activada';

    $res = $db->categorias->updateOne(
        ['_id' => $oid],
        ['$set' => ['estatus' => $nuevo]]
    );

    if ($res->getModifiedCount() === 0)
        return ['exito' => false, 'mensaje' => 'No se pudo actualizar el estatus.'];

    return ['exito' => true, 'mensaje' => "Categoría $accion correctamente.", 'nuevo_estatus' => $nuevo];
}
