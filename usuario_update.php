<?php
// usuario_update.php
session_start();
require "funciones/conecta.php";
$con = conecta();

// 1. Validar que el usuario esté logueado y que los datos vienen de un POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

// 2. Obtener el ID de la sesión (ignorar el del formulario por seguridad)
$id = $_SESSION['id_usuario'];

// 3. Obtener datos del formulario
$nombre    = $con->real_escape_string($_POST['nombre']);
$apellidos = $con->real_escape_string($_POST['apellidos']);
$pass      = $_POST['pass'] ?? '';

// 4. Construir la consulta SQL dinámicamente
$sql_parts = [];
$params = [];
$types = "";

// Campos que siempre se actualizan
$sql_parts[] = "nombre = ?";
$params[] = $nombre;
$types .= "s";

$sql_parts[] = "apellidos = ?";
$params[] = $apellidos;
$types .= "s";

// 5. Si el usuario escribió una nueva contraseña, la añadimos a la consulta
if (!empty($pass)) {
    $passHash = password_hash($pass, PASSWORD_DEFAULT);
    $sql_parts[] = "pass = ?";
    $params[] = $passHash;
    $types .= "s";
}

// 6. Si se subió una nueva foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    // Primero, borramos la foto anterior si existe
    $stmt_old = $con->prepare("SELECT archivo_file FROM usuario WHERE id = ?");
    $stmt_old->bind_param("i", $id);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    if ($row_old = $result_old->fetch_assoc()) {
        $old_file_path = 'uploads/' . $row_old['archivo_file'];
        if (file_exists($old_file_path)) {
            unlink($old_file_path);
        }
    }
    $stmt_old->close();

    // Ahora, guardamos la nueva foto
    $archivo_temporal = $_FILES['foto']['tmp_name'];
    $archivo_original = $_FILES['foto']['name'];
    $extension = pathinfo($archivo_original, PATHINFO_EXTENSION);
    $nombre_encriptado = md5_file($archivo_temporal) . ".$extension";
    
    if (move_uploaded_file($archivo_temporal, 'uploads/' . $nombre_encriptado)) {
        $sql_parts[] = "archivo_nombre = ?";
        $params[] = $archivo_original;
        $types .= "s";

        $sql_parts[] = "archivo_file = ?";
        $params[] = $nombre_encriptado;
        $types .= "s";
    }
}

// 7. Unir todo y ejecutar la consulta final
if (!empty($sql_parts)) {
    $sql = "UPDATE usuario SET " . implode(', ', $sql_parts) . " WHERE id = ?";
    $types .= "i";
    $params[] = $id;

    $stmt = $con->prepare($sql);
    // Usamos el "splat operator" (...) para pasar el array de parámetros
    $stmt->bind_param($types, ...$params); 
    $stmt->execute();
    $stmt->close();

    // Actualizamos el nombre en la sesión por si lo cambió
    $_SESSION['nombre'] = $nombre;
}

// 8. Redirigir de vuelta a la página de bienvenida
header("Location: bienvenido.php?status=updated");
exit();
?>