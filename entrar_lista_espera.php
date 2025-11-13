<?php
// entrar_lista_espera.php
session_start();
require "funciones/conecta.php";
$con = conecta();

$response = ['success' => false, 'message' => 'Error inesperado.'];

if (!isset($_SESSION['id_usuario']) || !isset($_POST['id_modulo'])) {
    $response['message'] = 'Datos insuficientes o sesión no válida.';
    echo json_encode($response);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_modulo = filter_var($_POST['id_modulo'], FILTER_VALIDATE_INT);

// Verificar que el usuario no tenga ya una reserva activa
$stmt_reserva = $con->prepare("SELECT id FROM reservacion WHERE id_usuario = ? AND status = 'activa'");
$stmt_reserva->bind_param("i", $id_usuario);
$stmt_reserva->execute();
if ($stmt_reserva->get_result()->num_rows > 0) {
    $response['message'] = 'Ya tienes un locker reservado. No puedes unirte a una lista de espera.';
    echo json_encode($response);
    exit;
}

// Insertar al usuario en la lista de espera
// Insertar o reactivar al usuario en la lista de espera
$sql = "INSERT INTO lista_espera (id_usuario, id_modulo, status)
        VALUES (?, ?, 'pendiente')
        ON DUPLICATE KEY UPDATE status = 'pendiente'";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $id_usuario, $id_modulo);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Has sido agregado (o reactivado) en la lista de espera.';
} else {
    $response['message'] = 'No se pudo procesar tu solicitud en este momento.';
}



header('Content-Type: application/json');
echo json_encode($response);
?>