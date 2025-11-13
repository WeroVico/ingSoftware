<!DOCTYPE html>
<html>
<head>
    <!--index.php-->
    <title>Iniciar Sesión - Sistema de Lockers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="jquery-3.3.1.min.js"></script>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="body-centrado">
    <div class="login-box" >
        <h2>Sistema de Lockers</h2>
        <form id="loginForm">
            <input type="email" name="correo" id="correo" placeholder="Correo Institucional" required>
            <input type="password" name="pass" id="pass" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <div id="mensaje"></div>
        <div class="register-link">
            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#loginForm').submit(function(e) {
            e.preventDefault();
            $('#mensaje').hide().text('');

            const correo = $('#correo').val().trim();
            const pass = $('#pass').val().trim();

            if (!correo || !pass) {
                $('#mensaje').text('Todos los campos son obligatorios').show();
                return;
            }

            $.ajax({
                url: 'login_verifica.php',
                type: 'POST',
                dataType: 'json',
                data: { correo: correo, pass: pass },
                success: function(res) {
                    if (res.existe) {
                        window.location.href = 'bienvenido.php';
                    } else {
                        $('#mensaje').text('Correo o contraseña incorrectos').show();
                    }
                }
            });
        });
    });
    </script>
</body>
</html>