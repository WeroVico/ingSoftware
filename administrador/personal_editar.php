<?php
// personal_editar.php
require "funciones/conecta.php";
require_once 'menu.php';

$con = conecta();

// Validación segura del ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header("Location: personal_lista.php");
    exit();
}

// Consulta preparada
$stmt = $con->prepare("SELECT id, nombre, apellidos, correo, rol FROM personal_table WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: personal_lista.php");
    exit();
}
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edición de empleados</title>
    <script src="jquery-3.3.1.min.js"></script>    
    
    <style>
      body {        
        background: #f0f2f5;              
      }
      /* Estilo para los mensajes de error */
      .error {
        color: red;
        margin-top: 5px;
      }
      /* Estilos básicos para inputs y labels */
      label {
        display: block;
        margin-top: 10px;
      }
      input, select {
        width: 300px;
        padding: 5px;
      }

      .mainDiv {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 50vh;
        margin: 0;
      }

      .editar {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 350px;
      }
      h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 25px;
      }

      .detail-item {margin-bottom: 20px;}

      .detail-item label {
        display: block;
        color: #7f8c8d;
        font-size: 0.9em;
        margin-bottom: 5px;
      }

      .detail-item span {
        display: block;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        color: #2c3e50;
        font-weight: 500;
      }

      button {
        display: block;
        text-align: center;
        background:rgb(163, 218, 255);
        color: white;
        padding: 12px;
        border-radius: 8px;
        text-decoration: none;
        margin-top: 20px;
        color: black;
      }

      button.volver {
        background:rgb(206, 72, 72);
      }

      button.guardar {
        background:rgb(49, 165, 20);
      }

      button:hover {
        background:rgba(41, 0, 190, 0.37);
      }
      
      .status-activo {
        color: #27ae60;
        font-weight: bold;
      }

      .status-inactivo {
        color: #e74c3c;
        font-weight: bold;
      }
      
      .formBottom {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0;     
      }
    </style>



</head>
<body>
    <h2>Edición de empleados</h2>
    <div class="mainDiv">
      <form id="formEditar" class="editar" action="personal_update.php" method="POST" enctype="multipart/form-data" novalidate>    
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">        
        <!-- — ID — -->
        <label for="id"><?php echo("ID:".$id); ?></label>

        <!-- — Nombre — -->
        <label for="nombre">Nombre</label>
        <input 
          type="text"
          id="nombre"
          name="nombre"
          value='<?php echo htmlspecialchars($row["nombre"], ENT_QUOTES, "UTF-8"); ?>'
        >
        
        <!-- — Apellidos — -->
        <label for="apellidos">Apellidos</label>
        <input 
          type="text"
          id="apellidos"
          name="apellidos"
          value='<?php echo htmlspecialchars($row["apellidos"], ENT_QUOTES, "UTF-8"); ?>'
        >
        
        <!-- — Correo — -->
        <label for="correo">Correo</label>
        <input 
          type="text"
          id="correo"
          name="correo"
          value='<?php echo htmlspecialchars($row["correo"], ENT_QUOTES, "UTF-8"); ?>'
        >

        <!-- DIV para mostrar mensaje de correo duplicado -->
        <div id="errorCorreo" class="error" style="display:none;"></div>

        <!-- — Rol — -->
        <label for="rol">Rol:</label>
        <select name="rol" id="rol">
          <option value="0" <?php if($row['rol']==0) echo 'selected';?>>Empleado</option>
          <option value="1" <?php if($row['rol']==1) echo 'selected';?>>Gerente</option>
        </select>

        <!-- — Contraseña — -->
        <label for="password">Contraseña (Dejar vacío para no cambiar): </label>
        <input type="password" id="password" name="password" placeholder="************">

        <!-- Foto -->
        <label for="foto">Nueva Foto (opcional):</label>
        <input type="file" name="foto" accept="image/*">
        
        <!-- — Botones volver/guardar — -->
        <div class="formBottom">
          <button class="volver" type="button" onclick="location.href='personal_lista.php'"><b>Volver</b></button>
          <button id="btnGuardar" class="guardar" type='submit'><b>Guardar datos</b></button>

        </div>

        <!-- Mensaje de error -->
        <div id='errorCampos' class='error' style='display:none;'></div>
      </form>
    </div>  
    <script>
      $(document).ready(function() {
        const form = $('#formEditar');
        const errDiv = $('#errorCampos');
        
        form.submit(function(e) {
            e.preventDefault();
            errDiv.hide();
            
            // Validar campos
            const campos = {
                nombre: $('#nombre').val().trim(),
                apellidos: $('#apellidos').val().trim(),
                correo: $('#correo').val().trim(),
                rol: $('#rol').val()
            };
            
            if (Object.values(campos).some(v => !v)) {
                errDiv.text('Faltan campos por llenar').show().delay(5000).fadeOut();
                return;
            }
            
            // Crear FormData para enviar archivos
            const formData = new FormData(this);
            
            // AJAX para guardar
            $.ajax({
                url: 'personal_update.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function() {
                    window.location.href = 'personal_lista.php';
                },
                error: function(xhr) {
                    alert('Error al guardar: ' + xhr.responseText);
                }
            });
        });

        // Validación de correo en tiempo real
        $('#correo').on('blur', function() {
            const correo = $(this).val().trim();
            const idActual = <?php echo $row['id']; ?>;
            
            if (!correo) return;
            
            $.post('verifica_correo.php', { correo, id_actual: idActual }, function(res) {
                if (res.existe) {
                    $('#errorCorreo').text(`El correo ${correo} ya existe`).show().delay(5000).fadeOut();
                    $('#btnGuardar').prop('disabled', true);
                } else {
                    $('#errorCorreo').hide();
                    $('#btnGuardar').prop('disabled', false);
                }
            }, 'json');
        });
      });
    </script>
</body>
</html>