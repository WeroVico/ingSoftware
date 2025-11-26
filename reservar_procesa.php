<?php
// reservar_procesa.php
session_start();
require "funciones/conecta.php";
$con = conecta();

$response = ['success' => false, 'message' => 'Ocurrió un error inesperado.'];

// Verificaciones de seguridad
if (!isset($_SESSION['id_usuario']) || !isset($_POST['id_locker']) || !isset($_POST['duracion'])) {
    $response['message'] = 'Datos insuficientes o sesión no válida.';
    echo json_encode($response);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_locker = filter_var($_POST['id_locker'], FILTER_VALIDATE_INT);
$duracion_valor = filter_var($_POST['duracion'], FILTER_VALIDATE_INT);

// Validar duración (incluimos 5 para pruebas si quieres)
if (!in_array($duracion_valor, [2, 3, 4, 5])) {
    $response['message'] = 'La duración seleccionada no es válida.';
    echo json_encode($response);
    exit;
}

$con->begin_transaction();

try {
    // --- VALIDACIÓN DE HORARIO ---
    date_default_timezone_set('America/Mexico_City');
    $hora_actual = new DateTime();
    $hora_apertura = (new DateTime())->setTime(7, 0, 0);
    // Mantenemos horario extendido para tus pruebas
    $hora_cierre = (new DateTime())->setTime(23, 59, 59); 

    if ($hora_actual < $hora_apertura) {
        throw new Exception('No puedes reservar antes de las 7:00 AM.');
    }

    $hora_fin_reserva = clone $hora_actual;
    
    if ($duracion_valor == 5) {
        $hora_fin_reserva->add(new DateInterval("PT2M")); // Pruebas: 2 minutos
    } else {
        $hora_fin_reserva->add(new DateInterval("PT{$duracion_valor}H"));
    }
    
    // Comentado para pruebas, como pediste anteriormente
    /*
    if ($hora_fin_reserva > $hora_cierre) {
        throw new Exception('Tu reservación no puede terminar después del horario de cierre.');
    }
    */

    // 1. Verificar que el usuario no tenga otra reserva activa (CORRECCIÓN: 'reservacion')
    $sql_check_user = "SELECT id FROM reservacion WHERE id_usuario = ? AND status = 'activa' FOR UPDATE";
    $stmt_check_user = $con->prepare($sql_check_user);
    $stmt_check_user->bind_param("i", $id_usuario);
    $stmt_check_user->execute();
    if ($stmt_check_user->get_result()->num_rows > 0) {
        throw new Exception('Ya tienes una reservación activa.');
    }

    // 2. Verificar disponibilidad (CORRECCIÓN: 'locker')
    $sql_check_locker = "SELECT status FROM locker WHERE id = ? AND status = 'disponible' FOR UPDATE";
    $stmt_check_locker = $con->prepare($sql_check_locker);
    $stmt_check_locker->bind_param("i", $id_locker);
    $stmt_check_locker->execute();
    if ($stmt_check_locker->get_result()->num_rows === 0) {
        throw new Exception('El locker seleccionado ya no está disponible.');
    }

    // 3. Actualizar locker (CORRECCIÓN: 'locker')
    $sql_update_locker = "UPDATE locker SET status = 'ocupado' WHERE id = ?";
    $stmt_update_locker = $con->prepare($sql_update_locker);
    $stmt_update_locker->bind_param("i", $id_locker);
    $stmt_update_locker->execute();

    // 4. Crear reserva (CORRECCIÓN: 'reservacion')
    $fecha_fin_str = $hora_fin_reserva->format('Y-m-d H:i:s');
    $sql_insert = "INSERT INTO reservacion (id_usuario, id_locker, fecha_fin) VALUES (?, ?, ?)";
    $stmt_insert = $con->prepare($sql_insert);
    $stmt_insert->bind_param("iis", $id_usuario, $id_locker, $fecha_fin_str);
    $stmt_insert->execute();

    $con->commit();

    // Usar nuestro wrapper de logs si existe
    if(function_exists('log_nueva_reserva')) {
        log_nueva_reserva($con, $id_usuario, $id_locker, $duracion_valor, $fecha_fin_str);
    }

    $response = ['success' => true, 'message' => '¡Reservación exitosa!'];

} catch (Exception $e) {
    $con->rollback();
    $response['message'] = $e->getMessage();

    if (isset($id_usuario) && function_exists('log_error')) {
        log_error($con, $id_usuario, 'INTENTO_RESERVA_FALLIDO', $e->getMessage(), [
            'id_locker' => $id_locker
        ]);
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
?>