<?php
// extender_reserva.php
session_start();
require "funciones/conecta.php";
$con = conecta();

$response = ['success' => false, 'message' => 'Error inesperado.'];

if (!isset($_SESSION['id_usuario']) || !isset($_POST['horas'])) {
    $response['message'] = 'Datos insuficientes.';
    echo json_encode($response);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$horas_a_extender = filter_var($_POST['horas'], FILTER_VALIDATE_INT);

if (!in_array($horas_a_extender, [1, 2])) {
    $response['message'] = 'Número de horas no válido.';
    echo json_encode($response);
    exit;
}

$con->begin_transaction();

try {
    // 1. Obtener la reservación activa actual para verificarla
    $sql_find = "SELECT id, fecha_fin FROM reservacion WHERE id_usuario = ? AND status = 'activa' FOR UPDATE";
    $stmt_find = $con->prepare($sql_find);
    $stmt_find->bind_param("i", $id_usuario);
    $stmt_find->execute();
    $res = $stmt_find->get_result()->fetch_assoc();

    if (!$res) {
        throw new Exception('No se encontró una reservación activa para extender.');
    }
    
    $id_reserva = $res['id'];
    $fecha_fin_actual = new DateTime($res['fecha_fin']);

    // 2. Calcular nueva fecha de fin y validar el horario
    date_default_timezone_set('America/Mexico_City');
    $hora_cierre = (new DateTime())->setTime(21, 0, 0);
    $nueva_fecha_fin = (clone $fecha_fin_actual)->add(new DateInterval("PT{$horas_a_extender}H"));

    if ($nueva_fecha_fin > $hora_cierre) {
        throw new Exception('La extensión excede el horario de cierre de las 9:00 PM.');
    }

    // 3. Actualizar la fecha de fin en la base de datos
    $nueva_fecha_fin_str = $nueva_fecha_fin->format('Y-m-d H:i:s');
    $sql_update = "UPDATE reservacion SET fecha_fin = ? WHERE id = ?";
    $stmt_update = $con->prepare($sql_update);
    $stmt_update->bind_param("si", $nueva_fecha_fin_str, $id_reserva);
    $stmt_update->execute();

    $con->commit();
    $response['success'] = true;

} catch (Exception $e) {
    $con->rollback();
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>