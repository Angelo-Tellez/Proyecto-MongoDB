<?php
/**
 * dashboard.php — Consultas del dashboard (MongoDB)
 *
 * Colecciones usadas: cursos, instructores, categorias
 * Los cursos llevan nivel y categoria embebidos como subdocumentos.
 */

function obtenerEstadisticasGenerales(): array {
    $db      = obtenerConexion();
    $cursos  = $db->cursos;
    $instrs  = $db->instructores;
    $cats    = $db->categorias;

    $total         = $cursos->countDocuments([]);
    $activos       = $cursos->countDocuments(['estatus' => 'activo']);
    $inactivos     = $cursos->countDocuments(['estatus' => 'inactivo']);
    $total_instrs  = $instrs->countDocuments([]);
    $total_cats    = $cats->countDocuments(['estatus' => 'activo']);

    // Promedio de precio
    $pipeline = [
        ['$group' => ['_id' => null, 'precio_promedio' => ['$avg' => '$precio']]]
    ];
    $res = iterator_to_array($cursos->aggregate($pipeline));
    $precio_promedio = isset($res[0]) ? round((float)$res[0]['precio_promedio'], 2) : 0;

    return [
        'total_cursos'      => $total,
        'cursos_activos'    => $activos,
        'cursos_inactivos'  => $inactivos,
        'precio_promedio'   => $precio_promedio,
        'total_instructores'=> $total_instrs,
        'total_categorias'  => $total_cats,
    ];
}

function obtenerCursosPorCategoria(): array {
    $db = obtenerConexion();

    $pipeline = [
        ['$group' => [
            '_id'                   => '$categoria.nombre',
            'TOTAL_CURSOS'          => ['$sum' => 1],
            'CURSOS_ACTIVOS'        => ['$sum' => ['$cond' => [['$eq' => ['$estatus', 'activo']],   1, 0]]],
            'CURSOS_INACTIVOS'      => ['$sum' => ['$cond' => [['$eq' => ['$estatus', 'inactivo']], 1, 0]]],
            'PRECIO_PROMEDIO'       => ['$avg' => '$precio'],
            'DURACION_PROM_MIN'     => ['$avg' => '$duracion_minutos'],
        ]],
        ['$project' => [
            'CATEGORIA'             => '$_id',
            'TOTAL_CURSOS'          => 1,
            'CURSOS_ACTIVOS'        => 1,
            'CURSOS_INACTIVOS'      => 1,
            'PRECIO_PROMEDIO'       => ['$round' => ['$PRECIO_PROMEDIO', 2]],
            'DURACION_PROMEDIO_HORAS' => ['$round' => [['$divide' => ['$DURACION_PROM_MIN', 60]], 1]],
        ]],
        ['$sort' => ['TOTAL_CURSOS' => -1]],
    ];

    $resultado = iterator_to_array($db->cursos->aggregate($pipeline));
    return array_map(fn($r) => iterator_to_array($r), $resultado);
}

function obtenerCursosPorInstructor(): array {
    $db = obtenerConexion();

    $pipeline = [
        ['$group' => [
            '_id'                       => '$instructor_id',
            'INSTRUCTOR'                => ['$first' => ['$concat' => ['$instructor.nombre', ' ', '$instructor.apellido']]],
            'EMAIL'                     => ['$first' => '$instructor.email'],
            'TOTAL_CURSOS_PUBLICADOS'   => ['$sum' => 1],
            'CURSOS_ACTIVOS'            => ['$sum' => ['$cond' => [['$eq' => ['$estatus', 'activo']],   1, 0]]],
            'CURSOS_INACTIVOS'          => ['$sum' => ['$cond' => [['$eq' => ['$estatus', 'inactivo']], 1, 0]]],
            'PRECIO_PROMEDIO'           => ['$avg' => '$precio'],
        ]],
        ['$project' => [
            'INSTRUCTOR'                => 1,
            'EMAIL'                     => 1,
            'TOTAL_CURSOS_PUBLICADOS'   => 1,
            'CURSOS_ACTIVOS'            => 1,
            'CURSOS_INACTIVOS'          => 1,
            'PRECIO_PROMEDIO'           => ['$round' => ['$PRECIO_PROMEDIO', 2]],
        ]],
        ['$sort' => ['TOTAL_CURSOS_PUBLICADOS' => -1]],
    ];

    $resultado = iterator_to_array($db->cursos->aggregate($pipeline));
    return array_map(fn($r) => iterator_to_array($r), $resultado);
}

