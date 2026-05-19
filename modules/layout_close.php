<?php

?>

        </section>
        <!-- Fin de .contenido__body — aquí termina el contenido del módulo -->

    </main>
    <!-- Fin de .contenido — área principal derecha -->

</div>
<!-- Fin de .layout — contenedor flex principal (sidebar + contenido) -->

<?php
/**
 * Script específico del módulo.
 * Se carga al final del body (antes del cierre </body>) para garantizar
 * que el DOM ya está completamente cargado cuando el JS se ejecuta,
 * evitando errores de elementos no encontrados.
 */
if (!empty($js_extra)): ?>
    <script src="<?= $js_extra ?>"></script>
<?php endif; ?>

</body>
</html>
