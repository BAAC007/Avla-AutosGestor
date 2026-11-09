<?php
// Back/inc/util.php
// Listas de valores permitidos (deben coincidir con los CHECK de la BD) y validadores.

const COMBUSTIBLES     = ['gasolina', 'diésel', 'híbrido', 'híbrido enchufable', 'eléctrico', 'GLP', 'GNC'];
const TRANSMISIONES    = ['manual', 'automático'];
const ETIQUETAS_DGT    = ['0', 'ECO', 'C', 'B', 'SIN'];
const DISPONIBILIDADES = ['disponible', 'reservado', 'vendido'];

/** Devuelve 'Y-m-d' si $raw es una fecha real; si no, $defecto. */
function fecha_valida(?string $raw, ?string $defecto = null): ?string
{
    $raw = trim((string)$raw);
    $d = DateTime::createFromFormat('Y-m-d', $raw);
    return ($d && $d->format('Y-m-d') === $raw) ? $raw : $defecto;
}

/** Devuelve 'HH:MM:SS' si $raw es una hora válida (HH:MM o HH:MM:SS); si no, null. */
function hora_valida(?string $raw): ?string
{
    $raw = trim((string)$raw);
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::([0-5]\d))?$/', $raw, $m)) {
        return null;
    }
    return sprintf('%s:%s:%s', $m[1], $m[2], $m[3] ?? '00');
}

/** Entero opcional dentro de un rango; '' => null. Lanza si está fuera de rango. */
function entero_opcional($v, int $min, int $max): ?int
{
    if ($v === null || trim((string)$v) === '') {
        return null;
    }
    if (!is_numeric($v) || (int)$v < $min || (int)$v > $max) {
        throw new InvalidArgumentException('entero fuera de rango');
    }
    return (int)$v;
}

/** Valor de una lista cerrada; '' => $defecto. Lanza si no está en la lista. */
function opcion_lista($v, array $permitidos, ?string $defecto = null): ?string
{
    $v = trim((string)$v);
    if ($v === '') {
        return $defecto;
    }
    if (!in_array($v, $permitidos, true)) {
        throw new InvalidArgumentException('valor no permitido');
    }
    return $v;
}

/**
 * Valida y normaliza los campos de ficha técnica de un vehículo desde $_POST.
 * Lanza InvalidArgumentException si algún dato no es válido.
 */
function vehiculo_ficha_desde_post(array $p): array
{
    $matricula = strtoupper(preg_replace('/[\s-]+/', '', trim((string)($p['matricula'] ?? ''))));
    if ($matricula !== '' && !preg_match('/^[A-Z0-9]{4,10}$/', $matricula)) {
        throw new InvalidArgumentException('matrícula no válida');
    }
    $descripcion = trim((string)($p['descripcion'] ?? ''));
    if (mb_strlen($descripcion) > 2000) {
        throw new InvalidArgumentException('descripción demasiado larga');
    }

    return [
        'matricula'              => $matricula === '' ? null : $matricula,
        'combustible'            => opcion_lista($p['combustible'] ?? '', COMBUSTIBLES),
        'transmision'            => opcion_lista($p['transmision'] ?? '', TRANSMISIONES),
        'potencia_cv'            => entero_opcional($p['potencia_cv'] ?? '', 1, 2000),
        'puertas'                => entero_opcional($p['puertas'] ?? '', 1, 9),
        'plazas'                 => entero_opcional($p['plazas'] ?? '', 1, 60),
        'etiqueta_dgt'           => opcion_lista($p['etiqueta_dgt'] ?? '', ETIQUETAS_DGT),
        'propietarios_anteriores'=> entero_opcional($p['propietarios_anteriores'] ?? '', 0, 20) ?? 0,
        'garantia_meses'         => entero_opcional($p['garantia_meses'] ?? '', 0, 120) ?? 0,
        'descripcion'            => $descripcion === '' ? null : $descripcion,
        'disponibilidad'         => opcion_lista($p['disponibilidad'] ?? '', DISPONIBILIDADES, 'disponible'),
    ];
}
