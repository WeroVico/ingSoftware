<?php
// usuario_edita.php
require_once 'menu.php'; // Incluye el menú y la validación de sesión
require "funciones/conecta.php";
$con = conecta();

// Obtenemos el ID del usuario de la sesión, no de la URL. ¡Más seguro!
$id = $_SESSION['id_usuario'] ?? 0;

if ($id === 0) {
    // Si por alguna razón no hay ID en la sesión, lo regresamos al inicio
    header("Location: index.php");
    exit();
}

// Consulta preparada para obtener los datos del usuario actual
$stmt = $con->prepare("SELECT nombre, apellidos, codigo, correo FROM usuario WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    echo "Error: Usuario no encontrado.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mis Datos</title>
    <script src="jquery-3.3.1.min.js"></script>
    </head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Editar mi Perfil</h3>
                    </div>
                    <div class="card-body">
                        <form id="formEditar" action="usuario_update.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">

                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre(s)</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="apellidos" class="form-label">Apellidos</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
                            </div>

                             <div class="mb-3">
                                <label for="codigo" class="form-label">Código de Alumno</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" value="<?php echo htmlspecialchars($usuario['codigo']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" readonly>
                                <div class="form-text">El correo no se puede modificar.</div>
                            </div>

                            <div class="mb-3">
                                <label for="pass" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="pass" name="pass" placeholder="Deja en blanco para no cambiar">
                            </div>
                            
                            <div class="mb-3">
                                <label for="foto" class="form-label">Cambiar Foto de Perfil</label>
                                <input class="form-control" type="file" name="foto" id="foto" accept="image/*">
                            </div>

                            <hr>
                            
                            <div class="text-center">
                                <a href="bienvenido.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>