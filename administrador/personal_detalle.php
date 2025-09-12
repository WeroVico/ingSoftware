<?php
// personal_detalle.php
require "funciones/conecta.php";
require_once 'menu.php';

$con = conecta();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: personal_lista.php");
    exit();
}

$id = $_GET['id'];
$stmt = $con->prepare("SELECT 
    nombre, 
    apellidos, 
    correo, 
    rol, 
    eliminado,
    archivo_nombre,
    archivo_file
FROM personal_table WHERE id = ?");


$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: personal_lista.php");
    exit();
}

$empleado = $result->fetch_assoc();

$rol = ($empleado['rol'] == 1) ? 'Gerente' : 'Empleado';
$status = ($empleado['eliminado'] == 0) ? 'Activo' : 'Inactivo';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Empleado</title>
    <style>
        body {
            background: #f0f2f5;
            min-height: 100vh;
            margin: 0;
        }

        .detalle {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 350px;
            margin: 0 auto;
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
        }

        .detail-item {
            margin-bottom: 20px;
        }

        .detail-item label {
            display: block;
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .detail-item span {
            display: block;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .btnVolver {
            display: block;
            text-align: center;
            background: #3498db;
            color: white;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
        }

        .btnVolver:hover {
            background: #2980b9;
        }

        .status-activo {
            color: #27ae60;
            font-weight: bold;
        }

        .status-inactivo {
            color: #e74c3c;
            font-weight: bold;
        }

    </style>
</head>
<body>
    <div class="detalle" align="center">
        <h2>Detalle del Empleado</h2>
        
        <div class="detail-item">
            <label>Nombre completo</label>
            <span><?php echo htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellidos']); ?></span>
        </div>
        
        <div class="detail-item">
            <label>Correo electrónico</label>
            <span><?php echo htmlspecialchars($empleado['correo']); ?></span>
        </div>
        
        <div class="detail-item">
            <label>Puesto</label>
            <span><?php echo $rol; ?></span>
        </div>
        
        <div class="detail-item">
            <label>Estado</label>
            <span class="status-<?php echo strtolower($status); ?>">
                <?php echo $status; ?>
            </span>
        </div>
        
        <div class="detail-item">
            <label>Foto</label>
            <?php if ($empleado['archivo_file']): ?>
                <img src="subir_archivos/salvados/<?php echo $empleado['archivo_file']; ?>" 
                    style="max-width: 200px; margin-top: 10px;"
                    alt="Foto de <?php echo htmlspecialchars($empleado['nombre']); ?>"> <!-- Corrección aquí -->
            <?php else: ?>
                <span>Sin foto registrada</span>
            <?php endif; ?>
        </div>


        <div class="btnContainer"><a href="personal_lista.php" class="btnVolver">Volver</a></div>
        
    </div>
</body>
</html>