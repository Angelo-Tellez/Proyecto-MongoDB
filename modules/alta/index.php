<?php
/**
 * alta/index.php — Módulo de alta de cursos
 * Asociación directa a categoría (sin subcategoría).
 * Patrón PRG para evitar reenvío del POST al recargar.
 */

require_once __DIR__ . '/../../orm/conexion.php';
require_once __DIR__ . '/assets/js/alta.php';

$titulo_pagina = 'Alta de Curso';
$modulo_activo = 'alta';
$css_extra     = '/ProyectoMongoDB/modules/alta/assets/css/alta.css';
$js_extra      = '/ProyectoMongoDB/modules/alta/assets/js/alta.js';

$categorias   = obtenerCategorias();
$niveles      = obtenerNiveles();
$instructores = obtenerInstructores();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = registrarCurso($_POST);
    if ($resultado['exito']) {
        header('Location: index.php?ok=1');
        exit;
    }
    $mensaje_error = $resultado['mensaje'];
}

require_once __DIR__ . '/../layout.php';
?>

<?php if (isset($_GET['ok'])): ?>
    <div class="alerta alerta--exito">Curso registrado correctamente.</div>
<?php endif; ?>

<?php if (isset($mensaje_error)): ?>
    <div class="alerta alerta--error"><?= htmlspecialchars($mensaje_error) ?></div>
<?php endif; ?>

<div class="form-card">
    <h2 class="form-titulo">Registrar Nuevo Curso</h2>

    <form method="POST" action="" id="form-alta">

        <!-- CATEGORÍA: asociación directa, sin subcategoría -->
        <div class="form-group">
            <label for="id_categoria">Categoría *</label>
            <select id="id_categoria" name="id_categoria" required>
                <option value="">-- Selecciona una categoría --</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['ID_CATEGORIA'] ?>">
                        <?= htmlspecialchars($cat['NOMBRE']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- NOMBRE DEL CURSO -->
        <div class="form-group">
            <label for="nombre">Nombre del Curso *</label>
            <input type="text" id="nombre" name="nombre"
                   placeholder="Ej. JavaScript Avanzado" required>
        </div>

        <!-- DESCRIPCIÓN -->
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion"
                      placeholder="Describe el contenido del curso..."></textarea>
        </div>

        <!-- NIVEL / INSTRUCTOR -->
        <div class="form-fila-2">
            <div class="form-group">
                <label for="id_nivel">Nivel *</label>
                <select id="id_nivel" name="id_nivel" required>
                    <option value="">-- Selecciona --</option>
                    <?php foreach ($niveles as $niv): ?>
                        <option value="<?= $niv['ID_NIVEL'] ?>">
                            <?= htmlspecialchars($niv['NOMBRE']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_instructor">Instructor *</label>
                <select id="id_instructor" name="id_instructor" required>
                    <option value="">-- Selecciona --</option>
                    <?php foreach ($instructores as $inst): ?>
                        <option value="<?= $inst['ID_INSTRUCTOR'] ?>">
                            <?= htmlspecialchars($inst['NOMBRE_COMPLETO']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- PRECIO / DURACIÓN -->
        <div class="form-fila-2">
            <div class="form-group">
                <label for="precio">Precio ($) *</label>
                <input type="number" id="precio" name="precio"
                       min="0" step="0.01" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label for="duracion_minutos">Duración (minutos) *</label>
                <input type="number" id="duracion_minutos" name="duracion_minutos"
                       min="1" placeholder="Ej. 300" required>
            </div>
        </div>

        <!-- FECHA DE PUBLICACIÓN -->
        <div class="form-group">
            <label for="fecha_publicacion">Fecha de Publicación *</label>
            <input type="date" id="fecha_publicacion" name="fecha_publicacion"
                   value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn--primario">Registrar Curso</button>
            <button type="reset"  class="btn btn--gris">Limpiar</button>
        </div>

    </form>
</div>

<?php require_once __DIR__ . '/../layout_close.php'; ?>
