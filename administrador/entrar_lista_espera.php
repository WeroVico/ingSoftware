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
$stmt_reserva = $con->prepare("SELECT id FROM reservaciones WHERE id_usuario = ? AND status = 'activa'");
$stmt_reserva->bind_param("i", $id_usuario);
$stmt_reserva->execute();
if ($stmt_reserva->get_result()->num_rows > 0) {
    $response['message'] = 'Ya tienes un locker reservado. No puedes unirte a una lista de espera.';
    echo json_encode($response);
    exit;
}

// Insertar al usuario en la lista de espera
// La UNIQUE KEY en la BD previene que se inserte si ya está en la lista para ese módulo
$sql = "INSERT INTO lista_espera (id_usuario, id_modulo, status) VALUES (?, ?, 'pendiente')";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $id_usuario, $id_modulo);

if ($stmt->execute()) {
    $response['success'] = true;
} else {
    // MySQL error 1062 es para entradas duplicadas
    if ($con->errno == 1062) {
        $response['message'] = 'Ya estás en la lista de espera para este módulo.';
    } else {
        $response['message'] = 'No se pudo procesar tu solicitud en este momento.';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>