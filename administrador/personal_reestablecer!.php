<?php
//personal_reestablecer!.php
require "funciones/conecta.php";
$con = conecta();

//Cacho variables
$id = $_REQUEST['id'];

//$sql = "DELETE FROM personal_table WHERE id = $id";
$sql = "UPDATE personal_table SET eliminado = 0";
$res = $con-> query($sql);

header("Location: personal_lista.php");


?> 