<?php
/**
 * categorias/index.php
 * CRUD completo de categorías: crear, editar, activar/desactivar.
 *
 * Flujo:
 *   GET              → muestra tabla + formulario de creación vacío
 *   GET ?editar=N    → muestra tabla + formulario pre-llenado para editar
 *   POST action=crear  → crearCategoria()  → PRG redirect
 *   POST action=editar → editarCategoria() → PRG redirect
 *   POST action=toggle → cambiarEstatusCategoria() → PRG redirect (via AJAX)
 */

require_once __DIR__ . '/../../orm/conexion.php';
require_once __DIR__ . '/assets/js/categorias.php';

$titulo_pagina = 'Categorías';
$modulo_activo = 'categorias';
$css_extra     = '/ProyectoMongoDB/modules/categorias/assets/css/categorias.css';
$js_extra      = '/ProyectoMongoDB/modules/categorias/assets/js/categorias.js';

// --- ACCIONES POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['action'] ?? '';

    // Toggle estatus (llamado desde AJAX también)
    if ($accion === 'toggle') {
        $id  = (int)($_POST['id_categoria'] ?? 0);
        $res = cambiarEstatusCategoria($id);
        // Si es AJAX devuelve JSON; si es form normal redirige
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }
        $msg = $res['exito'] ? 'ok_toggle' : 'err_' . urlencode($res['mensaje']);
        header("Location: index.php?msg=$msg");
        exit;
    }

    if ($accion === 'eliminar') {
        $id  = trim($_POST['id_categoria'] ?? '');
        $res = eliminarCategoria($id);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }
        $msg = $res['exito'] ? 'ok_eliminar' : 'err_' . urlencode($res['mensaje']);
        header("Location: index.php?msg=$msg");
        exit;
    }

    if ($accion === 'crear') {
        $res = crearCategoria($_POST);
        $msg = $res['exito'] ? 'ok_crear' : 'err_' . urlencode($res['mensaje']);
        header("Location: index.php?msg=$msg");
        exit;
    }

    if ($accion === 'editar') {
        $res = editarCategoria($_POST);
        $msg = $res['exito'] ? 'ok_editar' : 'err_' . urlencode($res['mensaje']);
        header("Location: index.php?msg=$msg");
        exit;
    }
}

// --- MODO EDICIÓN (GET ?editar=N) ---
$modo_edicion = false;
$cat_editar   = null;
if (isset($_GET['editar'])) {
    $cat_editar   = obtenerCategoriaPorId((int)$_GET['editar']);
    $modo_edicion = $cat_editar !== null;
}

// --- MENSAJES FLASH ---
$flash = '';
$flash_tipo = '';
$msg_param = $_GET['msg'] ?? '';
if ($msg_param === 'ok_crear')    { $flash = 'Categoría creada correctamente.';      $flash_tipo = 'exito'; }
if ($msg_param === 'ok_editar')  { $flash = 'Categoría actualizada correctamente.'; $flash_tipo = 'exito'; }
if ($msg_param === 'ok_toggle')  { $flash = 'Estatus actualizado correctamente.';   $flash_tipo = 'exito'; }
if ($msg_param === 'ok_eliminar'){ $flash = 'Categoría eliminada correctamente.';   $flash_tipo = 'exito'; }
if (str_starts_with($msg_param, 'err_')) {
    $flash      = urldecode(substr($msg_param, 4));
    $flash_tipo = 'error';
}

$categorias = obtenerTodasCategorias();

require_once __DIR__ . '/../layout.php';
?>

<?php if ($flash): ?>
    <div class="alerta alerta--<?= $flash_tipo ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<?php if ($modo_edicion): ?>
<div class="banner-edicion">
    <span class="banner-edicion__icono">✏️</span>
    <div class="banner-edicion__texto">
        <strong>Modo edición activo</strong>
        <span>Estás editando: "<?= htmlspecialchars($cat_editar['NOMBRE']) ?>" — modifica los campos y haz clic en <em>Guardar Cambios</em>.</span>
    </div>
