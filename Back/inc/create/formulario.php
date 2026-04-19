<?php
include dirname(__DIR__, 2) . "/db.php";
require_once dirname(__DIR__) . '/csrf.php';

// Obtener marcas, modelos, accesorios y clientes
$marcas     = $conexion->query("SELECT id, nombre FROM marca ORDER BY nombre ASC");
$modelos    = $conexion->query("SELECT id, nombre FROM modelo ORDER BY nombre ASC");
$accesorios = $conexion->query("SELECT id, nombre, precio FROM accesorio WHERE stock > 0 ORDER BY nombre ASC");
$clientes   = $conexion->query("SELECT id, nombre, DNI_NIE FROM cliente ORDER BY nombre ASC");
?>

<form action="/api/inc/create/procesaformulario.php" method="POST" id="formVehiculo">

    <!-- ══════════════════════════════════════════════════ -->
    <!-- SECCIÓN 1: DATOS DEL VEHÍCULO                      -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="form-seccion">
        <h3 class="form-seccion-titulo"> Datos del Vehículo</h3>

        <div class="controlformulario">
            <label for="marca_id">Marca</label>
            <select name="marca_id" id="marca_id" required>
                <option value="">-- Selecciona una marca --</option>
                <?php while($m = $marcas->fetch_assoc()): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="controlformulario">
            <label for="modelo_id">Modelo</label>
            <select name="modelo_id" id="modelo_id" required>
                <option value="">-- Selecciona un modelo --</option>
                <?php while($mo = $modelos->fetch_assoc()): ?>
                    <option value="<?= $mo['id'] ?>"><?= htmlspecialchars($mo['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="controlformulario">
            <label for="año">Año</label>
            <input type="number" name="año" id="año" min="1900" max="2100"
                   value="<?= date('Y') ?>" required>
        </div>

        <div class="controlformulario">
            <label for="vin">VIN (Chasis)</label>
            <input type="text" name="vin" id="vin" maxlength="17" pattern="[A-HJ-NPR-Z0-9]{17}"
                   placeholder="17 caracteres alfanuméricos" required
                   title="El VIN debe tener 17 caracteres sin I, O o Q">
        </div>

        <div class="controlformulario">
            <label for="color">Color</label>
            <input type="text" name="color" id="color" maxlength="50" required>
        </div>

        <div class="controlformulario">
            <label for="precio">Precio (€)</label>
            <input type="number" name="precio" id="precio" step="0.01" min="0" required>
        </div>

        <div class="controlformulario">
            <label for="estado">Estado</label>
            <select name="estado" id="estado" required>
                <option value="nuevo">Nuevo</option>
                <option value="usado">Usado</option>
            </select>
        </div>

        <div class="controlformulario">
            <label for="kilometraje">Kilometraje</label>
            <input type="number" name="kilometraje" id="kilometraje" min="0" value="0">
        </div>

        <div class="controlformulario">
            <label for="fecha_ingreso">Fecha de Ingreso</label>
            <input type="date" name="fecha_ingreso" id="fecha_ingreso"
                   value="<?= date('Y-m-d') ?>">
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- SECCIÓN 2: ACCESORIOS DEL VEHÍCULO                 -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="form-seccion">
        <h3 class="form-seccion-titulo"> Accesorios (opcional)</h3>
        <p class="form-seccion-desc">Selecciona los accesorios que incluye este vehículo e indica el precio de instalación de cada uno.</p>

        <div id="accesorios-lista">
            <?php
            // Reiniciamos el resultado por si fue usado antes
            $accesorios->data_seek(0);
            while ($acc = $accesorios->fetch_assoc()):
            ?>
            <div class="accesorio-item">
                <label class="accesorio-label">
                    <input type="checkbox"
                           name="accesorios[]"
                           value="<?= $acc['id'] ?>"
                           class="acc-check"
                           data-id="<?= $acc['id'] ?>">
                    <span><?= htmlspecialchars($acc['nombre']) ?></span>
                    <span class="acc-precio-base">(Precio base: <?= number_format($acc['precio'], 2) ?>€)</span>
                </label>
                <!-- Campo de instalación: se muestra solo si el checkbox está marcado -->
                <div class="acc-instalacion" id="instalacion-<?= $acc['id'] ?>" style="display:none;">
                    <label>Precio instalación (€):</label>
                    <input type="number"
                           name="instalacion[<?= $acc['id'] ?>]"
                           step="0.01"
                           min="0"
                           value="0"
                           placeholder="0.00">
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- SECCIÓN 3: PRUEBAS DE MANEJO                       -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="form-seccion">
        <h3 class="form-seccion-titulo"> Pruebas de Manejo (opcional)</h3>
        <p class="form-seccion-desc">Programa las pruebas de manejo para este vehículo. Puedes añadir varias.</p>

        <div id="pruebas-container">
            <!-- Plantilla de una prueba (se clona con JS) -->
            <div class="prueba-item" id="prueba-0">
                <div class="prueba-header">
                    <span>Prueba #<span class="prueba-num">1</span></span>
                    <button type="button" class="btn-eliminar-prueba" onclick="eliminarPrueba(this)" style="display:none">✕ Eliminar</button>
                </div>

                <div class="prueba-campos">
                    <div class="controlformulario">
                        <label>Cliente</label>
                        <select name="prueba_cliente_id[]" required>
                            <option value="">-- Selecciona un cliente --</option>
                            <?php
                            $clientes->data_seek(0);
                            while ($cl = $clientes->fetch_assoc()):
                            ?>
                                <option value="<?= $cl['id'] ?>">
                                    <?= htmlspecialchars($cl['nombre']) ?> (<?= htmlspecialchars($cl['DNI_NIE']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="controlformulario">
                        <label>Fecha</label>
                        <input type="date" name="prueba_fecha[]" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="controlformulario">
                        <label>Hora</label>
                        <input type="time" name="prueba_hora[]" required>
                    </div>

                    <div class="controlformulario">
                        <label>Observaciones</label>
                        <textarea name="prueba_observaciones[]" rows="2" placeholder="Notas sobre la prueba..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="btn-añadir-prueba" onclick="añadirPrueba()">
            + Añadir otra prueba de manejo
        </button>
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- BOTÓN PRINCIPAL                                    -->
    <!-- ══════════════════════════════════════════════════ -->
    <input type="submit" value="Registrar Vehículo">

</form>

<!-- ══════════════════════════════════════════════════ -->
<!-- JAVASCRIPT: lógica de accesorios y pruebas         -->
<!-- ══════════════════════════════════════════════════ -->
<script>
// --- ACCESORIOS: mostrar/ocultar campo de instalación ---
document.querySelectorAll('.acc-check').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        var id = this.dataset.id;
        var div = document.getElementById('instalacion-' + id);
        div.style.display = this.checked ? 'block' : 'none';
        // Si se desmarca, resetear el valor
        if (!this.checked) {
            div.querySelector('input').value = 0;
        }
    });
});

// --- PRUEBAS DE MANEJO: añadir y eliminar filas ---
var contadorPruebas = 1; // Ya existe la prueba #0

function añadirPrueba() {
    var plantilla = document.getElementById('prueba-0').cloneNode(true);
    plantilla.id = 'prueba-' + contadorPruebas;
    // Actualizar número visible
    plantilla.querySelector('.prueba-num').textContent = contadorPruebas + 1;
    // Mostrar botón eliminar en las nuevas pruebas
    plantilla.querySelector('.btn-eliminar-prueba').style.display = 'inline-block';
    // Limpiar valores clonados
    plantilla.querySelectorAll('input, select, textarea').forEach(function(el) {
        if (el.type === 'date') el.value = '<?= date('Y-m-d') ?>';
        else if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = '';
    });
    document.getElementById('pruebas-container').appendChild(plantilla);
    contadorPruebas++;
}

function eliminarPrueba(btn) {
    var item = btn.closest('.prueba-item');
    item.remove();
    // Renumerar las pruebas visibles
    document.querySelectorAll('.prueba-item').forEach(function(p, i) {
        p.querySelector('.prueba-num').textContent = i + 1;
    });
}
</script>