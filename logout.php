<?php
// logout.php
session_start();

require "funciones/conecta.php"; 
$con = conecta();

if (isset($_SESSION['id_usuario'])) {
    // Ahora sí funciona porque $con existe y la función fue importada
    log_salida($con, $_SESSION['id_usuario']);
}

session_destroy();
header("Location: index.php");
exit();
?>