<?php
// Final/administrador/menu.php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php"); // Redirige al login (index.php raíz)
    exit();
}

// Obtener datos del usuario
$nombre = $_SESSION['nombre'] ?? 'Usuario';
$correo = $_SESSION['correo'] ?? 'Correo no definido';
?>
<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Menú -->
<style>
    #mainNav {
        width: 100vw;
    }
</style>
<nav id="mainNav" class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="bienvenido.php">INICIO</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="personal_lista.php">EMPLEADOS</a></li>
                <li class="nav-item"><a class="nav-link" href="#">PRODUCTOS</a></li>
                <li class="nav-item"><a class="nav-link" href="#">PROMOCIONES</a></li>
                <li class="nav-item"><a class="nav-link" href="#">PEDIDOS</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="navbar-text me-3"><?php echo $nombre; ?></span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-danger" href="../../logout.php">Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>