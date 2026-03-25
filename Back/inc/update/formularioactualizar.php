<?php
include dirname(__DIR__, 2) . "/db.php";

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ ID de vehículo no válido");
}
$id = intval($_GET['id']);

// Cargar vehículo
$resultado = $conexion->query("SELECT * FROM vehiculo WHERE id = $id");
if (!$resultado || $resultado->num_rows === 0) {
    die("❌ Vehículo no encontrado");
}
$fila = $resultado->fetch_assoc();

// Listas desplegables
$marcas  = $conexion->query("SELECT id, nombre FROM marca ORDER BY nombre ASC");
$modelos = $conexion->query("SELECT id, nombre FROM modelo ORDER BY nombre ASC");
$clientes = $conexion->query("SELECT id, nombre, DNI_NIE FROM cliente ORDER BY nombre ASC");

// Accesorios disponibles (con stock)
$accesorios = $conexion->query("SELECT id, nombre, precio FROM accesorio WHERE stock > 0 ORDER BY nombre ASC");

// Accesorios YA asociados a este vehículo (para marcar checkboxes y precargar precios)
$accesorios_actuales = [];
$res_acc = $conexion->query(
    "SELECT accesorio_id, precio_instalacion FROM vehiculo_accesorio WHERE vehiculo_id = $id"
);
while ($a = $res_acc->fetch_assoc()) {
    $accesorios_actuales[$a['accesorio_id']] = $a['precio_instalacion'];
}

// Pruebas de manejo YA registradas para este vehículo
$pruebas_actuales = [];
$res_pruebas = $conexion->query(
    "SELECT id, cliente_id, fecha, hora, observaciones FROM prueba_manejo
     WHERE vehiculo_id = $id ORDER BY fecha ASC, hora ASC"
);
while ($p = $res_pruebas->fetch_assoc()) {
    $pruebas_actuales[] = $p;
}
?>