</div>
<?php endif; ?>

<div class="cat-layout">

    <!-- =====================================================
         PANEL IZQUIERDO: Formulario crear / editar
    ===================================================== -->
    <div class="form-card cat-form-panel">
        <h2 class="form-titulo">
            <?= $modo_edicion ? 'Editar Categoría' : 'Nueva Categoría' ?>
        </h2>

        <form method="POST" action="">
            <input type="hidden" name="action"
                   value="<?= $modo_edicion ? 'editar' : 'crear' ?>">
            <?php if ($modo_edicion): ?>
                <input type="hidden" name="id_categoria"
                       value="<?= $cat_editar['ID_CATEGORIA'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre"
                       placeholder="Ej. Desarrollo Web"
                       value="<?= $modo_edicion ? htmlspecialchars($cat_editar['NOMBRE']) : '' ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion"
                          placeholder="Breve descripción de la categoría..."><?=
                    $modo_edicion ? htmlspecialchars($cat_editar['DESCRIPCION'] ?? '') : ''
                ?></textarea>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn btn--primario">
                    <?= $modo_edicion ? 'Guardar Cambios' : 'Crear Categoría' ?>
                </button>
                <?php if ($modo_edicion): ?>
                    <a href="/ProyectoMongoDB/modules/categorias/index.php"
                       class="btn btn--gris">Cancelar</a>
                <?php else: ?>
                    <button type="reset" class="btn btn--gris">Limpiar</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- =====================================================
         PANEL DERECHO: Tabla de categorías
    ===================================================== -->
    <div class="tabla-wrapper cat-tabla-panel">
        <h2>
            Listado de Categorías
            <span class="resultado-count"><?= count($categorias) ?> registros</span>
        </h2>

        <?php if (empty($categorias)): ?>
            <p class="sin-resultados">Aún no hay categorías registradas.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Cursos</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $i => $cat): ?>
                <tr id="fila-cat-<?= $cat['ID_CATEGORIA'] ?>">
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($cat['NOMBRE']) ?></strong></td>
                    <td class="cat-desc">
                        <?= $cat['DESCRIPCION']
                            ? htmlspecialchars($cat['DESCRIPCION'])
                            : '<span style="color:#94a3b8">—</span>' ?>
                    </td>
                    <td>
                        <span class="badge-count"><?= $cat['TOTAL_CURSOS'] ?></span>
                    </td>
                    <td>
                        <span class="badge badge--<?= $cat['ESTATUS'] ?>"
                              id="badge-<?= $cat['ID_CATEGORIA'] ?>">
                            <?= ucfirst($cat['ESTATUS']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="cat-acciones">
                            <!-- Editar: redirige con ?editar=N -->
                            <a href="?editar=<?= $cat['ID_CATEGORIA'] ?>"
                               class="btn-icono btn-icono--editar"
                               title="Editar">✏️</a>

                            <!-- Toggle estatus via AJAX -->
                            <button class="btn-icono btn-toggle"
                                    data-id="<?= $cat['ID_CATEGORIA'] ?>"
                                    data-estatus="<?= $cat['ESTATUS'] ?>"
                                    title="<?= $cat['ESTATUS'] === 'activo' ? 'Desactivar' : 'Activar' ?>">
                                <?= $cat['ESTATUS'] === 'activo' ? '🔴' : '🟢' ?>
                            </button>

                            <!-- Eliminar via AJAX -->
                            <button class="btn-icono btn-eliminar"
                                    data-id="<?= $cat['ID_CATEGORIA'] ?>"
                                    data-nombre="<?= htmlspecialchars($cat['NOMBRE']) ?>"
                                    data-cursos="<?= $cat['TOTAL_CURSOS'] ?>"
                                    title="Eliminar">🗑️</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../layout_close.php'; ?>
