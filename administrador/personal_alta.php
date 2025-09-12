<?php
session_start();
// personal_alta.php
require_once 'menu.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $apellidos = $_POST["apellidos"];
    $correo = $_POST["correo"];
    $contraseña = $_POST["pass"];
    $rol = $_POST["rol"];

    // Convertir valores numéricos a texto en el select   
    $roles = ["0" => "No seleccionada", "1" => "Gerente", "2" => "Empleado"];
    $rolTexto = isset($roles[$rol]) ? $roles[$rol] : "Desconocida";


    echo "<h2>Datos recibidos</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>Nombre</td><td>$nombre</td></tr>";
    echo "<tr><td>Apellidos</td><td>$apellidos</td></tr>";
    echo "<tr><td>Correo</td><td>$correo</td></tr>";
    echo "<tr><td>Rol</td><td>$rolTexto</td></tr>";
    echo "<tr><td>Contraseña</td><td>********</td></tr>"; // Ocultar contraseña por seguridad
    echo "</table>";
?>
<html>
    <br>
    <a href="personal_lista.php">Continuar</a>
</html>

<?php
} else {
    echo "<h2>No se recibió información.</h2>";
}
?>

