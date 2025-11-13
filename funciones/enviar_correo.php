<?php
// funciones/enviar_correo.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function enviarCorreoBienvenida($nombre, $correo) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'angelcampeon2005@gmail.com';
        $mail->Password   = 'mehm ruqj tqim slwm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('angelcampeon2005@gmail.com', 'Sistema de Lockers');
        $mail->addAddress($correo, $nombre);

        $mail->isHTML(true);
        $mail->Subject = "Bienvenido al Sistema de Lockers";
        $mail->Body    = "Hola <b>$nombre</b>,<br><br>¡Te damos la bienvenida al sistema de apartado de lockers!<br>Ya puedes iniciar sesión con tu correo y la contraseña que registraste.<br><br>Saludos,<br>Administración";
        $mail->AltBody = "Hola $nombre,\n\n¡Te damos la bienvenida al sistema de apartado de lockers!\nYa puedes iniciar sesión con tu correo y la contraseña que registraste.\n\nSaludos,\nAdministración";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo de bienvenida: {$mail->ErrorInfo}");
        return false;
    }
}

function notificarUsuarioEnEspera($id_modulo, $con) {
    // 1. Buscar al primer usuario en la lista de espera (FIFO)
    $sql = "SELECT le.id, u.nombre, u.correo 
            FROM lista_espera le
            JOIN usuario u ON le.id_usuario = u.id
            WHERE le.id_modulo = ? AND le.status = 'pendiente'
            ORDER BY le.fecha_registro ASC
            LIMIT 1";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_modulo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        $id_lista = $usuario['id'];

        // 2. Marcar al usuario como 'notificado' para no volver a enviarle
        $stmt_update = $con->prepare("UPDATE lista_espera SET status = 'notificado' WHERE id = ?");
        $stmt_update->bind_param("i", $id_lista);
        $stmt_update->execute();

        // 3. Enviar el correo de notificación
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'angelcampeon2005@gmail.com';
            $mail->Password   = 'mehm ruqj tqim slwm';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('angelcampeon2005@gmail.com', 'Sistema de Lockers');
            $mail->addAddress($usuario['correo'], $usuario['nombre']);

            $mail->isHTML(true);
            $mail->Subject = "Un locker se ha liberado!";
            $mail->Body    = "Hola <b>{$usuario['nombre']}</b>,<br><br>¡Buenas noticias! Un locker en uno de los módulos en los que estabas esperando se ha liberado.<br><br>¡Date prisa y entra al sistema para reservarlo antes que alguien más!<br><br>Saludos,<br>Yo xd";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al notificar a usuario en espera: {$mail->ErrorInfo}");
            return false;
        }
    }
    return false;
}