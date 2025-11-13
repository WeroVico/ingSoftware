<?php
// lockers_get.php
require "funciones/conecta.php";
$con = conecta();

$id_modulo = $_POST['id_modulo'] ?? 0;

$sql = "SELECT id, etiqueta_completa, status FROM locker WHERE id_modulo = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_modulo);
$stmt->execute();
$result = $stmt->get_result();

$lockers = [];
while ($row = $result->fetch_assoc()) {
    $lockers[] = $row;
}

header('Content-Type: application/json');
echo json_encode($lockers);
?>