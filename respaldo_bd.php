<?php
// respaldo_bd.php
// Este script debe ejecutarse desde la línea de comandos o por una tarea programada.

require "funciones/conecta.php";

// 1. Configuración
$carpeta_backups = __DIR__ . '/backups';
$fecha = date('Y-m-d_H-i-s');
$nombre_archivo = "respaldo_lockers_$fecha.sql";
$ruta_completa = "$carpeta_backups/$nombre_archivo";

// Crear la carpeta si no existe
if (!file_exists($carpeta_backups)) {
    mkdir($carpeta_backups, 0777, true);
}

// 2. Configuración de mysqldump
// IMPORTANTE: En XAMPP/Windows, a veces necesitas la ruta completa.
// Si falla, cambia "mysqldump" por algo como: "C:/xampp/mysql/bin/mysqldump.exe"
$ruta_mysqldump = "mysqldump"; 

// 3. Obtener credenciales desde conecta.php
// Desglosamos el host y puerto (ej. localhost:3307)
$host_parts = explode(':', HOST);
$db_host = $host_parts[0];
$db_port = isset($host_parts[1]) ? $host_parts[1] : '3306';

$usuario = USER_BD;
$password = PASS_BD;
$base_datos = BD;

// Construir el comando (sin espacio después de -p para la contraseña)
$pass_cmd = ($password != '') ? "-p$password" : "";

$comando = "$ruta_mysqldump --host=$db_host --port=$db_port --user=$usuario $pass_cmd $base_datos > \"$ruta_completa\"";

// 4. Ejecutar el comando
// system() ejecuta el comando en la consola del servidor
$salida = null;
$codigo_retorno = null;
exec($comando, $salida, $codigo_retorno);

if ($codigo_retorno === 0) {
    echo "✅ Respaldo exitoso creado en: $ruta_completa\n";
    
    // (Opcional) Borrar respaldos viejos (mayores a 30 días) para no llenar el disco
    $archivos = glob("$carpeta_backups/*.sql");
    $ahora = time();
    foreach ($archivos as $archivo) {
        if (is_file($archivo)) {
            if ($ahora - filemtime($archivo) >= 30 * 24 * 60 * 60) { // 30 días
                unlink($archivo);
                echo "🗑️ Respaldo antiguo eliminado: " . basename($archivo) . "\n";
            }
        }
    }
} else {
    echo "❌ Error al crear el respaldo. Código de error: $codigo_retorno\n";
    echo "Verifica que 'mysqldump' esté en las variables de entorno o pon la ruta completa en el script.\n";
}
?>