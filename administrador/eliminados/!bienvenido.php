<?php
// bienvenido.php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido</title>
</head>
<body>
    <h1>Hola, bienvenido al sistema.</h1>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>