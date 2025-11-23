<?php
// login_verifica.php
session_start();
require "funciones/conecta.php";
$con = conecta();

$correo = $_POST['correo'] ?? '';
$pass = $_POST['pass'] ?? '';
$response = ['existe' => false];

if (!empty($correo) && !empty($pass)) {
    // Añadimos 'rol' a la consulta
    $stmt = $con->prepare("SELECT id, nombre, correo, pass, rol FROM usuario WHERE correo = ? AND eliminado = 0");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        if (password_verify($pass, $usuario['pass'])) {
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['rol'] = $usuario['rol']; // Guardamos el rol en la sesión
            
            log_acceso($con, $usuario['id']);
            $response['existe'] = true;
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>