<?php
/**
 * busqueda.php — Lógica del módulo de búsqueda de cursos (MongoDB)
 *
 * buscarCursos() construye un filtro dinámico para find().
 * Los cursos tienen categoria e instructor embebidos, por lo que
 * no se necesita ningún $lookup (equivalente a JOIN).
 */

function buscarCursos(array $filtros): array {
    $db    = obtenerConexion();
    $query = [];

    // Búsqueda por nombre (regex case-insensitive)
    // Equivale a: WHERE NOMBRE LIKE '%termino%'
    if (!empty($filtros['nombre'])) {
        $termino        = preg_quote(trim($filtros['nombre']), '/');
        $query['nombre'] = new MongoDB\BSON\Regex($termino, 'i');
    }

    // Filtro por categoría (múltiple → $in)
    if (!empty($filtros['id_categoria'])) {
        $ids = array_filter((array)$filtros['id_categoria'], fn($v) => !empty($v));
        if ($ids) {
            $query['categoria.id'] = ['$in' => array_values($ids)];
        }
    }

    // Filtro por nivel (múltiple → $in sobre campo embebido)
    if (!empty($filtros['id_nivel'])) {
        // id_nivel son ObjectIds; buscamos por nombre de nivel para simplificar
        // Si prefieres filtrar por _id de nivel, ajusta aquí.
        $db_niv = obtenerConexion();
        $oids   = [];
        foreach ((array)$filtros['id_nivel'] as $nid) {
            if (!empty($nid)) {
                try { $oids[] = new MongoDB\BSON\ObjectId($nid); } catch (Exception $e) {}
            }
        }
        if ($oids) {
            $nombres = [];
            foreach ($db_niv->niveles->find(['_id' => ['$in' => $oids]]) as $n) {
                $nombres[] = $n['nombre'];
            }
            if ($nombres) $query['nivel'] = ['$in' => $nombres];
        }
    }

    // Filtro por instructor
    if (!empty($filtros['id_instructor'])) {
        $ids = array_filter((array)$filtros['id_instructor'], fn($v) => !empty($v));
        if ($ids) {
            $query['instructor_id'] = ['$in' => array_values($ids)];
        }
    }

    // Rango de precio
    $precioFiltro = [];
    if (isset($filtros['precio_min']) && $filtros['precio_min'] !== '') {
        $precioFiltro['$gte'] = (float)$filtros['precio_min'];
    }
    if (isset($filtros['precio_max']) && $filtros['precio_max'] !== '') {
        $precioFiltro['$lte'] = (float)$filtros['precio_max'];
    }
    if ($precioFiltro) $query['precio'] = $precioFiltro;

    // Estatus
    if (!empty($filtros['estatus'])) {
        $query['estatus'] = $filtros['estatus'];
    }

    $cursor = $db->cursos->find(
        $query,
        ['sort' => ['fecha_publicacion' => -1]]
    );

    $datos = [];
    foreach ($cursor as $doc) {
        $datos[] = [
            'ID_CURSO'          => idStr($doc['_id']),
            'CATEGORIA'         => $doc['categoria']['nombre'] ?? '',
            'AUTOR'             => ($doc['instructor']['nombre'] ?? '') . ' ' . ($doc['instructor']['apellido'] ?? ''),
            'CURSO'             => $doc['nombre'],
            'NIVEL'             => $doc['nivel'] ?? '',
            'PRECIO'            => $doc['precio'],
            'DURACION_HORAS'    => round(($doc['duracion_minutos'] ?? 0) / 60, 1),
            'FECHA_PUBLICACION' => $doc['fecha_publicacion'] ?? '',
            'ESTATUS'           => $doc['estatus'],
        ];
    }
    return $datos;
}
