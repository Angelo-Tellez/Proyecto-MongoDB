<?php
require_once __DIR__ . '/../../orm/conexion.php';
require_once __DIR__ . '/assets/js/dashboard.php';

$titulo_pagina  = 'Dashboard';
$modulo_activo  = 'dashboard';
$css_extra      = '/ProyectoMongoDB/modules/dashboard/assets/css/dashboard.css';

$stats          = obtenerEstadisticasGenerales();
$por_categoria  = obtenerCursosPorCategoria();
$por_instructor = obtenerCursosPorInstructor();
$por_nivel      = obtenerCursosPorNivel();
$por_mes        = obtenerCursosPorMes();
$por_precio     = obtenerCursosPorRangoPrecio();

require_once __DIR__ . '/../layout.php';
?>

<div class="cards-grid">
    <div class="card card--rojo">
        <div class="card__label">Total Cursos</div>
        <div class="card__valor"><?= $stats['total_cursos'] ?></div>
    </div>
    <div class="card card--verde">
        <div class="card__label">Cursos Activos</div>
        <div class="card__valor"><?= $stats['cursos_activos'] ?></div>
    </div>
    <div class="card card--naranja">
        <div class="card__label">Cursos Inactivos</div>
        <div class="card__valor"><?= $stats['cursos_inactivos'] ?></div>
    </div>
    <div class="card card--amarillo">
        <div class="card__label">Precio Promedio</div>
        <div class="card__valor">$<?= $stats['precio_promedio'] ?></div>
    </div>
    <div class="card card--morado">
        <div class="card__label">Instructores</div>
        <div class="card__valor"><?= $stats['total_instructores'] ?></div>
    </div>
    <div class="card" style="border-top-color:var(--menta)">
        <div class="card__label">Categorías Activas</div>
        <div class="card__valor"><?= $stats['total_categorias'] ?></div>
    </div>
</div>

<div class="tabla-wrapper">
    <h2>Cursos por Categoría</h2>
    <table>
        <thead>
            <tr>
                <th>Categoría</th>
                <th>Total</th>
                <th>Activos</th>
                <th>Inactivos</th>
                <th>Precio Promedio</th>
                <th>Duración Prom. (hrs)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($por_categoria)): ?>
                <tr><td colspan="6" style="text-align:center;color:#94a3b8;">Sin datos</td></tr>
            <?php else: ?>
                <?php foreach ($por_categoria as $row): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['CATEGORIA']) ?></strong></td>
                    <td><?= $row['TOTAL_CURSOS'] ?></td>
                    <td><?= $row['CURSOS_ACTIVOS'] ?></td>
                    <td><?= $row['CURSOS_INACTIVOS'] ?></td>
                    <td>$<?= $row['PRECIO_PROMEDIO'] ?></td>
                    <td><?= $row['DURACION_PROMEDIO_HORAS'] ?> hrs</td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="tabla-wrapper">
    <h2>Cursos Publicados por Instructor</h2>
    <table>
        <thead>
            <tr>
                <th>Instructor</th>
                <th>Email</th>
                <th>Total</th>
                <th>Activos</th>
                <th>Inactivos</th>
                <th>Precio Promedio</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($por_instructor as $row): ?>
            <tr>
                <td><strong><?= htmlspecialchars($row['INSTRUCTOR']) ?></strong></td>
                <td><?= htmlspecialchars($row['EMAIL']) ?></td>
                <td><?= $row['TOTAL_CURSOS_PUBLICADOS'] ?></td>
                <td><?= $row['CURSOS_ACTIVOS'] ?></td>
                <td><?= $row['CURSOS_INACTIVOS'] ?></td>
                <td>$<?= $row['PRECIO_PROMEDIO'] ?? '0.00' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="dashboard-fila-2">
    <div class="tabla-wrapper">
        <h2>Cursos por Nivel</h2>
        <table>
            <thead><tr><th>Nivel</th><th>Total</th><th>Precio Prom.</th><th>Duración Prom.</th></tr></thead>
            <tbody>
                <?php foreach ($por_nivel as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['NIVEL']) ?></td>
                    <td><?= $row['TOTAL_CURSOS'] ?></td>
                    <td>$<?= $row['PRECIO_PROMEDIO'] ?></td>
                    <td><?= $row['DURACION_PROMEDIO_HORAS'] ?> hrs</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="tabla-wrapper">
        <h2>Cursos por Rango de Precio</h2>
        <table>
            <thead><tr><th>Rango</th><th>Total</th></tr></thead>
            <tbody>
                <?php foreach ($por_precio as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['RANGO_PRECIO']) ?></td>
                    <td><?= $row['TOTAL_CURSOS'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="tabla-wrapper">
    <h2>Cursos Publicados por Mes</h2>
    <table>
        <thead><tr><th>Año</th><th>Mes</th><th>Cursos Publicados</th></tr></thead>
        <tbody>
            <?php
            $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                      'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
            foreach ($por_mes as $row): ?>
            <tr>
                <td><?= $row['ANIO'] ?></td>
                <td><?= $meses[(int)$row['MES']] ?></td>
                <td><?= $row['CURSOS_PUBLICADOS'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout_close.php'; ?>
