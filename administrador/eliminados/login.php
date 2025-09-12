<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login de Empleados</title>
    <script src="jquery-3.3.1.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 300px;
        }
        .login-box h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        .login-box label {
            display: block;
            margin-top: 10px;
            color: #555;
        }
        .login-box input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        #mensaje {
            color: red;
            margin-top: 15px;
            text-align: center;
        }
        .btn-login {
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            background: #3498db;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-login:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Iniciar Sesión</h2>
        <form id="formLogin" novalidate>
            <label for="correo">Correo (usuario)</label>
            <input type="email" id="correo" name="correo" placeholder="tu@correo.com">

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" placeholder="********">

            <button type="submit" class="btn-login">Entrar</button>
        </form>
        <div id="mensaje"></div>
    </div>

    <script>
    $(function(){
        $('#formLogin').on('submit', function(e){
            e.preventDefault();
            $('#mensaje').text('');

            const correo = $('#correo').val().trim();
            const pass   = $('#password').val().trim();

            if (!correo || !pass) {
                $('#mensaje').text('Por favor, llena todos los campos.');
                return;
            }

            $.ajax({
                url: 'auth.php',
                method: 'POST',
                dataType: 'json',
                data: { correo, password: pass },
                success: function(res) {
                   console.log("Respuesta de auth.php:", res);
                    if (res.existe) {
                        window.location.href = 'bienvenido.php';
                    } else {
                        $('#mensaje').text('Usuario o contraseña incorrectos, o cuenta inactiva.');
                    }
                },
                error: function(xhr, status, err){
                   console.error("Error AJAX:", status, err, xhr.responseText);
                    $('#mensaje').text('Error de comunicación con el servidor.');
                }
            });
        });
    });
    </script>
</body>
</html>
