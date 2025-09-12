<?php
// personal_salva.php
require_once 'menu.php';
require "funciones/conecta.php";
$con = conecta();

$error = isset($_GET['error']) ? $_GET['error'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $rol = $_POST['rol'] ?? '';

    
    // Generar hash de la contraseña
    $passHash = password_hash($pass, PASSWORD_DEFAULT);

    // Insertar en BD
    $stmt = $con->prepare("INSERT INTO personal_table 
                          (nombre, apellidos, correo, pass, rol) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $nombre, $apellidos, $correo, $passHash, $rol);
    
    if ($stmt->execute()) {
        header("Location: personal_lista.php");
        exit();
    } else {
        die("Error al registrar: " . $stmt->error);
    }

    // 1. Validar archivo
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        header("Location: personal_salva.php?error=Debes subir una foto del empleado");
        exit();
    }

    // 2. Validar tipo MIME real
    $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $mime = mime_content_type($_FILES['foto']['tmp_name']); // Usar tmp_name
    
    if (!in_array($mime, $permitidos)) {
        die("Error: Formato no válido. Solo JPG, PNG o GIF. Tipo detectado: $mime");
    }

    // 3. Generar nombres
    $archivo_temporal = $_FILES['foto']['tmp_name'];
    $archivo_original = $_FILES['foto']['name'];
    $extension = pathinfo($archivo_original, PATHINFO_EXTENSION);
    $nombre_encriptado = md5_file($archivo_temporal) . ".$extension";
    echo "<script>console.log('Nombre encriptado:', " . json_encode($nombre_encriptado) . ")</script>";
    $directorio = "subir_archivos/salvados/"; // Ruta

    // 4. Crear directorio si no existe
    if (!file_exists($archivo_temporal)) {
        die("Error: Archivo temporal no encontrado.");
    }

    // 5. Mover archivo
    if (!move_uploaded_file($archivo_temporal, $directorio . $nombre_encriptado)) {
        die("Error: No se pudo guardar la imagen. Verifica permisos de la carpeta 'salvados'");
    }

    

    function correoExiste($correo, $conexion) {
        $consulta = $conexion->prepare("SELECT id FROM personal_table WHERE correo = ? AND eliminado = 0");
        $consulta->bind_param("s", $correo);
        $consulta->execute();
        $consulta->store_result();
        return $consulta->num_rows > 0;
    }

    if (correoExiste($correo, $con)) {
        header("Location: personal_salva.php?error=El correo electrónico ya está registrado");
        exit();
    }
    $pass = $_POST['pass'] ?? '';
    $passEnc = password_hash($pass, PASSWORD_DEFAULT);

    $sql = "INSERT INTO personal_table
        (nombre, apellidos, correo, pass, rol, archivo_nombre, archivo_file)
        VALUES(?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssssss", 
        $nombre, 
        $apellidos, 
        $correo, 
        $passEnc, 
        $rol,
        $archivo_original,  // Nombre real
        $nombre_encriptado  // Nombre encriptado
    );
    $stmt->execute();
    
    header("Location: personal_lista.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de empleados</title>
    <script src="jquery-3.3.1.min.js"></script>
    <style>
        /* ===== Nuevos estilos CSS ===== */
        .form-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .form-row {
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .form-label {
            width: 200px;
            text-align: right;
            padding-right: 20px;
            font-weight: 500;
            color: #4a5568;
        }

        .form-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            border-color: #4299e1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(66,153,225,0.1);
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 35px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-volver {
            background: #718096;
            color: white;
        }

        .btn-guardar {
            background: #48bb78;
            color: white;
        }

        .error-message {
            color: #e53e3e;
            background: #fff5f5;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #fed7d7;
        }

        /* ===== Mantenemos estructura original ===== */
        body {
            background: #f8f9fa;
            padding: 20px;
        }
    </style>
</head>
<body>
    <?php require_once 'menu.php'; ?>
    
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 30px; color: #2d3748;">Alta de empleados</h2>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="personal_salva.php" method="post" name="forma01" id="formulario1" enctype="multipart/form-data">
            <!-- Mantenemos misma estructura de campos solo con mejoras visuales -->
            <div class="form-row">
                <label class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-input" placeholder="Nombre" required>
            </div>

            <div class="form-row">
                <label class="form-label">Apellidos:</label>
                <input type="text" name="apellidos" class="form-input" placeholder="Apellidos" required>
            </div>

            <div class="form-row">
                <label class="form-label">Correo:</label>
                <input type="email" name="correo" id="correo" class="form-input" placeholder="Mail" required>
                <div id="correo-error" style="color: #e53e3e; margin-top: 5px;"></div>
            </div>

            <div class="form-row">
                <label class="form-label">Contraseña:</label>
                <input type="password" name="pass" class="form-input" placeholder="Password" required>
            </div>

            <div class="form-row">
                <label class="form-label">Rol:</label>
                <select name="rol" class="form-input" required>
                    <option value="0">Selecciona...</option>
                    <option value="1">Gerente</option>
                    <option value="2">Empleado</option>
                </select>
            </div>

            <div class="form-row">
                <label class="form-label">Foto:</label>
                <input type="file" name="foto" class="form-input" accept="image/*" required 
                    style="padding: 8px; background: #f8fafc;">
            </div>

            <div class="button-group">
                <button type="button" onclick="location.href='personal_lista.php'" 
                    class="btn btn-volver">Volver</button>
                <button type="submit" class="btn btn-guardar">Guardar</button>
            </div>
        </form>
    </div>

    <!-- Mantenemos mismo JS original -->
    <script>
        $(document).ready(function() {
            $('#correo').blur(function() {
                const correo = $(this).val().trim();
                if (!correo) return;

                $.ajax({
                    url: 'verifica_correo.php',
                    method: 'POST',
                    data: { correo },
                    dataType: 'json',
                    success: (res) => {
                        if (res.existe) {
                            $('#correo-error').text(`El correo ${correo} ya existe`)
                            setTimeout(() => $('#correo-error').text(''), 5000);
                        }
                    }
                });
            });

            function validar(e) {
                e.preventDefault();
                const campos = ['nombre', 'apellidos', 'correo', 'pass', 'rol'];
                let valido = true;

                campos.forEach(campo => {
                    const valor = document.forms['forma01'][campo].value.trim();
                    if (!valor || (campo === 'rol' && valor === '0')) valido = false;
                });

                if (valido) {
                    document.forma01.submit();
                } else {
                    alert('Faltan campos por llenar');
                }
            }
        });
    </script>
</body>
</html>