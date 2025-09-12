<?php
// personal_update.php
require "funciones/conecta.php";
$con = conecta();

// 1. Validar datos básicos
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) die('ID inválido');

// 2. Obtener datos del formulario
$nombre    = $con->real_escape_string($_POST['nombre']);
$apellidos = $con->real_escape_string($_POST['apellidos']);
$correo    = $con->real_escape_string($_POST['correo']);
$rol       = filter_input(INPUT_POST, 'rol', FILTER_VALIDATE_INT);
$pass      = $_POST['password'] ?? '';

// 3. Inicializar variables para la consulta dinámica
$set = "nombre = ?, apellidos = ?, correo = ?, rol = ?";
$tipos = "sssi"; // Tipos de datos: string, string, string, integer
$params = [&$nombre, &$apellidos, &$correo, &$rol];

// 4. Procesar contraseña si se proporcionó
if (!empty($pass)) {
    $passHash = password_hash($pass, PASSWORD_DEFAULT);
    $set .= ", pass = ?";
    $tipos .= "s";
    $params[] = &$passHash;
}

// 5. Procesar nueva foto si se subió
if (!empty($_FILES['foto']['tmp_name'])) {
    // 1. Validar tipo MIME real
    $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $mime = mime_content_type($_FILES['foto']['tmp_name']);
    if (!in_array($mime, $permitidos)) {
        die("Error: Formato no válido. Tipo detectado: $mime");
    }

    // 2. Eliminar imagen anterior
    $sql_old = "SELECT archivo_file FROM personal_table WHERE id = ?";
    $stmt_old = $con->prepare($sql_old);
    $stmt_old->bind_param("i", $id);
    $stmt_old->execute();
    $old_file = $stmt_old->get_result()->fetch_assoc()['archivo_file'];
    
    if ($old_file && file_exists("subir_archivos/salvados/$old_file")) {
        unlink("subir_archivos/salvados/$old_file");
    }

    // 3. Generar nuevo nombre
    $archivo_temporal = $_FILES['foto']['tmp_name'];
    $archivo_original = htmlspecialchars($_FILES['foto']['name'], ENT_QUOTES);
    $extension = pathinfo($archivo_original, PATHINFO_EXTENSION);
    $nombre_encriptado = md5_file($archivo_temporal) . ".$extension";
    $directorio = "subir_archivos/salvados/";

    // 4. Mover archivo y actualizar campos
    if (move_uploaded_file($archivo_temporal, $directorio . $nombre_encriptado)) {
        $set .= ", archivo_nombre = ?, archivo_file = ?";
        $tipos .= "ss";
        $params[] = &$archivo_original;
        $params[] = &$nombre_encriptado;
    }
}

// 6. Agregar ID a los parámetros
$tipos .= "i";
$params[] = &$id;

// 7. Crear y ejecutar consulta preparada
$sql = "UPDATE personal_table SET $set WHERE id = ?";
$stmt = $con->prepare($sql);

if (!$stmt) die("Error en preparación: " . $con->error);

// Vincular parámetros dinámicamente
$stmt->bind_param($tipos, ...$params);

if ($stmt->execute()) {
    header('Location: personal_lista.php');
} else {
    echo "Error al actualizar: " . $stmt->error;
}

$stmt->close();
?>