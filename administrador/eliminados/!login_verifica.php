<?php
// login_verifica.php
require "funciones/conecta.php";
$con = conecta();

$correo = $_POST['correo'] ?? '';
$pass = $_POST['pass'] ?? '';

$response = ['existe' => false];

// 1. Buscar usuario activo
$stmt = $con->prepare("SELECT id, pass FROM personal_table 
                      WHERE correo = ? AND eliminado = 0");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // 2. Verificar contraseña
    if (password_verify($pass, $row['pass'])) {
        session_start();
        $_SESSION['id_usuario'] = $row['id'];
        $response['existe'] = true;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>