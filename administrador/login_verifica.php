<?php
session_start();
require "funciones/conecta.php";
$con = conecta();

$correo = $_POST['correo'] ?? '';
$pass = $_POST['pass'] ?? '';
$response = ['existe' => false];

if (!empty($correo) && !empty($pass)) {
    // Consulta preparada que incluya nombre y correo
    $stmt = $con->prepare("SELECT id, nombre, correo, pass FROM personal_table 
                          WHERE correo = ? AND eliminado = 0");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        if (password_verify($pass, $usuario['pass'])) {
            // Asignar TODAS las variables de sesión necesarias
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre']; // Obligatorio
            $_SESSION['correo'] = $usuario['correo']; // Obligatorio
            $response['existe'] = true;
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>