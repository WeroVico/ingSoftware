<?php
// usuario_salva.php
require __DIR__ . "/funciones/conecta.php";
require __DIR__ . "/funciones/enviar_correo.php";

// Define tu clave secreta. ¡Cámbiala por algo seguro!
define('ADMIN_SECRET_KEY', 'admin');

$con = conecta();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $admin_key = $_POST['admin_key'] ?? '';

    // Lógica para asignar el rol
    $rol = 2; // Rol de Estudiante por defecto
    if (!empty($admin_key) && $admin_key === ADMIN_SECRET_KEY) {
        $rol = 1; // Rol de Administrador
    }

    $passHash = password_hash($pass, PASSWORD_DEFAULT);
    
    $archivo_original = '';
    $nombre_encriptado = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $archivo_temporal = $_FILES['foto']['tmp_name'];
        $archivo_original = $_FILES['foto']['name'];
        $extension = pathinfo($archivo_original, PATHINFO_EXTENSION);
        $nombre_encriptado = md5_file($archivo_temporal) . ".$extension";
        $directorio = "uploads/";

        if (!move_uploaded_file($archivo_temporal, $directorio . $nombre_encriptado)) {
            die("Error al guardar la imagen.");
        }
    }

    // Actualizamos la consulta para incluir el nuevo campo 'rol'
    $stmt = $con->prepare("INSERT INTO usuario (nombre, apellidos, codigo, correo, pass, rol, archivo_nombre, archivo_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiss", $nombre, $apellidos, $codigo, $correo, $passHash, $rol, $archivo_original, $nombre_encriptado);

    if ($stmt->execute()) {
        enviarCorreoBienvenida($nombre, $correo);
        header("Location: index.php?status=success");
        exit();
    } else {
        header("Location: registro.php?status=error");
        exit();
    }
}
?>