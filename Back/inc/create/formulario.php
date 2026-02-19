<?php
include __DIR__ . "/../db.php";

// 1. Obtener marcas y modelos para los <select>
$marcas = $conexion->query("SELECT id, nombre FROM marca ORDER BY nombre ASC");
$modelos = $conexion->query("SELECT id, nombre FROM modelo ORDER BY nombre ASC");
?>

<form action="inc/create/procesaformulario.php" method="POST">

    <!-- Marca (Foreign Key) -->
    <div class="controlformulario">
        <label for="marca_id">Marca</label>
        <select name="marca_id" id="marca_id" required>
            <option value="">-- Selecciona una marca --</option>
            <?php while($m = $marcas->fetch_assoc()): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Modelo (Foreign Key) -->
    <div class="controlformulario">
        <label for="modelo_id">Modelo</label>
        <select name="modelo_id" id="modelo_id" required>
            <option value="">-- Selecciona un modelo --</option>
            <?php while($mo = $modelos->fetch_assoc()): ?>
                <option value="<?= $mo['id'] ?>"><?= htmlspecialchars($mo['nombre']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Año (CHECK 1900-2100) -->
    <div class="controlformulario">
        <label for="año">Año</label>
        <input type="number" name="año" id="año" min="1900" max="2100" 
               value="<?= date('Y') ?>" required>
    </div>

    <!-- VIN (Unique, 17 chars) -->
    <div class="controlformulario">
        <label for="vin">VIN (Chasis)</label>
        <input type="text" name="vin" id="vin" maxlength="17" pattern="[A-HJ-NPR-Z0-9]{17}" 
               placeholder="17 caracteres alfanuméricos" required 
               title="El VIN debe tener 17 caracteres sin I, O o Q">
    </div>

    <!-- Color -->
    <div class="controlformulario">
        <label for="color">Color</label>
        <input type="text" name="color" id="color" maxlength="50" required>
    </div>

    <!-- Precio (Decimal) -->
    <div class="controlformulario">
        <label for="precio">Precio (€)</label>
        <input type="number" name="precio" id="precio" step="0.01" min="0" required>
    </div>

    <!-- Estado (Enum: nuevo/usado) -->
    <div class="controlformulario">
        <label for="estado">Estado</label>
        <select name="estado" id="estado" required>
            <option value="nuevo">Nuevo</option>
            <option value="usado">Usado</option>
        </select>
    </div>

    <!-- Kilometraje (Default 0) -->
    <div class="controlformulario">
        <label for="kilometraje">Kilometraje</label>
        <input type="number" name="kilometraje" id="kilometraje" min="0" value="0">
    </div>

    <!-- Fecha Ingreso (Opcional, por defecto hoy) -->
    <div class="controlformulario">
        <label for="fecha_ingreso">Fecha de Ingreso</label>
        <input type="date" name="fecha_ingreso" id="fecha_ingreso" 
               value="<?= date('Y-m-d') ?>">
    </div>

    <input type="submit" value="Registrar Vehículo">
</form>