<?php
// Script temporal para hashear contraseñas viejas (ejecutar una vez):
require "funciones/conecta.php";
$con = conecta();

$result = $con->query("SELECT id, pass FROM personal_table");
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $newPass = password_hash($row['pass'], PASSWORD_DEFAULT);
    $con->query("UPDATE personal_table SET pass = '$newPass' WHERE id = $id");
}
?>