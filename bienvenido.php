<?php
// bienvenido.php
// Usamos require_once para asegurarnos de que el menú se cargue una sola vez.
require_once 'menu.php';
require_once 'funciones/conecta.php';
$con = conecta();

// El nombre del usuario ya está en la sesión gracias a menu.php
$nombre = $_SESSION['nombre'] ?? 'Usuario';
$id_usuario = $_SESSION['id_usuario'] ?? 0;

// --- LÓGICA DE RESERVACIÓN REAL ---
// Hacemos una consulta para ver si el usuario ya tiene un locker.
$tiene_reserva = false;
$info_locker = [];

$sql = "SELECT r.fecha_fin, l.etiqueta_completa 
        FROM reservacion r
        JOIN locker l ON r.id_locker = l.id
        WHERE r.id_usuario = ? AND r.status = 'activa'";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $reserva = $result->fetch_assoc();
    $tiene_reserva = true;
    $info_locker = [
        'numero' => $reserva['etiqueta_completa'],
        'vence' => date("d/m/Y", strtotime($reserva['fecha_fin']))
    ];
}
// --- Fin de la lógica ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - Sistema de Lockers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="css/estilo.css?v=1.2">

</head>
<body>

    <div class="container mt-4">
        <div class="jumbotron text-center">
            <h1 class="display-5">¡Hola, <?php echo htmlspecialchars($nombre); ?>!</h1>
            <p class="lead">Bienvenido a tu panel de control. Aquí puedes gestionar todo lo relacionado con tu locker.</p>
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
                            <p class="card-text">Parece que no tienes una reservación activa. ¡Consigue tu locker ahora mismo!</p>
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
                            <p class="card-text text-muted">Actualiza tu nombre, contraseña o foto de perfil.</p>
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
                            <p class="card-text text-muted">Encuentra nuestras redes sociales o envíanos un correo.</p>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>

</body>
</html>