<table>
<thead>
  <tr>
    <th>Marca</th>
    <th>Modelo</th>
    <th>Año</th>
    <th>VIN</th>
    <th>Precio</th>
    <th>Estado</th>
    <th>Acciones</th>
  </tr>
</thead>
<tbody>
<?php
include dirname(__DIR__, 2) . "/db.php";

$sql = "SELECT v.id, v.vin, v.año, v.precio, v.estado, 
               m.nombre as marca_nombre, mo.nombre as modelo_nombre
        FROM vehiculo v
        LEFT JOIN marca m ON v.marca_id = m.id
        LEFT JOIN modelo mo ON v.modelo_id = mo.id
        ORDER BY v.id ASC";

$resultado = $conexion->query($sql);

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".htmlspecialchars($fila['marca_nombre'])."</td>";
        echo "<td>".htmlspecialchars($fila['modelo_nombre'])."</td>";
        echo "<td>".htmlspecialchars($fila['año'])."</td>";
        echo "<td>".htmlspecialchars($fila['vin'])."</td>";
        echo "<td>$".number_format($fila['precio'], 2)."</td>";
        echo "<td>".htmlspecialchars($fila['estado'])."</td>";
        echo "<td>
                <a href='?accion=editar&id=".$fila['id']."' class='editar'>✎</a>
                <a href='?accion=eliminar&id=".$fila['id']."' class='eliminar'>✖</a>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>Error en la consulta: ".$conexion->error."</td></tr>";
}

$conexion->close();
?>
</tbody>
</table>