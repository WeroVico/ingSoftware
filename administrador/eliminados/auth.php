<?php
// auth.php
header('Content-Type: application/json');
require __DIR__ . "/funciones/conecta.php";
$con = conecta();
if (!$con) {
    error_log("auth.php: No se pudo conectar a la BD");
    echo json_encode(['existe' => false]);
    exit;
}

$correo = $_POST['correo']   ?? '';
$pass   = $_POST['password'] ?? '';

if (!$correo || !$pass) {
    error_log("auth.php: Falta correo o password");
    echo json_encode(['existe' => false]);
    exit;
}

// Consulta: busca registro aunque eliminado sea NULL o 0
$sql = "SELECT pass FROM personal_table 
        WHERE correo = ? 
          AND (eliminado = 0 OR eliminado IS NULL)
        LIMIT 1";
$stmt = $con->prepare($sql);
if (!$stmt) {
    error_log("auth.php prepare fallo: " . $con->error);
    echo json_encode(['existe' => false]);
    exit;
}
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    error_log("auth.php: Usuario no encontrado o inactivo -> $correo");
    echo json_encode(['existe' => false]);
    exit;
}

$stmt->bind_result($hash);
$stmt->fetch();
$stmt->close();

// Verifica contraseña
if (password_verify($pass, $hash)) {
    echo json_encode(['existe' => true]);
} else {
    error_log("auth.php: Password incorrecto para -> $correo");
    echo json_encode(['existe' => false]);
}
