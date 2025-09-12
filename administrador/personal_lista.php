<html>
<!--personal_lista.php-->
<head>
<script src="jquery-3.3.1.min.js"></script>
<script>
    function eliminarEmpleado(id) {
        if (confirm("¿Estás seguro de que deseas eliminar este empleado?"))
        {
            $.ajax({
                url: 'personal_elimina.php',
                type: 'POST',
                data: { id: id },
                success: function(response) {
                    if (response.trim() === "success") {
                        location.reload();
                    } else {``
                        alert("Error al eliminar el empleado.");
                    }
                },
                error: function() {
                    alert("Hubo un error en la solicitud.");
                }
            });
        }
    }

   
</script>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f3f3f3;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 30px;
    }

    .tabla {
        width: 80%;
        border-collapse: collapse;
        background-color: rgb(210, 241, 223);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }

    .tabla th, .tabla td {
        border: 1px solid #ddd;
        padding: 10px 15px;
        text-align: center;
    }

    .tabla th {
        background-color: #4CAF50;
        color: white;
    }


    .tabla a {
        color:rgb(5, 57, 126);
        font-weight: bold;
    }
    #detalleLink{
        color:rgb(14, 133, 30);
        font-weight: bold;
    }
    .nuevoEmp {
        border: none; 
        color: green; 
        padding: 14px 28px; 
        cursor: pointer; 
        border-radius: 5px; 
        background-color: #007bff;
    }
    .nuevoEmp:hover {background: #0b7dda;}


    button {background-color: white; border: 2px solid #ff9800;} 
    button:hover {background:rgb(199, 77, 6); color: white;}
</style>
</head>
</html>

<?php
//Personal_lista.php
require_once "menu.php";
require "funciones/conecta.php";
$con = conecta();

$sql = "SELECT * FROM personal_table WHERE eliminado = 0";
$res = $con->query($sql);
$num = $res->num_rows;

echo "<div id='cuerpo_tabla' align='center'>";
    echo "
    <div class='nuevoEmp'><a href='personal_salva.php' id='nuevoEmp'><h2><i>Dar de alta</h2></i></a><br> </div>";
    echo "<table class='tabla' border='2' align='center'>";
    echo "<thead>
            <tr>
                <th>ID</th>
                <th>Nombre completo</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Ver detalle</th>
                <th>Editar</th>
                <th>Eliminar</th>
            </tr>
        </thead>";
    echo "<tbody>";

echo "</div>";


while($row = $res->fetch_array()) {
    $id         = $row["id"];
    $nombre     = $row["nombre"];
    $apellidos  = $row["apellidos"];
    $correo     = $row["correo"];
    $rol        = ($row["rol"] == 1) ? "Gerente" : "Empleado";

    echo "<tr>
      <td>$id</td>
      <td>$nombre $apellidos</td>
      <td>$correo</td>
      <td>$rol</td>
      <td>
        <a href='personal_detalle.php?id=$id' id='detalleLink'>Mostrar</a>
      </td>
      <td>
        <a href='personal_editar.php?id=" . $id . "' class='btn-editar'>Editar</a>
      </td>
      <td>
        <button onclick='eliminarEmpleado($id)'>Eliminar</button>
      </td>
    </tr>";
}

echo "</tbody>";
echo "</table>";

echo "<br><br>";
echo "<a href=\"personal_reestablecer!.php\"> RECUPERAR TODO <br>(función para test) </a>";



?>


