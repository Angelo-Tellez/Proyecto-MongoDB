/**
 * categorias.js
 * Toggle de estatus via AJAX con modal de confirmación + toast de resultado.
 * Resaltado de fila en edición.
 */

/* =========================================================
   TOAST — notificación flotante auto-descartable
   ========================================================= */
function mostrarToast(mensaje, tipo = 'exito') {
    const anterior = document.getElementById('cat-toast');
    if (anterior) anterior.remove();

    const toast = document.createElement('div');
    toast.id          = 'cat-toast';
    toast.className   = `cat-toast cat-toast--${tipo}`;
    toast.textContent = mensaje;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('cat-toast--visible'));

    setTimeout(() => {
        toast.classList.remove('cat-toast--visible');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

/* =========================================================
   MODAL DE CONFIRMACIÓN
   ========================================================= */
function confirmarAccion(mensaje, subMensaje, textoConfirmar, callbackSi) {
    const anterior = document.getElementById('cat-modal-overlay');
    if (anterior) anterior.remove();

    const overlay = document.createElement('div');
    overlay.id        = 'cat-modal-overlay';
    overlay.innerHTML = `
        <div class="cat-modal">
            <div class="cat-modal__icono">⚠️</div>
            <h3 class="cat-modal__titulo">${mensaje}</h3>
            <p  class="cat-modal__sub">${subMensaje}</p>
            <div class="cat-modal__acciones">
                <button id="cat-modal-cancelar" class="btn btn--gris">Cancelar</button>
                <button id="cat-modal-confirmar" class="btn btn--primario">${textoConfirmar}</button>
            </div>
        </div>`;

    document.body.appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add('cat-modal-overlay--visible'));

    const cerrar = () => {
        overlay.classList.remove('cat-modal-overlay--visible');
        setTimeout(() => overlay.remove(), 300);
    };

    document.getElementById('cat-modal-cancelar').addEventListener('click', cerrar);
    overlay.addEventListener('click', e => { if (e.target === overlay) cerrar(); });
    document.getElementById('cat-modal-confirmar').addEventListener('click', () => {
        cerrar();
        callbackSi();
    });
}

/* =========================================================
   TOGGLE DE ESTATUS
   ========================================================= */
async function ejecutarToggle(id, estatusActual, btn) {
    btn.disabled = true;
    btn.style.opacity = '0.5';

    const fd = new FormData();
    fd.append('action',       'toggle');
    fd.append('id_categoria', id);

    try {
        // URL explícita — evita problema con fetch('') en algunos entornos
        const res = await fetch('index.php', {
            method:  'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body:    fd
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        const data = await res.json();

        if (data.exito) {
            const nuevoEstatus = data.nuevo_estatus;

            const badge = document.getElementById('badge-' + id);
            badge.textContent = nuevoEstatus.charAt(0).toUpperCase() + nuevoEstatus.slice(1);
            badge.className   = `badge badge--${nuevoEstatus}`;

            btn.dataset.estatus = nuevoEstatus;
            btn.textContent     = nuevoEstatus === 'activo' ? '🔴' : '🟢';
            btn.title           = nuevoEstatus === 'activo' ? 'Desactivar' : 'Activar';

            mostrarToast(data.mensaje, 'exito');
        } else {
            mostrarToast(data.mensaje || 'Error al cambiar el estatus.', 'error');
        }
    } catch (e) {
        mostrarToast('Error de red al cambiar estatus. Intenta de nuevo.', 'error');
    } finally {
        btn.disabled = false;
        btn.style.opacity = '';
    }
}

/* =========================================================
   INICIALIZACIÓN
   ========================================================= */
document.addEventListener('DOMContentLoaded', () => {

    /* Resalta la fila que se está editando */
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('editar');
    if (editId) {
        const fila = document.getElementById('fila-cat-' + editId);
        if (fila) fila.classList.add('editando');
    }

    /* Botones de toggle con modal de confirmación */
    document.querySelectorAll('.btn-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const id            = btn.dataset.id;
            const estatusActual = btn.dataset.estatus;
            const accion        = estatusActual === 'activo' ? 'desactivar' : 'activar';
            const filaEl        = document.getElementById('fila-cat-' + id);
            const nombre        = filaEl
                ? filaEl.querySelector('td strong')?.textContent.trim()
                : `Categoría #${id}`;

            confirmarAccion(
                `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} categoría?`,
                `La categoría <strong>"${nombre}"</strong> pasará a estar <strong>${estatusActual === 'activo' ? 'inactiva' : 'activa'}</strong>.`,
                accion === 'desactivar' ? '🔴 Sí, desactivar' : '🟢 Sí, activar',
                () => ejecutarToggle(id, estatusActual, btn)
            );
        });
    });

    /* Toast para mensajes flash de edición/creación (viene en la URL) */
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    if (msg === 'ok_editar') mostrarToast('✅ Categoría actualizada correctamente.', 'exito');
    if (msg === 'ok_crear')  mostrarToast('✅ Categoría creada correctamente.', 'exito');
    if (msg === 'ok_toggle') mostrarToast('✅ Estatus actualizado correctamente.', 'exito');
    if (msg && msg.startsWith('err_')) {
        mostrarToast('❌ ' + decodeURIComponent(msg.slice(4)), 'error');
    }
});
