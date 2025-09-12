<!DOCTYPE html>
<!-- login.php -->
<html>
<head>
    <title>Login</title>
    <script src="jquery-3.3.1.min.js"></script>
    <style>
        .login-box {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            box-sizing: border-box;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            width: 100%;
            cursor: pointer;
        }
        #mensaje {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <form id="loginForm">
            <input type="email" name="correo" id="correo" placeholder="Correo electrónico" required>
            <input type="password" name="pass" id="pass" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <div id="mensaje"></div>
    </div>

    <script>
    $(document).ready(function() {
        $('#loginForm').submit(function(e) {
            e.preventDefault();
            
            // Validación de campos
            const correo = $('#correo').val().trim();
            const pass = $('#pass').val().trim();
            $('#mensaje').hide().text('');
            
            if (!correo || !pass) {
                $('#mensaje').text('Todos los campos son obligatorios').show();
                return;
            }
            
            // Enviar por AJAX
            $.ajax({
                url: 'login_verifica.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    correo: correo,
                    pass: pass
                },
                success: function(res) {
                    if (res.existe) {
                        window.location.href = 'bienvenido.php';
                    } else {
                        $('#mensaje').text('Credenciales incorrectas o usuario inactivo').show();
                    }
                },
                error: function() {
                    $('#mensaje').text('Error en la conexión').show();
                }
            });
        });
    });
    </script>
</body>
</html>