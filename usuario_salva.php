<?php
// usuario_salva.php
session_start(); // IMPORTANTE: Iniciar sesión al principio para el auto-login
require __DIR__ . "/funciones/conecta.php";
require __DIR__ . "/funciones/enviar_correo.php";

// Define tu clave secreta para crear administradores
define('ADMIN_SECRET_KEY', 'admin');

$con = conecta();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $admin_key = $_POST['admin_key'] ?? '';

    // Lógica para asignar el rol (1=Admin, 2=Estudiante)
    $rol = 2; 
    if (!empty($admin_key) && $admin_key === ADMIN_SECRET_KEY) {
        $rol = 1; 
    }

    $passHash = password_hash($pass, PASSWORD_DEFAULT);
    
    // Manejo de la foto de perfil
    $archivo_original = '';
    $nombre_encriptado = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $archivo_temporal = $_FILES['foto']['tmp_name'];
        $archivo_original = $_FILES['foto']['name'];
        $extension = pathinfo($archivo_original, PATHINFO_EXTENSION);
        $nombre_encriptado = md5_file($archivo_temporal) . ".$extension";
        $directorio = "uploads/";

        if (!move_uploaded_file($archivo_temporal, $directorio . $nombre_encriptado)) {
            // Si falla la imagen, podrías decidir detener todo o seguir sin imagen.
            // Aquí optamos por detener para evitar registros incompletos.
            die("Error al guardar la imagen.");
        }
    }

    // Insertar el usuario en la BD
    $stmt = $con->prepare("INSERT INTO usuario (nombre, apellidos, codigo, correo, pass, rol, archivo_nombre, archivo_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiss", $nombre, $apellidos, $codigo, $correo, $passHash, $rol, $archivo_original, $nombre_encriptado);

    if ($stmt->execute()) {
        // 1. Obtener el ID del nuevo usuario recién creado
        $id_nuevo_usuario = $con->insert_id;

        // 2. AUTO-LOGIN: Crear variables de sesión
        $_SESSION['id_usuario'] = $id_nuevo_usuario;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['correo'] = $correo;
        $_SESSION['rol'] = $rol;

        // 3. AUDITORÍA: Registrar el evento de registro y el de acceso
        // (Asegúrate de que funciones/conecta.php ya tenga las funciones registrar_log y log_acceso)
        registrar_log($con, $id_nuevo_usuario, 'REGISTRO_NUEVO_USUARIO', [
            'rol_asignado' => $rol,
            'codigo' => $codigo
        ]);
        log_acceso($con, $id_nuevo_usuario); 

        // 4. Enviar correo de bienvenida y redirigir al panel principal
        enviarCorreoBienvenida($nombre, $correo);
        header("Location: bienvenido.php?status=welcome");
        exit();
    } else {
        // Error en la inserción
        header("Location: registro.php?status=error");
        exit();
    }
}
?>