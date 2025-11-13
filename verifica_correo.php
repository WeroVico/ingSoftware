<?php
// verifica_correo.php
require "funciones/conecta.php";
$con = conecta();

$correo = $_POST['correo'] ?? '';

$stmt = $con->prepare("SELECT id FROM usuario WHERE correo = ? AND eliminado = 0");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->store_result();

header('Content-Type: application/json');
echo json_encode(['existe' => $stmt->num_rows > 0]);
$stmt->close();
?>