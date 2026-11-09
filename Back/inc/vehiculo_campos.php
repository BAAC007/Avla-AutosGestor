<?php
// Back/inc/vehiculo_campos.php
// Campos de ficha técnica del vehículo, compartidos por los formularios de crear y editar.
// Espera opcionalmente un array $fila con los valores actuales (editar).
require_once __DIR__ . '/util.php';

$f   = isset($fila) && is_array($fila) ? $fila : [];
$val = fn(string $k, $d = '') => htmlspecialchars((string)($f[$k] ?? $d));
$sel = fn(string $k, string $op, string $d = '') => ((string)($f[$k] ?? $d) === $op) ? 'selected' : '';
?>
<div class="controlformulario">
    <label for="disponibilidad">Disponibilidad</label>
    <select name="disponibilidad" id="disponibilidad">
        <?php foreach (DISPONIBILIDADES as $op): ?>
            <option value="<?= $op ?>" <?= $sel('disponibilidad', $op, 'disponible') ?>><?= ucfirst($op) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="controlformulario">
    <label for="matricula">Matrícula (si está matriculado)</label>
    <input type="text" name="matricula" id="matricula" maxlength="10" value="<?= $val('matricula') ?>">
</div>

<div class="controlformulario">
    <label for="combustible">Combustible</label>
    <select name="combustible" id="combustible">
        <option value="">-- Sin especificar --</option>
        <?php foreach (COMBUSTIBLES as $op): ?>
            <option value="<?= htmlspecialchars($op) ?>" <?= $sel('combustible', $op) ?>><?= htmlspecialchars(ucfirst($op)) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="controlformulario">
    <label for="transmision">Transmisión</label>
    <select name="transmision" id="transmision">
        <option value="">-- Sin especificar --</option>
        <?php foreach (TRANSMISIONES as $op): ?>
            <option value="<?= htmlspecialchars($op) ?>" <?= $sel('transmision', $op) ?>><?= htmlspecialchars(ucfirst($op)) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="controlformulario">
    <label for="potencia_cv">Potencia (CV)</label>
    <input type="number" name="potencia_cv" id="potencia_cv" min="1" max="2000" value="<?= $val('potencia_cv') ?>">
</div>

<div class="controlformulario">
    <label for="puertas">Puertas</label>
    <input type="number" name="puertas" id="puertas" min="1" max="9" value="<?= $val('puertas') ?>">
</div>

<div class="controlformulario">
    <label for="plazas">Plazas</label>
    <input type="number" name="plazas" id="plazas" min="1" max="60" value="<?= $val('plazas') ?>">
</div>

<div class="controlformulario">
    <label for="etiqueta_dgt">Etiqueta ambiental DGT</label>
    <select name="etiqueta_dgt" id="etiqueta_dgt">
        <option value="">-- Sin especificar --</option>
        <?php foreach (ETIQUETAS_DGT as $op): ?>
            <option value="<?= $op ?>" <?= $sel('etiqueta_dgt', $op) ?>><?= $op === 'SIN' ? 'Sin etiqueta' : $op ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="controlformulario">
    <label for="propietarios_anteriores">Propietarios anteriores</label>
    <input type="number" name="propietarios_anteriores" id="propietarios_anteriores" min="0" max="20"
           value="<?= $val('propietarios_anteriores', 0) ?>">
</div>

<div class="controlformulario">
    <label for="garantia_meses">Garantía (meses)</label>
    <input type="number" name="garantia_meses" id="garantia_meses" min="0" max="120"
           value="<?= $val('garantia_meses', 0) ?>">
</div>

<div class="controlformulario">
    <label for="descripcion">Descripción</label>
    <textarea name="descripcion" id="descripcion" rows="4" maxlength="2000"><?= $val('descripcion') ?></textarea>
</div>
