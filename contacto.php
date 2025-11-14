<?php
// contacto.php
require_once 'menu.php'; // Incluye el menú y la validación de sesión
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto y Redes Sociales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="css/estilo.css?v=1.2">

</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Contacto y Redes Sociales</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-center">¡Síguenos o envíanos un correo para cualquier duda!</p>
                        
                        <a href="mailto:angel.tinoco8140@alumnos.udg.mx" class="social-link link-email">
                            <i class="fas fa-envelope"></i> Enviar Correo
                        </a>

                        <a href="#" class="social-link link-x">
                            <img src="imgs/logo x.png" alt="Logo de X" class="logo-x">
                            Síguenos en X
                        </a>

                        <a href="#" class="social-link link-instagram">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>

                        <a href="https://github.com/WeroVico/ingSoftware" class="social-link link-github">
                            <i class="fab fa-github"></i> Github
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>