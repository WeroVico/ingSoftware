<?php
// funciones/conecta.php
define("HOST", 'localhost:3306');
define("BD", 'lockers'); // Nombre de la nueva base de datos
define("USER_BD", 'root');
define("PASS_BD", '');

function conecta() {
    $con = new mysqli(HOST, USER_BD, PASS_BD, BD);
    if ($con->connect_error) {
        die("Error de conexión: " . $con->connect_error);
    }
    return $con;
}

function registrar_log($con, $id_usuario, $accion, $datos_extra = []) {
    // 1. Obtener "Snapshot" del perfil del usuario en este momento
    $sql_user = "SELECT id, nombre, apellidos, codigo, correo, rol FROM usuario WHERE id = ?";
    $stmt_u = $con->prepare($sql_user);
    $stmt_u->bind_param("i", $id_usuario);
    $stmt_u->execute();
    $res_u = $stmt_u->get_result();
    $datos_usuario = $res_u->fetch_assoc();

    // 2. Combinar perfil del usuario + datos de la reserva/acción
    $info_completa = [
        'perfil_usuario' => $datos_usuario,
        'datos_evento' => $datos_extra
    ];
    
    // Convertir todo a texto JSON para guardarlo
    $json_detalles = json_encode($info_completa, JSON_UNESCAPED_UNICODE);
    
    // Obtener IP (opcional)
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // 3. Insertar en la tabla de logs
    $sql_log = "INSERT INTO sistema_logs (id_usuario, accion, detalles, ip_usuario) VALUES (?, ?, ?, ?)";
    $stmt_l = $con->prepare($sql_log);
    $stmt_l->bind_param("isss", $id_usuario, $accion, $json_detalles, $ip);
    $stmt_l->execute();
}

// Wrapper para LOG DE INICIO DE SESIÓN (LOGIN) 
function log_acceso($con, $id_usuario) {
    registrar_log($con, $id_usuario, 'ACCESO_SISTEMA', [
        'mensaje' => 'Inicio de sesión exitoso',
        'tipo' => 'login'
    ]);
}

// Wrapper para LOG DE SALIDA (LOGOUT) 
function log_salida($con, $id_usuario) {
    registrar_log($con, $id_usuario, 'SALIDA_SISTEMA', [
        'mensaje' => 'Cierre de sesión voluntario',
        'tipo' => 'logout'
    ]);
}

// Wrapper para LOG DE ERRORES
function log_error($con, $id_usuario, $accion_intentada, $mensaje_error, $datos_contexto = []) {
    registrar_log($con, $id_usuario, 'ERROR_SISTEMA', [
        'accion_intentada' => $accion_intentada,
        'error_reportado' => $mensaje_error,
        'contexto' => $datos_contexto
    ]);
}

// Wrapper para LOG DE ADMIN
function log_admin_cambio($con, $id_admin, $id_locker, $nuevo_estado) {
    registrar_log($con, $id_admin, 'ADMIN_CAMBIO_ESTADO', [
        'id_locker_modificado' => $id_locker,
        'nuevo_estado' => $nuevo_estado,
        'nota' => 'Cambio forzado por administrador'
    ]);
}

// Wrapper para LOG DE NUEVA RESERVA
function log_nueva_reserva($con, $id_usuario, $id_locker, $duracion, $fecha_fin) {
    registrar_log($con, $id_usuario, 'NUEVA_RESERVA', [
        'id_locker' => $id_locker,
        'duracion_seleccionada' => $duracion,
        'fecha_fin_calculada' => $fecha_fin
    ]);
}

// Wrapper para LOG DE CANCELACIÓN
function log_cancelacion($con, $id_usuario, $id_reserva, $id_locker) {
    registrar_log($con, $id_usuario, 'CANCELACION_RESERVA', [
        'id_reserva_cancelada' => $id_reserva,
        'locker_liberado' => $id_locker,
        'razon' => 'Cancelación manual por usuario'
    ]);
}

// Wrapper para LOG DE EXTENSIÓN
function log_extension($con, $id_usuario, $id_reserva, $horas, $nueva_fecha) {
    registrar_log($con, $id_usuario, 'EXTENSION_TIEMPO', [
        'id_reserva' => $id_reserva,
        'horas_agregadas' => $horas,
        'nueva_fecha_vencimiento' => $nueva_fecha
    ]);
}

?>