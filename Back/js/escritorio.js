// Back/js/escritorio.js
// Lógica del panel de administración. Vive en un archivo externo porque la CSP del
// admin (script-src 'self') bloquea los <script> y los onclick/onsubmit inline.
(function () {
    'use strict';

    // ── Confirmación antes de eliminar (formularios con data-confirm) ──────────
    document.addEventListener('submit', function (e) {
        var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) {
            e.preventDefault();
        }
    });

    // ── Accesorios: mostrar/ocultar el precio de instalación ───────────────────
    document.addEventListener('change', function (e) {
        var cb = e.target;
        if (!cb.classList || !cb.classList.contains('acc-check')) return;
        var div = document.getElementById('instalacion-' + cb.dataset.id);
        if (!div) return;
        div.style.display = cb.checked ? 'block' : 'none';
        if (!cb.checked) {
            var input = div.querySelector('input');
            if (input) input.value = 0;
        }
    });

    // ── Pruebas de manejo ──────────────────────────────────────────────────────
    function hoy() {
        var c = document.getElementById('pruebas-container');
        return (c && c.dataset.hoy) || new Date().toISOString().slice(0, 10);
    }

    function renumerarPruebas() {
        document.querySelectorAll('#pruebas-container .prueba-item').forEach(function (p, i) {
            var n = p.querySelector('.prueba-num');
            if (n) n.textContent = i + 1;
        });
    }

    var acciones = {
        // Formulario de CREAR: clona la prueba #0
        'add-prueba': function () {
            var base = document.getElementById('prueba-0');
            if (!base) return;
            var siguiente = document.querySelectorAll('#pruebas-container .prueba-item').length;
            var copia = base.cloneNode(true);
            copia.id = 'prueba-' + siguiente;
            var btn = copia.querySelector('.btn-eliminar-prueba');
            if (btn) btn.style.display = 'inline-block';
            copia.querySelectorAll('input, select, textarea').forEach(function (el) {
                if (el.type === 'date') el.value = hoy();
                else if (el.tagName === 'SELECT') el.selectedIndex = 0;
                else el.value = '';
            });
            document.getElementById('pruebas-container').appendChild(copia);
            renumerarPruebas();
        },
        // Formulario de CREAR: quita una fila
        'remove-prueba': function (btn) {
            var item = btn.closest('.prueba-item');
            if (item) item.remove();
            renumerarPruebas();
        },
        // Formulario de EDITAR: añade una prueba nueva desde la <template>
        'add-prueba-nueva': function () {
            var tpl = document.getElementById('plantilla-prueba');
            if (!tpl) return;
            document.getElementById('pruebas-container').appendChild(tpl.content.cloneNode(true));
        },
        // Formulario de EDITAR: quita una prueba nueva antes de guardar
        'remove-prueba-nueva': function (btn) {
            var item = btn.closest('.prueba-item');
            if (item) item.remove();
        }
    };

    document.addEventListener('click', function (e) {
        var el = e.target.closest && e.target.closest('[data-accion]');
        if (!el || !acciones[el.dataset.accion]) return;
        e.preventDefault();
        acciones[el.dataset.accion](el);
    });
})();
