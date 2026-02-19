<table>
  <!-- Encabezados de la tabla (necesarios para que el CSS funcione bien) -->
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
    include __DIR__ . "/../db.php"; 

    $sql = "
      SELECT 
        v.id,
        v.vin,
        v.año,
        v.precio,
        v.estado,
        m.nombre as marca_nombre,
        mo.nombre as modelo_nombre
      FROM vehiculo v
      LEFT JOIN marca m ON v.marca_id = m.id
      LEFT JOIN modelo mo ON v.modelo_id = mo.id
      ORDER BY v.id ASC
    ";

    $resultado = $conexion->query($sql);

    while ($fila = $resultado->fetch_assoc()) {
      echo "<tr>";
        echo "<td>" . htmlspecialchars($fila['marca_nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['modelo_nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['año']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['vin']) . "</td>";
        echo "<td>$" . number_format($fila['precio'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($fila['estado']) . "</td>";
        echo "<td>
                <a href='?accion=editar&id=" . $fila['id'] . "' class='editar' title='Editar vehículo'>✎</a>
                <a href='?accion=eliminar&id=" . $fila['id'] . "' class='eliminar' title='Eliminar vehículo'>✖</a>
              </td>";
      echo "</tr>";
    }

    $conexion->close();
    ?>
  </tbody>
</table>