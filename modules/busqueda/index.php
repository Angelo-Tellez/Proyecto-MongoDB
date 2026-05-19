<?php
/**
 * busqueda/index.php — Búsqueda y filtrado de cursos
 * Filtro principal: nombre del curso (tema).
 * Filtros secundarios: categoría, nivel, instructor, precio, estatus.
 */

require_once __DIR__ . '/../../orm/conexion.php';
require_once __DIR__ . '/../alta/assets/js/alta.php';   // obtenerCategorias, obtenerNiveles, obtenerInstructores
require_once __DIR__ . '/assets/js/busqueda.php';

$titulo_pagina = 'Búsqueda de Cursos';
$modulo_activo = 'busqueda';
$css_extra     = '/ProyectoMongoDB/modules/busqueda/assets/css/busqueda.css';
$js_extra      = '/ProyectoMongoDB/modules/busqueda/assets/js/busqueda.js';

$categorias   = obtenerCategoriasAll();   // todas: activas e inactivas
$niveles      = obtenerNiveles();
$instructores = obtenerInstructores();

$cursos         = [];
$busqueda_hecha = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $busqueda_hecha = true;
    $cursos         = buscarCursos($_POST);
}

$sel_categorias   = $_POST['id_categoria']  ?? [];
$sel_niveles      = $_POST['id_nivel']      ?? [];
$sel_instructores = $_POST['id_instructor'] ?? [];

require_once __DIR__ . '/../layout.php';
?>

<div class="filtros-card">
    <h2>Filtros de Búsqueda</h2>
    <form method="POST" action="" id="form-busqueda">

        <!-- FILA 1: Búsqueda por nombre (tema) + Categoría -->
        <div class="filtros-grid">

            <div class="form-group">
                <label for="nombre">Tema / Nombre del Curso</label>
                <input type="text" id="nombre" name="nombre"
                       placeholder="Buscar por nombre del curso..."
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
            </div>

            <!-- Categoría: multiselect con checkboxes -->
            <div class="form-group">
                <label>Categoría</label>
                <div class="multiselect" id="ms-categoria">
                    <button type="button" class="multiselect-btn" data-placeholder="-- Todas --">
                        -- Todas --
                    </button>
                    <div class="multiselect-panel">
                        <input type="text" class="multiselect-search" placeholder="Buscar...">
                        <?php foreach ($categorias as $cat): ?>
                            <label class="multiselect-option
                                <?= in_array($cat['ID_CATEGORIA'], $sel_categorias) ? 'seleccionada' : '' ?>">
                                <input type="checkbox"
                                       name="id_categoria[]"
                                       value="<?= $cat['ID_CATEGORIA'] ?>"
                                       <?= in_array($cat['ID_CATEGORIA'], $sel_categorias) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($cat['NOMBRE']) ?>
                                <span class="badge badge--<?= $cat['ESTATUS'] ?>" style="font-size:.7rem;margin-left:4px;">
                                    <?= ucfirst($cat['ESTATUS']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Nivel: multiselect -->
            <div class="form-group">
                <label>Nivel</label>
                <div class="multiselect" id="ms-nivel">
                    <button type="button" class="multiselect-btn" data-placeholder="-- Todos --">
                        -- Todos --
                    </button>
                    <div class="multiselect-panel">
                        <input type="text" class="multiselect-search" placeholder="Buscar...">
                        <?php foreach ($niveles as $niv): ?>
                            <label class="multiselect-option
                                <?= in_array($niv['ID_NIVEL'], $sel_niveles) ? 'seleccionada' : '' ?>">
                                <input type="checkbox"
                                       name="id_nivel[]"
                                       value="<?= $niv['ID_NIVEL'] ?>"
                                       <?= in_array($niv['ID_NIVEL'], $sel_niveles) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($niv['NOMBRE']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- FILA 2: Instructor, Precio, Estatus -->
        <div class="filtros-grid">

            <div class="form-group">
                <label>Instructor</label>
                <div class="multiselect" id="ms-instructor">
                    <button type="button" class="multiselect-btn" data-placeholder="-- Todos --">
                        -- Todos --
                    </button>
                    <div class="multiselect-panel">
                        <input type="text" class="multiselect-search" placeholder="Buscar...">
                        <?php foreach ($instructores as $inst): ?>
                            <label class="multiselect-option
                                <?= in_array($inst['ID_INSTRUCTOR'], $sel_instructores) ? 'seleccionada' : '' ?>">
                                <input type="checkbox"
                                       name="id_instructor[]"
                                       value="<?= $inst['ID_INSTRUCTOR'] ?>"
                                       <?= in_array($inst['ID_INSTRUCTOR'], $sel_instructores) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($inst['NOMBRE_COMPLETO']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Precio ($)</label>
                <div class="rango-inputs">
                    <input type="number" name="precio_min" min="0" step="0.01"
                           placeholder="Min"
                           value="<?= htmlspecialchars($_POST['precio_min'] ?? '') ?>">
                    <span>—</span>
                    <input type="number" name="precio_max" min="0" step="0.01"
                           placeholder="Max"
                           value="<?= htmlspecialchars($_POST['precio_max'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="estatus">Estatus</label>
                <select id="estatus" name="estatus">
                    <option value="">-- Todos --</option>
                    <option value="activo"   <?= (($_POST['estatus'] ?? '') === 'activo')   ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= (($_POST['estatus'] ?? '') === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

        </div>

        <div class="filtros-acciones">
            <button type="submit" class="btn btn--primario">Buscar</button>
            <a href="/ProyectoMongoDB/modules/busqueda/index.php" class="btn btn--gris">Limpiar</a>
        </div>

    </form>
</div>

<?php if ($busqueda_hecha): ?>
<div class="tabla-wrapper">
    <h2>
        Resultados
        <span class="resultado-count"><?= count($cursos) ?> curso(s) encontrado(s)</span>
    </h2>

    <?php if (empty($cursos)): ?>
        <p class="sin-resultados">No se encontraron cursos con los filtros aplicados.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Curso</th>
                <th>Categoría</th>
                <th>Autor</th>
                <th>Nivel</th>
                <th>Precio</th>
                <th>Duración</th>
                <th>Publicación</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cursos as $i => $row): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($row['CURSO']) ?></strong></td>
                <td><?= htmlspecialchars($row['CATEGORIA']) ?></td>
                <td><?= htmlspecialchars($row['AUTOR']) ?></td>
                <td><?= htmlspecialchars($row['NIVEL']) ?></td>
                <td>$<?= number_format($row['PRECIO'], 2) ?></td>
                <td><?= $row['DURACION_HORAS'] ?> hrs</td>
                <td><?= $row['FECHA_PUBLICACION'] ?></td>
                <td>
                    <span class="badge badge--<?= $row['ESTATUS'] ?>">
                        <?= ucfirst($row['ESTATUS']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout_close.php'; ?>
