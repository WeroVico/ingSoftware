<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario - Sistema de Lockers</title>
    <script src="jquery-3.3.1.min.js"></script>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="body-centrado">
    <div class="register-box">
        <h2>Registro de Nuevo Usuario</h2>
        <form action="usuario_salva.php" method="post" name="formaRegistro" id="formaRegistro" enctype="multipart/form-data">
            <input type="text" name="nombre" placeholder="Nombre(s)" required>
            <input type="text" name="apellidos" placeholder="Apellidos" required>
            <input type="text" name="codigo" placeholder="Código de Alumno" required>
            <input type="email" name="correo" id="correo" placeholder="Correo Institucional" required>
            <div id="correo-error" class="error-message" style="display: none;"></div>
            <input type="password" name="pass" placeholder="Contraseña" required>
            <label for="foto" style="display: block; margin-top: 10px;">Foto de perfil:</label>
            <input type="file" name="foto" accept="image/*" required>
            <input type="password" name="admin_key" placeholder="Clave de acceso privilegiado (opcional)">
            <button type="submit">Registrarse</button>
        </form>
        <div class="register-link">
            <p>¿Ya tienes una cuenta? <a href="index.php">Inicia sesión</a></p>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#correo').blur(function() {
                const correo = $(this).val().trim();
                if (!correo) return;
                $.ajax({
                    url: 'verifica_correo.php',
                    method: 'POST',
                    data: { correo: correo },
                    dataType: 'json',
                    success: (res) => {
                        if (res.existe) {
                            $('#correo-error').text('Este correo ya está registrado').show();
                        } else {
                            $('#correo-error').hide();
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>