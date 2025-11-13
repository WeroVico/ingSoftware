<?php
// expirar_reservas.php
// Este script está diseñado para ser ejecutado por un CRON JOB.

require "funciones/conecta.php";
$con = conecta();

// ¡Importante! Asegurarse de que el script use la misma zona horaria que tu aplicación
date_default_timezone_set('America/Mexico_City');
$ahora = date('Y-m-d H:i:s');

// Iniciar una transacción para seguridad
$con->begin_transaction();

try {
    // 1. Buscar todas las reservas que están 'activas' Y cuya fecha_fin ya pasó
    $sql_vencidas = "SELECT id, id_locker 
                     FROM reservacion 
                     WHERE status = 'activa' AND fecha_fin <= ?";
    
    $stmt_vencidas = $con->prepare($sql_vencidas);
    $stmt_vencidas->bind_param("s", $ahora);
    $stmt_vencidas->execute();
    $res_vencidas = $stmt_vencidas->get_result();

    $lockers_a_liberar = [];
    $reservas_a_expirar = [];

    while ($row = $res_vencidas->fetch_assoc()) {
        $lockers_a_liberar[] = $row['id_locker']; // Guardamos el ID del locker
        $reservas_a_expirar[] = $row['id'];      // Guardamos el ID de la reserva
    }

    // 2. Si se encontraron reservas vencidas, actualizarlas
    if (count($reservas_a_expirar) > 0) {
        
        // 2a. Convertir los arrays de IDs en texto para las consultas 'IN'
        $lista_reservas = implode(',', $reservas_a_expirar);
        $lista_lockers = implode(',', $lockers_a_liberar);

        // 2b. Marcar las RESERVAS como 'expirada'
        $con->query("UPDATE reservacion SET status = 'expirada' WHERE id IN ($lista_reservas)");

        // 2c. Marcar los LOCKERS como 'disponible'
        $con->query("UPDATE locker SET status = 'disponible' WHERE id IN ($lista_lockers)");
    }

    // 3. Confirmar los cambios en la BD
    $con->commit();
    
    // (Opcional) Puedes guardar un log para saber que se ejecutó
    // file_put_contents('cron_log.txt', "[$ahora] Tarea ejecutada. " . count($reservas_a_expirar) . " reservas expiradas.\n", FILE_APPEND);

} catch (Exception $e) {
    // Si algo falla, revertir todo
    $con->rollback();
    
    // (Opcional) Guardar un log del error
    // file_put_contents('cron_error_log.txt', "[$ahora] ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
}

$con->close();
?>