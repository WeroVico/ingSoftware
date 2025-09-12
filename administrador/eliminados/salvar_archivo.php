<?php
/*
// salvar_archivo.php

//Cargar archivo
$archivo_original   =   $_FILES['archivo']['name'];
$archivo_temporal   =   $_FILES['archivo']['tmp_name'];

//Obtener extension
$arreglo    =   explode(".", $archivo_original);
$len        =   count($arreglo);
$pos        =   $len - 1;
$extension  =   $arreglo[$pos];

//Ruta para salvar
$dir = "salvados/";

//Obtener nueboNombreUnico
$nombre_encriptado  =   md5_file($archivo_temporal);
$nuevo_nombre       =   "$nombre_encriptado.$extension";

echo "Nombre original:      $archivo_original <br>";
echo "Archivo temporal:     $archivo_temporal <br>";
echo "Extension:            $extension <br>";
echo "Nombre encriptado:    $nombre_encriptado <br>";
echo "Nuevo nombre:         $nuevo_nombre <br>";

copy($archivo_temporal, $dir.$nuevo_nombre);
*/
header("Location: ../personal_salva.php");
exit;
?>