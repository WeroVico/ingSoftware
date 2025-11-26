<?php
require_once 'menu.php';
require_once 'funciones/conecta.php';
$con = conecta();

// Asegurar misma zona horaria
date_default_timezone_set('America/Mexico_City');

$nombre = $_SESSION['nombre'] ?? 'Usuario';
$id_usuario = $_SESSION['id_usuario'] ?? 0;

// --------------------------------------------------------------------
// 1. LÓGICA DE SUSPENSIÓN 
// --------------------------------------------------------------------
$estado_susp = 0;
$susp_hasta  = null;

// Verificar primero si las columnas existen para evitar errores fatales si no has corrido el ALTER TABLE
$cols_ok = false;
$check_sql = "SHOW COLUMNS FROM usuario LIKE 'estado_suspension'";
if ($con->query($check_sql)->num_rows > 0) {
    $cols_ok = true;
    $sql_susp = "SELECT estado_suspension, suspension_hasta FROM usuario WHERE id = ?";
    $stmt_susp = $con->prepare($sql_susp);
    $stmt_susp->bind_param("i", $id_usuario);
    $stmt_susp->execute();
    $res_susp = $stmt_susp->get_result()->fetch_assoc();
    $estado_susp = $res_susp['estado_suspension'] ?? 0;
    $susp_hasta  = $res_susp['suspension_hasta'] ?? null;
}

if ($cols_ok && $estado_susp == 1) {
    $suspHastaDt = $susp_hasta ? new DateTime($susp_hasta) : null;
    $nowDt = new DateTime();
    
    if ($suspHastaDt && $suspHastaDt <= $nowDt) {
        // La suspensión ya terminó -> levantar castigo
        $update = $con->prepare("UPDATE usuario SET estado_suspension=0, suspension_hasta=NULL WHERE id=?");
        $update->bind_param("i", $id_usuario);
        $update->execute();
        
        // Registrar en log
        if(function_exists('registrar_log')) {
            registrar_log($con, $id_usuario, 'SUSPENSION_FINALIZADA', ['motivo' => 'Tiempo cumplido']);
        }
    } else {
        // Sigue suspendido -> Bloquear acceso
        echo "<div class='container mt-5'><div class='alert alert-danger text-center'>
                <h3>🚫 Cuenta Suspendida</h3>
                <p>Tu cuenta está bloqueada por mal uso de los lockers.</p>
                <p>La suspensión termina el: <strong>" . date("d/m/Y H:i", strtotime($susp_hasta)) . "</strong></p>
              </div></div>";
        exit();
    }
}

// --------------------------------------------------------------------
// 2. VERIFICAR RESERVA Y VENCIMIENTO
// --------------------------------------------------------------------
$tiene_reserva = false;
$info_locker = [];

// CORRECCIÓN: Tabla 'locker' (singular)
$sql = "SELECT r.id AS id_reserva, r.fecha_fin, r.id_locker, l.etiqueta_completa 
        FROM reservacion r
        JOIN locker l ON r.id_locker = l.id
        WHERE r.id_usuario = ? AND r.status = 'activa'";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $reserva = $result->fetch_assoc();
    $fecha_fin_dt = new DateTime($reserva['fecha_fin']);
    $now_dt = new DateTime();

    // Si ya venció la reserva
    if ($fecha_fin_dt < $now_dt) {
        $con->begin_transaction();
        try {
            // 1. Marcar reserva vencida
            $con->query("UPDATE reservacion SET status='vencida' WHERE id={$reserva['id_reserva']}");
            
            // 2. Liberar locker (CORRECCIÓN: Tabla 'locker')
            $con->query("UPDATE locker SET status='disponible' WHERE id={$reserva['id_locker']}");
            
            // 3. Suspender usuario por 48 horas si existen las columnas
            if ($cols_ok) {
                $fecha_suspension = date('Y-m-d H:i:s', strtotime('+48 hours'));
                $upd_user = $con->prepare("UPDATE usuario SET estado_suspension=1, suspension_hasta=? WHERE id=?");
                $upd_user->bind_param("si", $fecha_suspension, $id_usuario);
                $upd_user->execute();
                
                // Log de penalización
                if(function_exists('registrar_log')) {
                    registrar_log($con, $id_usuario, 'PENALIZACION_AUTOMATICA', [
                        'motivo' => 'Reserva vencida sin entregar',
                        'reserva_id' => $reserva['id_reserva'],
                        'suspension_hasta' => $fecha_suspension
                    ]);
                }
            }
            
            $con->commit();
            
            echo "<div class='container mt-5'><div class='alert alert-warning text-center'>
                    <h3>⚠️ Reserva Vencida</h3>
                    <p>Tu reserva ha expirado y no liberaste el locker a tiempo.</p>
                    <p>Se ha aplicado una suspensión temporal de 48 horas.</p>
                  </div></div>";
            exit();

        } catch (Exception $e) {
            $con->rollback();
        }
    }

    // Si todo está bien
    $tiene_reserva = true;
    $info_locker = [
        'numero' => $reserva['etiqueta_completa'],
        'vence'  => date("d/m/Y H:i", strtotime($reserva['fecha_fin']))
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - Sistema de Lockers</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container mt-4">
        <div class="jumbotron text-center">
            <h1 class="display-5">¡Hola, <?php echo htmlspecialchars($nombre); ?>!</h1>
            <p class="lead">Bienvenido a tu panel de control.</p>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row text-center">
            <div class="col-lg-12 mb-4">
                <div class="card bg-light action-card">
                    <div class="card-body">
                        <?php if ($tiene_reserva): ?>
                            <i class="fas fa-check-circle text-success"></i>
                            <h5 class="card-title">Tu Locker Asignado</h5>
                            <p class="card-text fs-4"><strong>Número: <?php echo $info_locker['numero']; ?></strong></p>
                            <p class="card-text">Tu reservación vence el: <?php echo $info_locker['vence']; ?></p>
                            <a href="info_reservacion.php" class="btn btn-success">Ver Detalles</a>
                        <?php else: ?>
                            <i class="fas fa-info-circle text-primary"></i>
                            <h5 class="card-title">Aún no tienes un locker</h5>
                            <p class="card-text">Parece que no tienes una reservación activa.</p>
                            <a href="reservar_locker.php" class="btn btn-primary">Reservar Mi Locker</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <a href="usuario_edita.php" class="text-decoration-none">
                    <div class="card action-card h-100">
                        <div class="card-body">
                            <i class="fas fa-user-edit"></i>
                            <h5 class="card-title">Editar Mi Perfil</h5>
                            <p class="card-text text-muted">Actualiza tu nombre o contraseña.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 mb-4">
                <a href="contacto.php" class="text-decoration-none">
                    <div class="card action-card h-100">
                        <div class="card-body">
                            <i class="fas fa-comments"></i>
                            <h5 class="card-title">Contacto</h5>
                            <p class="card-text text-muted">¿Dudas? Contáctanos.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>