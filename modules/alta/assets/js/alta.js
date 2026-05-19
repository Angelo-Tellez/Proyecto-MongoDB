/**
 * alta.js — Sin subcategorías, sin lógica AJAX requerida.
 * Solo ajuste cosmético: pre-rellena fecha con hoy si está vacía.
 */
document.addEventListener('DOMContentLoaded', () => {
    const fechaInput = document.getElementById('fecha_publicacion');
    if (fechaInput && !fechaInput.value) {
        fechaInput.value = new Date().toISOString().split('T')[0];
    }
});
