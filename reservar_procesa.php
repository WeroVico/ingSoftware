<?php
// reservar_procesa.php
session_start();
require "funciones/conecta.php";
$con = conecta();

$response = ['success' => false, 'message' => 'Ocurrió un error inesperado.'];

// Verificaciones de seguridad
if (!isset($_SESSION['id_usuario']) || !isset($_POST['id_locker']) || !isset($_POST['duracion'])) {
    $response['message'] = 'Datos insuficientes o sesión no válida.';
    header('Content-Type: application/json; charset=utf-8'); // Añadido aquí por si falla temprano
    echo json_encode($response);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_locker = filter_var($_POST['id_locker'], FILTER_VALIDATE_INT);
$duracion_valor = filter_var($_POST['duracion'], FILTER_VALIDATE_INT);

// Validar usando el '5' del HTML (para 2 minutos)
if (!in_array($duracion_valor, [2, 3, 4, 5])) { 
    $response['message'] = 'La duración seleccionada no es válida.';
    header('Content-Type: application/json; charset=utf-8'); // Añadido aquí por si falla temprano
    echo json_encode($response);
    exit;
}

// Iniciar transacción
$con->begin_transaction();

try {
    // --- VALIDACIÓN DE HORARIO ---
    date_default_timezone_set('America/Mexico_City'); // Aseguramos la zona horaria correcta
    $hora_actual = new DateTime();
    $hora_apertura = (new DateTime())->setTime(7, 0, 0);
    $hora_cierre = (new DateTime())->setTime(21, 0, 0);

    // No se puede reservar antes de las 7am
    if ($hora_actual < $hora_apertura) {
        throw new Exception('No puedes reservar antes de las 7:00 AM.');
    }

    // Lógica condicional para calcular la hora de fin
    $hora_fin_reserva = clone $hora_actual; // Clonamos la hora actual
    
    if ($duracion_valor == 5) {
        // Es el caso especial de 2 minutos
        $hora_fin_reserva->add(new DateInterval("PT2M")); // M = Minutos
    } else {
        // Son los casos normales de 2, 3, o 4 horas
        $hora_fin_reserva->add(new DateInterval("PT{$duracion_valor}H")); // H = Horas
    }
    
    if ($hora_fin_reserva > $hora_cierre) {
        throw new Exception('Tu reservación no puede terminar después de las 9:00 PM.');
    }
    // --- FIN DE LA VALIDACIÓN DE HORARIO ---

    // 1. Verificar que el usuario no tenga otra reserva activa
    $sql_check_user = "SELECT id FROM reservacion WHERE id_usuario = ? AND status = 'activa' FOR UPDATE";
    $stmt_check_user = $con->prepare($sql_check_user);
    $stmt_check_user->bind_param("i", $id_usuario);
    $stmt_check_user->execute();
    if ($stmt_check_user->get_result()->num_rows > 0) {
        throw new Exception('Ya tienes una reservación activa.');
    }

    // 2. Verificar que el locker esté disponible
    $sql_check_locker = "SELECT status FROM locker WHERE id = ? AND status = 'disponible' FOR UPDATE";
    $stmt_check_locker = $con->prepare($sql_check_locker);
    $stmt_check_locker->bind_param("i", $id_locker);
    $stmt_check_locker->execute();
    if ($stmt_check_locker->get_result()->num_rows === 0) {
        throw new Exception('El locker seleccionado ya no está disponible.');
    }

    // 3. Actualizar el estado del locker
    $sql_update_locker = "UPDATE locker SET status = 'ocupado' WHERE id = ?";
    $stmt_update_locker = $con->prepare($sql_update_locker);
    $stmt_update_locker->bind_param("i", $id_locker);
    $stmt_update_locker->execute();

    // 4. Crear la nueva reservación con la fecha_fin calculada
    $fecha_fin_str = $hora_fin_reserva->format('Y-m-d H:i:s');
    $sql_insert_reserva = "INSERT INTO reservacion (id_usuario, id_locker, fecha_fin) VALUES (?, ?, ?)";
    $stmt_insert_reserva = $con->prepare($sql_insert_reserva);
    $stmt_insert_reserva->bind_param("iis", $id_usuario, $id_locker, $fecha_fin_str);
    $stmt_insert_reserva->execute();

    $con->commit();
    $response = ['success' => true, 'message' => '¡Reservación exitosa!'];

} catch (Exception $e) {
    $con->rollback();
    $response['message'] = $e->getMessage();
}

// ----> ESTA ES LA LÍNEA CORREGIDA <----
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
?>