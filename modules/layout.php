<?php
$titulo_pagina = $titulo_pagina ?? 'Admin';
$modulo_activo = $modulo_activo ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?> | Cursos Admin</title>
    <link rel="stylesheet" href="/ProyectoMongoDB/modules/shared/assets/css/global.css">
    <?php if (!empty($css_extra)): ?>
        <link rel="stylesheet" href="<?= $css_extra ?>">
    <?php endif; ?>
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <div class="sidebar__logo">
            <p>Cursos Admin<small>Panel Administrativo</small></p>
        </div>
        <nav class="sidebar__nav">
            <a href="/ProyectoMongoDB/modules/dashboard/index.php"
               class="sidebar__link <?= $modulo_activo === 'dashboard'   ? 'sidebar__link--activo' : '' ?>">
                📊 Dashboard
            </a>
            <a href="/ProyectoMongoDB/modules/categorias/index.php"
               class="sidebar__link <?= $modulo_activo === 'categorias'  ? 'sidebar__link--activo' : '' ?>">
                🗂️ Categorías
            </a>
            <a href="/ProyectoMongoDB/modules/alta/index.php"
               class="sidebar__link <?= $modulo_activo === 'alta'        ? 'sidebar__link--activo' : '' ?>">
                ➕ Alta de Curso
            </a>
            <a href="/ProyectoMongoDB/modules/busqueda/index.php"
               class="sidebar__link <?= $modulo_activo === 'busqueda'    ? 'sidebar__link--activo' : '' ?>">
                🔍 Búsqueda
            </a>
        </nav>
    </aside>

    <main class="contenido">
        <header class="contenido__header">
            <h1><?= htmlspecialchars($titulo_pagina) ?></h1>
        </header>
        <section class="contenido__body">
