<?php
// admin_update_locker.php
session_start();
require "funciones/conecta.php";
require "funciones/enviar_correo.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

$id_locker = $_POST['id_locker'] ?? 0;
$status = $_POST['status'] ?? '';
$valid_statuses = ['disponible', 'ocupado', 'mantenimiento'];

if ($id_locker && in_array($status, $valid_statuses)) {
    $con = conecta();
    $con->begin_transaction();

    try {
        // Si se libera un locker, también debemos cancelar la reserva asociada.
        if ($status === 'disponible') {
            $sql_cancel = "UPDATE reservacion SET status = 'cancelada' WHERE id_locker = ? AND status = 'activa'";
            $stmt_cancel = $con->prepare($sql_cancel);
            $stmt_cancel->bind_param("i", $id_locker);
            $stmt_cancel->execute();
        }

        // Actualizar el estado del locker
        $sql = "UPDATE locker SET status = ? WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("si", $status, $id_locker);
        $stmt->execute();
        
        $con->commit();

        registrar_log($con, $_SESSION['id_usuario'], 'ADMIN_CAMBIO_ESTADO', [
        'id_locker_modificado' => $id_locker,
        'nuevo_estado' => $status,
        'nota' => 'Cambio forzado por administrador'
         ]);

        // Si se liberó un locker, notificar al siguiente en la lista de espera
        if ($status === 'disponible') {
            $stmt_modulo = $con->prepare("SELECT id_modulo FROM locker WHERE id = ?");
            $stmt_modulo->bind_param("i", $id_locker);
            $stmt_modulo->execute();
            $result = $stmt_modulo->get_result();
            if ($result->num_rows > 0) {
                $id_modulo = $result->fetch_assoc()['id_modulo'];
                if ($id_modulo) {
                    notificarUsuarioEnEspera($id_modulo, $con);
                }
            }
        }
        
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $con->rollback();
        echo json_encode(['success' => false, 'message' => 'Error en la transacción.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
}
?>