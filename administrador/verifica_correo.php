<?php
// verifica_correo.php
require "funciones/conecta.php";
$con = conecta();

$correo = $_POST['correo'] ?? '';
$id_actual = $_POST['id_actual'] ?? 0;

$stmt = $con->prepare("SELECT id FROM personal_table 
                      WHERE correo = ? 
                      AND id != ? 
                      AND eliminado = 0");
$stmt->bind_param("si", $correo, $id_actual);
$stmt->execute();
$stmt->store_result();

header('Content-Type: application/json');
echo json_encode(['existe' => $stmt->num_rows > 0]);
$stmt->close();
?>