<?php
include dirname(__DIR__, 2) . "/db.php";

// Validar que el ID exista y sea numérico
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ ID de vehículo no válido");
}

// Sanitizar el ID antes de usarlo en la consulta
$id = intval($_GET['id']);

// Traemos el vehículo específico a editar
$sql = "SELECT * FROM vehiculo WHERE id = $id;";
$resultado = $conexion->query($sql);

// Verificar que la consulta devolvió resultados
if (!$resultado || $resultado->num_rows === 0) {
    die("❌ Vehículo no encontrado");
}

// Obtener listas de marcas y modelos para los <select> 
$marcas = $conexion->query("SELECT id, nombre FROM marca ORDER BY nombre ASC");
$modelos = $conexion->query("SELECT id, nombre FROM modelo ORDER BY nombre ASC");

while ($fila = $resultado->fetch_assoc()) {
?>

    <!-- Agregar campo oculto con el ID para que el procesador sepa qué actualizar -->
    <form action="inc/update/procesaformulario.php" method="POST">
        <input type="hidden" name="id_vehiculo" value="<?= $fila['id'] ?>">

        <!-- Marca (Foreign Key) -->
        <div class="controlformulario">
            <label for="marca_id">Marca</label>
            <select name="marca_id" id="marca_id" required>
                <option value="">-- Selecciona una marca --</option>
                <?php 
                // Resetear el pointer de $marcas por si se usó antes
                $marcas->data_seek(0);
                while ($m = $marcas->fetch_assoc()): 
                    // Agregar 'selected' si esta marca es la del vehículo
                    $selected = ($m['id'] == $fila['marca_id']) ? 'selected' : '';
                ?>
                    <option value="<?= $m['id'] ?>" <?= $selected ?>>
                        <?= htmlspecialchars($m['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Modelo (Foreign Key) -->
        <div class="controlformulario">
            <label for="modelo_id">Modelo</label>
            <select name="modelo_id" id="modelo_id" required>
                <option value="">-- Selecciona un modelo --</option>
                <?php 
                // Resetear pointer y agregar 'selected'
                $modelos->data_seek(0);
                while ($mo = $modelos->fetch_assoc()): 
                    $selected = ($mo['id'] == $fila['modelo_id']) ? 'selected' : '';
                ?>
                    <option value="<?= $mo['id'] ?>" <?= $selected ?>>
                        <?= htmlspecialchars($mo['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Año -->
        <div class="controlformulario">
            <label for="año">Año</label>
            <!-- Usar el valor guardado ($fila['año']) en lugar de date('Y') -->
            <input type="number" name="año" id="año" min="1900" max="2100"
                value="<?= htmlspecialchars($fila['año']) ?>" required>
        </div>

        <!-- VIN (Unique, 17 chars) -->
        <div class="controlformulario">
            <label for="vin">VIN (Chasis)</label>
            <!-- Precargar el VIN existente -->
            <input type="text" name="vin" id="vin" maxlength="17" pattern="[A-HJ-NPR-Z0-9]{17}"
                placeholder="17 caracteres alfanuméricos" required
                title="El VIN debe tener 17 caracteres sin I, O o Q"
                value="<?= htmlspecialchars($fila['vin']) ?>">
        </div>

        <!-- Color -->
        <div class="controlformulario">
            <label for="color">Color</label>
            <!-- Precargar el color existente -->
            <input type="text" name="color" id="color" maxlength="50" required
                value="<?= htmlspecialchars($fila['color']) ?>">
        </div>

        <!-- Precio (Decimal) -->
        <div class="controlformulario">
            <label for="precio">Precio (€)</label>
            <!-- Precargar el precio existente -->
            <input type="number" name="precio" id="precio" step="0.01" min="0" required
                value="<?= htmlspecialchars($fila['precio']) ?>">
        </div>

        <!-- Estado (Enum: nuevo/usado) -->
        <div class="controlformulario">
            <label for="estado">Estado</label>
            <select name="estado" id="estado" required>
                <!-- Agregar 'selected' según el estado guardado -->
                <option value="nuevo" <?= $fila['estado'] == 'nuevo' ? 'selected' : '' ?>>Nuevo</option>
                <option value="usado" <?= $fila['estado'] == 'usado' ? 'selected' : '' ?>>Usado</option>
            </select>
        </div>

        <!-- Kilometraje (Default 0) -->
        <div class="controlformulario">
            <label for="kilometraje">Kilometraje</label>
            <!-- Precargar el kilometraje existente -->
            <input type="number" name="kilometraje" id="kilometraje" min="0"
                value="<?= htmlspecialchars($fila['kilometraje']) ?>">
        </div>

        <!-- Fecha Ingreso (Opcional, por defecto hoy) -->
        <div class="controlformulario">
            <label for="fecha_ingreso">Fecha de Ingreso</label>
            <!-- Precargar la fecha existente -->
            <input type="date" name="fecha_ingreso" id="fecha_ingreso"
                value="<?= htmlspecialchars($fila['fecha_ingreso']) ?>">
        </div>

        <input type="submit" value="Actualizar Vehículo">
    </form>

<?php
}
?>