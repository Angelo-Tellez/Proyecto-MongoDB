/**
 * busqueda.js — Multiselect para categoría, nivel e instructor.
 * Sin lógica de subcategorías.
 */
document.addEventListener('DOMContentLoaded', () => {

    function iniciarMultiselect(contenedor) {
        const btn         = contenedor.querySelector('.multiselect-btn');
        const panel       = contenedor.querySelector('.multiselect-panel');
        const search      = contenedor.querySelector('.multiselect-search');
        const placeholder = btn.dataset.placeholder || '-- Todos --';

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const abierto = contenedor.classList.contains('abierto');
            document.querySelectorAll('.multiselect.abierto').forEach(ms => ms.classList.remove('abierto'));
            if (!abierto) {
                contenedor.classList.add('abierto');
                if (search) search.focus();
            }
        });

        if (search) {
            search.addEventListener('input', () => {
                const texto    = search.value.toLowerCase();
                const opciones = panel.querySelectorAll('.multiselect-option');
                let   visibles = 0;
                opciones.forEach(op => {
                    const ok = op.textContent.toLowerCase().includes(texto);
                    op.style.display = ok ? '' : 'none';
                    if (ok) visibles++;
                });
                let vacio = panel.querySelector('.multiselect-vacio');
                if (visibles === 0 && !vacio) {
                    vacio = document.createElement('p');
                    vacio.className   = 'multiselect-vacio';
                    vacio.textContent = 'Sin resultados';
                    panel.appendChild(vacio);
                } else if (visibles > 0 && vacio) {
                    vacio.remove();
                }
            });
        }

        panel.addEventListener('change', (e) => {
            if (e.target.type !== 'checkbox') return;
            const label = e.target.closest('.multiselect-option');
            if (label) label.classList.toggle('seleccionada', e.target.checked);
            actualizarBoton(contenedor, placeholder);
        });

        actualizarBoton(contenedor, placeholder);
    }

    function actualizarBoton(contenedor, placeholder) {
        const btn      = contenedor.querySelector('.multiselect-btn');
        const marcados = contenedor.querySelectorAll('input[type="checkbox"]:checked');
        const n        = marcados.length;
        if (n === 0) {
            btn.innerHTML = placeholder;
        } else if (n === 1) {
            btn.innerHTML = marcados[0].closest('.multiselect-option').textContent.trim();
        } else {
            btn.innerHTML = `${n} seleccionados <span class="multiselect-badge">${n}</span>`;
        }
    }

    document.querySelectorAll('.multiselect').forEach(ms => iniciarMultiselect(ms));

    document.addEventListener('click', () => {
        document.querySelectorAll('.multiselect.abierto').forEach(ms => ms.classList.remove('abierto'));
    });
});