function obtenerCursosPorNivel(): array {
    $db = obtenerConexion();

    $pipeline = [
        ['$group' => [
            '_id'               => '$nivel',
            'TOTAL_CURSOS'      => ['$sum' => 1],
            'PRECIO_PROMEDIO'   => ['$avg' => '$precio'],
            'DURACION_PROM_MIN' => ['$avg' => '$duracion_minutos'],
        ]],
        ['$project' => [
            'NIVEL'                   => '$_id',
            'TOTAL_CURSOS'            => 1,
            'PRECIO_PROMEDIO'         => ['$round' => ['$PRECIO_PROMEDIO', 2]],
            'DURACION_PROMEDIO_HORAS' => ['$round' => [['$divide' => ['$DURACION_PROM_MIN', 60]], 1]],
        ]],
        ['$sort' => ['NIVEL' => 1]],
    ];

    $resultado = iterator_to_array($db->cursos->aggregate($pipeline));
    return array_map(fn($r) => iterator_to_array($r), $resultado);
}

function obtenerCursosPorMes(): array {
    $db = obtenerConexion();

    $pipeline = [
        ['$group' => [
            '_id' => [
                'anio' => ['$year'  => ['$dateFromString' => ['dateString' => '$fecha_publicacion']]],
                'mes'  => ['$month' => ['$dateFromString' => ['dateString' => '$fecha_publicacion']]],
            ],
            'CURSOS_PUBLICADOS' => ['$sum' => 1],
        ]],
        ['$project' => [
            'ANIO'              => '$_id.anio',
            'MES'               => '$_id.mes',
            'CURSOS_PUBLICADOS' => 1,
        ]],
        ['$sort' => ['ANIO' => -1, 'MES' => -1]],
    ];

    $resultado = iterator_to_array($db->cursos->aggregate($pipeline));
    return array_map(fn($r) => iterator_to_array($r), $resultado);
}

function obtenerCursosPorRangoPrecio(): array {
    $db = obtenerConexion();

    $pipeline = [
        ['$addFields' => [
            'RANGO_PRECIO' => ['$switch' => ['branches' => [
                ['case' => ['$eq'  => ['$precio', 0]],                                                  'then' => 'Gratuito'],
                ['case' => ['$and' => [['$gte' => ['$precio', 1]],   ['$lte' => ['$precio', 99]]]],     'then' => '$1 - $99'],
                ['case' => ['$and' => [['$gte' => ['$precio', 100]], ['$lte' => ['$precio', 299]]]],    'then' => '$100 - $299'],
                ['case' => ['$and' => [['$gte' => ['$precio', 300]], ['$lte' => ['$precio', 499]]]],    'then' => '$300 - $499'],
            ], 'default' => '$500+']]
        ]],
        ['$group' => [
            '_id'          => '$RANGO_PRECIO',
            'TOTAL_CURSOS' => ['$sum' => 1],
        ]],
        ['$project' => [
            'RANGO_PRECIO' => '$_id',
            'TOTAL_CURSOS' => 1,
        ]],
        ['$sort' => ['RANGO_PRECIO' => 1]],
    ];

    $resultado = iterator_to_array($db->cursos->aggregate($pipeline));
    return array_map(fn($r) => iterator_to_array($r), $resultado);
}
