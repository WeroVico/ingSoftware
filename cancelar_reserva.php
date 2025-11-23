<?php
// cancelar_reserva.php
session_start();
require "funciones/conecta.php";
require "funciones/enviar_correo.php";
$con = conecta();

$response = ['success' => false, 'message' => 'Ocurrió un error.'];

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Sesión no válida.';
    echo json_encode($response);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$con->begin_transaction();

try {
    // 1. Encontrar la reservación activa y el ID del locker
    $sql_find = "SELECT r.id, r.id_locker, l.id_modulo 
                 FROM reservacion r
                 JOIN locker l ON r.id_locker = l.id
                 WHERE r.id_usuario = ? AND r.status = 'activa' FOR UPDATE";
                 
    $stmt_find = $con->prepare($sql_find);
    $stmt_find->bind_param("i", $id_usuario);
    $stmt_find->execute();
    $res = $stmt_find->get_result()->fetch_assoc();

    if (!$res) {
        throw new Exception('No se encontró una reservación activa.');
    }
    
    $id_reserva = $res['id'];
    $id_locker = $res['id_locker'];
    $id_modulo = $res['id_modulo'];

    // 2. Actualizar el estado de la reservación a 'cancelada'
    $sql_update_res = "UPDATE reservacion SET status = 'cancelada' WHERE id = ?";
    $stmt_update_res = $con->prepare($sql_update_res);
    $stmt_update_res->bind_param("i", $id_reserva);
    $stmt_update_res->execute();

    // 3. Actualizar el estado del locker a 'disponible'
    $sql_update_locker = "UPDATE locker SET status = 'disponible' WHERE id = ?";
    $stmt_update_locker = $con->prepare($sql_update_locker);
    $stmt_update_locker->bind_param("i", $id_locker);
    $stmt_update_locker->execute();

    $con->commit();

    //  4. Llevar registro (logs)
    registrar_log($con, $id_usuario, 'CANCELACION_RESERVA', [
        'id_reserva_cancelada' => $id_reserva,
        'locker_liberado' => $id_locker,
        'modulo' => $id_modulo,
        'razon' => 'Cancelación manual por usuario'
    ]);
    
    // 5. Notificar al siguiente en la lista de espera para ese módulo
    if ($id_modulo) {
        notificarUsuarioEnEspera($id_modulo, $con);
    }
    
    $response = ['success' => true];

} catch (Exception $e) {
    $con->rollback();
    $response['message'] = $e->getMessage();

    if (isset($id_usuario)) {
        log_error($con, $id_usuario, 'INTENTO_CANCELACION_FALLIDO', $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>