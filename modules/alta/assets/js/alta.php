<?php
/**
 * alta.php — Lógica del módulo de alta de cursos (MongoDB)
 *
 * En MongoDB cada curso embebe categoria e instructor como subdocumentos.
 * Colecciones: cursos, categorias, niveles, instructores
 */

function obtenerCategorias(): array {
    $db     = obtenerConexion();
    $cursor = $db->categorias->find(
        ['estatus' => 'activo'],
        ['projection' => ['_id' => 1, 'nombre' => 1], 'sort' => ['nombre' => 1]]
    );
    $datos = [];
    foreach ($cursor as $doc) {
        $datos[] = ['ID_CATEGORIA' => idStr($doc['_id']), 'NOMBRE' => $doc['nombre']];
    }
    return $datos;
}

function obtenerCategoriasAll(): array {
    $db     = obtenerConexion();
    $cursor = $db->categorias->find(
        [],
        ['projection' => ['_id' => 1, 'nombre' => 1, 'estatus' => 1], 'sort' => ['nombre' => 1]]
    );
    $datos = [];
    foreach ($cursor as $doc) {
        $datos[] = [
            'ID_CATEGORIA' => idStr($doc['_id']),
            'NOMBRE'       => $doc['nombre'],
            'ESTATUS'      => $doc['estatus'],
        ];
    }
    return $datos;
}

function obtenerNiveles(): array {
    $db     = obtenerConexion();
    $cursor = $db->niveles->find(
        ['estatus' => 'activo'],
        ['projection' => ['_id' => 1, 'nombre' => 1], 'sort' => ['orden' => 1]]
    );
    $datos = [];
    foreach ($cursor as $doc) {
        $datos[] = ['ID_NIVEL' => idStr($doc['_id']), 'NOMBRE' => $doc['nombre']];
    }
    return $datos;
}

function obtenerInstructores(): array {
    $db     = obtenerConexion();
    $cursor = $db->instructores->find(
        ['estatus' => 'activo'],
        ['projection' => ['_id' => 1, 'nombre' => 1, 'apellido' => 1], 'sort' => ['nombre' => 1]]
    );
    $datos = [];
    foreach ($cursor as $doc) {
        $datos[] = [
            'ID_INSTRUCTOR'  => idStr($doc['_id']),
            'NOMBRE_COMPLETO'=> $doc['nombre'] . ' ' . $doc['apellido'],
        ];
    }
    return $datos;
}

function registrarCurso(array $datos): array {
    $db = obtenerConexion();

    $id_categoria     = trim($datos['id_categoria']     ?? '');
    $id_nivel         = trim($datos['id_nivel']         ?? '');
    $id_instructor    = trim($datos['id_instructor']    ?? '');
    $nombre           = trim($datos['nombre']           ?? '');
    $descripcion      = trim($datos['descripcion']      ?? '');
    $precio           = (float)($datos['precio']        ?? 0);
    $duracion_minutos = (int)  ($datos['duracion_minutos'] ?? 0);
    $fecha_pub        = trim($datos['fecha_publicacion'] ?? '');

    if (empty($nombre))           return ['exito' => false, 'mensaje' => 'El nombre del curso es obligatorio.'];
    if (empty($id_categoria))     return ['exito' => false, 'mensaje' => 'Selecciona una categoría válida.'];
    if (empty($id_nivel))         return ['exito' => false, 'mensaje' => 'Selecciona un nivel válido.'];
    if (empty($id_instructor))    return ['exito' => false, 'mensaje' => 'Selecciona un instructor válido.'];
    if ($precio < 0)              return ['exito' => false, 'mensaje' => 'El precio no puede ser negativo.'];
    if ($duracion_minutos <= 0)   return ['exito' => false, 'mensaje' => 'La duración debe ser mayor a 0 minutos.'];
    if (empty($fecha_pub))        return ['exito' => false, 'mensaje' => 'La fecha de publicación es obligatoria.'];

    // Validar que los ObjectIds existen y traer datos para embeber
    try {
        $oidCat  = new MongoDB\BSON\ObjectId($id_categoria);
        $oidNiv  = new MongoDB\BSON\ObjectId($id_nivel);
        $oidInst = new MongoDB\BSON\ObjectId($id_instructor);
    } catch (Exception $e) {
        return ['exito' => false, 'mensaje' => 'ID inválido proporcionado.'];
    }

    $cat  = $db->categorias->findOne(['_id' => $oidCat]);
    $niv  = $db->niveles->findOne(['_id'    => $oidNiv]);
    $inst = $db->instructores->findOne(['_id'=> $oidInst]);

    if (!$cat)  return ['exito' => false, 'mensaje' => 'Categoría no encontrada.'];
    if (!$niv)  return ['exito' => false, 'mensaje' => 'Nivel no encontrado.'];
    if (!$inst) return ['exito' => false, 'mensaje' => 'Instructor no encontrado.'];

    // Verificar nombre único dentro de la categoría
    $existe = $db->cursos->findOne([
        'categoria.id' => idStr($oidCat),
        'nombre'       => $nombre,
    ]);
    if ($existe) {
        return ['exito' => false, 'mensaje' => 'Ya existe un curso con ese nombre en la categoría seleccionada.'];
    }

    $documento = [
        'categoria' => [
            'id'     => idStr($oidCat),
            'nombre' => $cat['nombre'],
        ],
        'nivel'           => $niv['nombre'],
        'instructor_id'   => idStr($oidInst),
        'instructor'      => [
            'nombre'  => $inst['nombre'],
            'apellido'=> $inst['apellido'],
            'email'   => $inst['email'],
        ],
        'nombre'           => $nombre,
        'descripcion'      => $descripcion !== '' ? $descripcion : null,
        'precio'           => $precio,
        'duracion_minutos' => $duracion_minutos,
        'fecha_publicacion'=> $fecha_pub,
        'estatus'          => 'activo',
        'fecha_creacion'   => new MongoDB\BSON\UTCDateTime(),
    ];

    try {
        $db->cursos->insertOne($documento);
        return ['exito' => true, 'mensaje' => 'Curso registrado correctamente.'];
    } catch (Exception $e) {
        return ['exito' => false, 'mensaje' => 'Error al registrar: ' . $e->getMessage()];
    }
}
