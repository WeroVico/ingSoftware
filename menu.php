<?php
// menu.php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php"); 
    exit();
}

$nombre = $_SESSION['nombre'] ?? 'Usuario';
$id_usuario = $_SESSION['id_usuario'];
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="bienvenido.php">Lockers UDG</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="reservar_locker.php">Reservar Locker</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="info_reservacion.php">Mi Reservación</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contacto.php">Contacto</a>
                </li>
                
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1): ?>
                
                <li class="nav-item">
                    <a class="nav-link bg-primary text-white rounded" href="admin_lockers.php">Administrar Lockers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link bg-primary text-white rounded" href="lista_baneados.php" >Lista de baneados</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link bg-primary text-white rounded ms-1" href="admin_reportes.php">
                        <i class="fas fa-chart-bar"></i> Reportes
                    </a>
                </li>

                <?php endif; ?>
                
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo htmlspecialchars($nombre); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="usuario_edita.php?id=<?php echo $id_usuario; ?>">Editar mis datos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>