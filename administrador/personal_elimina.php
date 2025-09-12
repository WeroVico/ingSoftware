<?php
// personal_elimina.php
require "funciones/conecta.php";
$con = conecta();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    $sql = "UPDATE personal_table SET eliminado = 1 WHERE id = $id";
    if ($con->query($sql) === TRUE) {
        echo "success"; // Respuesta para AJAX
    } else {
        echo "error";
    }
}
?>