<form action="inc/update/procesaformulario.php" method="POST" id="formActualizar">
    <input type="hidden" name="id_vehiculo" value="<?= $fila['id'] ?>">

    <!-- ══════════════════════════════════════════════════ -->
    <!-- SECCIÓN 1: DATOS DEL VEHÍCULO                      -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="form-seccion">
        <h3 class="form-seccion-titulo">🚗 Datos del Vehículo</h3>

        <div class="controlformulario">
            <label for="marca_id">Marca</label>
            <select name="marca_id" id="marca_id" required>
                <option value="">-- Selecciona una marca --</option>
                <?php $marcas->data_seek(0); while($m = $marcas->fetch_assoc()): ?>
                    <option value="<?= $m['id'] ?>" <?= $m['id'] == $fila['marca_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="controlformulario">
            <label for="modelo_id">Modelo</label>
            <select name="modelo_id" id="modelo_id" required>
                <option value="">-- Selecciona un modelo --</option>
                <?php $modelos->data_seek(0); while($mo = $modelos->fetch_assoc()): ?>
                    <option value="<?= $mo['id'] ?>" <?= $mo['id'] == $fila['modelo_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mo['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="controlformulario">
            <label for="año">Año</label>
            <input type="number" name="año" id="año" min="1900" max="2100"
                   value="<?= htmlspecialchars($fila['año']) ?>" required>
        </div>

        <div class="controlformulario">
            <label for="vin">VIN (Chasis)</label>
            <input type="text" name="vin" id="vin" maxlength="17" pattern="[A-HJ-NPR-Z0-9]{17}"
                   placeholder="17 caracteres alfanuméricos" required
                   title="El VIN debe tener 17 caracteres sin I, O o Q"
                   value="<?= htmlspecialchars($fila['vin']) ?>">
        </div>

        <div class="controlformulario">
            <label for="color">Color</label>
            <input type="text" name="color" id="color" maxlength="50" required
                   value="<?= htmlspecialchars($fila['color']) ?>">
        </div>

        <div class="controlformulario">
            <label for="precio">Precio (€)</label>
            <input type="number" name="precio" id="precio" step="0.01" min="0" required
                   value="<?= htmlspecialchars($fila['precio']) ?>">
        </div>

        <div class="controlformulario">
            <label for="estado">Estado</label>
            <select name="estado" id="estado" required>
                <option value="nuevo"  <?= $fila['estado'] == 'nuevo'  ? 'selected' : '' ?>>Nuevo</option>
                <option value="usado" <?= $fila['estado'] == 'usado' ? 'selected' : '' ?>>Usado</option>
            </select>
        </div>

        <div class="controlformulario">
            <label for="kilometraje">Kilometraje</label>
            <input type="number" name="kilometraje" id="kilometraje" min="0"
                   value="<?= htmlspecialchars($fila['kilometraje']) ?>">
        </div>

        <div class="controlformulario">
            <label for="fecha_ingreso">Fecha de Ingreso</label>
            <input type="date" name="fecha_ingreso" id="fecha_ingreso"
                   value="<?= htmlspecialchars($fila['fecha_ingreso']) ?>">
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- SECCIÓN 2: ACCESORIOS DEL VEHÍCULO                 -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="form-seccion">
        <h3 class="form-seccion-titulo">🔧 Accesorios</h3>
        <p class="form-seccion-desc">Los accesorios marcados ya están asociados a este vehículo. Desmarca para quitarlos o añade nuevos.</p>

        <div id="accesorios-lista">
            <?php $accesorios->data_seek(0); while ($acc = $accesorios->fetch_assoc()): ?>
            <?php
                $ya_asociado = array_key_exists($acc['id'], $accesorios_actuales);
                $precio_inst = $ya_asociado ? $accesorios_actuales[$acc['id']] : 0;
            ?>
            <div class="accesorio-item">
                <label class="accesorio-label">
                    <input type="checkbox"
                           name="accesorios[]"
                           value="<?= $acc['id'] ?>"
                           class="acc-check"
                           data-id="<?= $acc['id'] ?>"
                           <?= $ya_asociado ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($acc['nombre']) ?></span>
                    <span class="acc-precio-base">(Base: <?= number_format($acc['precio'], 2) ?>€)</span>
                </label>
                <div class="acc-instalacion" id="instalacion-<?= $acc['id'] ?>"
                     style="display: <?= $ya_asociado ? 'block' : 'none' ?>;">
                    <label>Precio instalación (€):</label>
                    <input type="number"
                           name="instalacion[<?= $acc['id'] ?>]"
                           step="0.01"
                           min="0"
                           value="<?= htmlspecialchars($precio_inst) ?>"
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
        <h3 class="form-seccion-titulo">🏁 Pruebas de Manejo</h3>
        <p class="form-seccion-desc">
            Las pruebas existentes se muestran precargadas. Puedes modificarlas, eliminarlas (marcando el checkbox) o añadir nuevas.
        </p>

        <div id="pruebas-container">

            <?php if (!empty($pruebas_actuales)): ?>
            <!-- ── Pruebas EXISTENTES ── -->
            <?php foreach ($pruebas_actuales as $i => $prueba): ?>
            <div class="prueba-item prueba-existente">
                <div class="prueba-header">
                    <span>Prueba #<?= $i + 1 ?> <span class="badge-existente">Ya guardada</span></span>
                    <label class="eliminar-prueba-label">
                        <input type="checkbox"
                               name="prueba_eliminar[]"
                               value="<?= $prueba['id'] ?>">
                        ✕ Eliminar esta prueba
                    </label>
                </div>

                <!-- ID oculto para saber que es una prueba existente a ACTUALIZAR -->
                <input type="hidden" name="prueba_id[]" value="<?= $prueba['id'] ?>">

                <div class="prueba-campos">
                    <div class="controlformulario">
                        <label>Cliente</label>
                        <select name="prueba_cliente_id[]" required>
                            <option value="">-- Selecciona un cliente --</option>
                            <?php $clientes->data_seek(0); while ($cl = $clientes->fetch_assoc()): ?>
                                <option value="<?= $cl['id'] ?>"
                                    <?= $cl['id'] == $prueba['cliente_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cl['nombre']) ?> (<?= htmlspecialchars($cl['DNI_NIE']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="controlformulario">
                        <label>Fecha</label>
                        <input type="date" name="prueba_fecha[]"
                               value="<?= htmlspecialchars($prueba['fecha']) ?>" required>
                    </div>

                    <div class="controlformulario">
                        <label>Hora</label>
                        <input type="time" name="prueba_hora[]"
                               value="<?= htmlspecialchars($prueba['hora']) ?>" required>
                    </div>

                    <div class="controlformulario">
                        <label>Observaciones</label>
                        <textarea name="prueba_observaciones[]" rows="2"><?= htmlspecialchars($prueba['observaciones']) ?></textarea>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <!-- ── Plantilla para NUEVAS pruebas (clonada por JS) ── -->
            <template id="plantilla-prueba">
                <div class="prueba-item prueba-nueva">
                    <div class="prueba-header">
                        <span>Nueva prueba <span class="badge-nueva">Nueva</span></span>
                        <button type="button" class="btn-eliminar-prueba" onclick="eliminarPruebaElemento(this)">✕ Quitar</button>
                    </div>
                    <!-- ID = 0 para indicar que es nueva (no existe en BD) -->
                    <input type="hidden" name="prueba_id[]" value="0">

                    <div class="prueba-campos">
                        <div class="controlformulario">
                            <label>Cliente</label>
                            <select name="prueba_cliente_id[]">
                                <option value="">-- Selecciona un cliente --</option>
                                <?php $clientes->data_seek(0); while ($cl = $clientes->fetch_assoc()): ?>
                                    <option value="<?= $cl['id'] ?>">
                                        <?= htmlspecialchars($cl['nombre']) ?> (<?= htmlspecialchars($cl['DNI_NIE']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="controlformulario">
                            <label>Fecha</label>
                            <input type="date" name="prueba_fecha[]" value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="controlformulario">
                            <label>Hora</label>
                            <input type="time" name="prueba_hora[]">
                        </div>

                        <div class="controlformulario">
                            <label>Observaciones</label>
                            <textarea name="prueba_observaciones[]" rows="2" placeholder="Notas sobre la prueba..."></textarea>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <button type="button" class="btn-añadir-prueba" onclick="añadirNuevaPrueba()">
            + Añadir nueva prueba de manejo
        </button>
    </div>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- BOTÓN PRINCIPAL                                    -->
    <!-- ══════════════════════════════════════════════════ -->
    <input type="submit" value="Actualizar Vehículo">

</form>

<script>
// --- ACCESORIOS ---
document.querySelectorAll('.acc-check').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        var id = this.dataset.id;
        var div = document.getElementById('instalacion-' + id);
        div.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            div.querySelector('input').value = 0;
        }
    });
});

// --- PRUEBAS: añadir nueva ---
function añadirNuevaPrueba() {
    var plantilla = document.getElementById('plantilla-prueba');
    var clon = plantilla.content.cloneNode(true);
    document.getElementById('pruebas-container').appendChild(clon);
}

// --- PRUEBAS: quitar nueva antes de guardar ---
function eliminarPruebaElemento(btn) {
    btn.closest('.prueba-item').remove();
}
</script>