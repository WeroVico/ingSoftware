<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header("Location: Final/administrador/bienvenido.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial,sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-self: center;
        }

        .login-box {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #mensaje {
            color: red;
            margin-top: 10px;
            text-align: center;
        }

        .formLoginItem {
            display: flex;
            justify-self: center;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 class="formLoginItem">Iniciar Sesión</h2>
        <form id="loginForm">
            <input class="formLoginItem" type="email" name="correo" id="correo" placeholder="Correo" required>
            <input class="formLoginItem" type="password" name="pass" id="pass" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <div id="mensaje"></div>
    </div>

    <script>
    $(document).ready(function() {
        $('#loginForm').submit(function(e) {
            e.preventDefault();
            $('#mensaje').hide().text('');

            // Validar campos
            const correo = $('#correo').val().trim();
            const pass = $('#pass').val().trim();

            if (!correo || !pass) {
                $('#mensaje').text('Todos los campos son obligatorios').show();
                return;
            }

            // Enviar por AJAX
            $.ajax({
                url: 'Final/administrador/login_verifica.php',
                type: 'POST',
                dataType: 'json',
                data: { correo: correo, pass: pass },
                success: function(res) {
                    if (res.existe) {
                        window.location.href = 'Final/administrador/bienvenido.php';
                    } else {
                        $('#mensaje').text('Usuario o contraseña incorrectos').show();
                    }
                }
            });
        });
    });
    </script>
</body>
</html>