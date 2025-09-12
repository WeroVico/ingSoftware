<?php
require_once 'menu.php';
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ././index.php");
    exit();
}

$nombre = $_SESSION['nombre'] ?? 'Usuario';
$correo = $_SESSION['correo'] ?? 'Correo no definido';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .welcome-card {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .dropdown-menu {
            background-color: rgb(255, 254, 210)
        }
        .dropdown-item:hover {
            background-color: rgb(134, 228, 155)
        }
    </style>
</head>
<body>
    <!-- Menú de Navegación -->
    <!--
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        
        <div class="container">
            <a class="navbar-brand" href="#">Lorem</a>
            <div class="d-flex align-items-center">
                <span class="me-3" id="nombre"><?php /*echo $_SESSION['nombre']; */?></span>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a id="cerrarSesion" class="dropdown-item" href="../../logout.php">Cerrar sesión</a></li>
                        <li><a id="listaEmp" class="dropdown-item" href="personal_lista.php">Lista de empleados</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    -->

    <!-- Contenido Principal -->
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title">Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</h1>
                <p class="card-text">Correo: <?php echo htmlspecialchars($correo); ?></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>