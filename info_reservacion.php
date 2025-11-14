<?php
// info_reservacion.php
require_once 'menu.php';
require_once 'funciones/conecta.php';
$con = conecta();
$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT 
            r.id as id_reserva,
            r.fecha_inicio, 
            r.fecha_fin, 
            l.etiqueta_completa, 
            m.nombre as nombre_modulo
        FROM reservacion r
        JOIN locker l ON r.id_locker = l.id
        JOIN modulo m ON l.id_modulo = m.id
        WHERE r.id_usuario = ? AND r.status = 'activa'";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$reserva = $result->fetch_assoc();

// Lógica para determinar si se puede extender la reserva
$puede_extender = false;
if ($reserva) {
    date_default_timezone_set('America/Mexico_City');
    $hora_fin_reserva = new DateTime($reserva['fecha_fin']);
    $hora_cierre = (new DateTime())->setTime(21, 0, 0);
    // Solo se puede extender si al añadir una hora no se pasa de las 9 PM
    $hora_fin_extendida = (clone $hora_fin_reserva)->add(new DateInterval("PT1H"));
    if ($hora_fin_extendida <= $hora_cierre) {
        $puede_extender = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información de Mi Locker</title>

    <link rel="stylesheet" href="css/estilo.css?v=1.2">

</head>
<body>
    <div class="container mt-4">
        <?php if ($reserva): ?>
            <div class="card">
                <h5 class="card-header">Detalles de tu Reservación</h5>
                <div class="card-body">
                    <h5 class="card-title">Locker: <?php echo $reserva['etiqueta_completa']; ?></h5>
                    <p class="card-text"><strong>Módulo:</strong> <?php echo $reserva['nombre_modulo']; ?></p>
                    <p class="card-text"><strong>Reservado el:</strong> <?php echo date("d/m/Y H:i", strtotime($reserva['fecha_inicio'])); ?> hrs</p>
                    <p class="card-text"><strong>Vence el:</strong> <?php echo date("d/m/Y H:i", strtotime($reserva['fecha_fin'])); ?> hrs</p>
                    <hr>

                    <?php if ($puede_extender): ?>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#extendModal">Extender Reservación</button>
                    <?php else: ?>
                        <p class="text-warning">Ya no puedes extender tu reservación porque excede el horario de cierre (9:00 PM).</p>
                    <?php endif; ?>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancelar Reservación</button>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <h4>No tienes ninguna reservación activa.</h4>
                <a href="reservar_locker.php" class="btn btn-primary mt-3">Ir a Reservar un Locker</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="extendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Extender tiempo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Puedes extender tu reservación por una o dos horas más.</p>
                    <select id="selectExtension" class="form-select">
                        <option value="1">1 hora</option>
                        <option value="2">2 horas</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="confirm-extend-button" class="btn btn-info">Confirmar Extensión</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">¿Estás seguro?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Estás a punto de cancelar y liberar tu locker. Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="confirm-cancel-button" class="btn btn-danger">Sí, Cancelar Ahora</button>
                </div>
            </div>
        </div>
    </div>

    <script src="jquery-3.3.1.min.js"></script>
    <script>
    $(document).ready(function() {
        // Lógica para el botón de cancelar
        $('#confirm-cancel-button').on('click', function() {
            $.ajax({
                url: 'cancelar_reserva.php',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = 'bienvenido.php?status=cancelled';
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            });
        });

        // NUEVA LÓGICA PARA EXTENDER
        $('#confirm-extend-button').on('click', function() {
            const horas = $('#selectExtension').val();
            $.ajax({
                url: 'extender_reserva.php',
                type: 'POST',
                dataType: 'json',
                data: { horas: horas },
                success: function(response) {
                    if (response.success) {
                        // Recargamos la página para ver la nueva hora de vencimiento
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            });
        });
    });
    </script>
</body>
</html>