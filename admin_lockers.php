<?php
// admin_lockers.php
require_once 'menu.php';
require "funciones/conecta.php";

// Máxima seguridad: si no es admin, lo sacamos de aquí.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header("Location: bienvenido.php");
    exit();
}

$con = conecta();
$sql_modulos = "SELECT * FROM modulo ORDER BY nombre";
$res_modulos = $con->query($sql_modulos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Lockers</title>
    
    <link rel="stylesheet" href="css/estilo.css?v=1.2">

</head>
<body>
    <div class="container mt-4">
        <h2>Panel de Administración de Lockers</h2>
        <p>Selecciona un módulo para ver y modificar el estado de los lockers.</p>
        <div class="card">
            <div class="card-body">
                <select id="selectModulo" class="form-select">
                    <option selected disabled>-- Selecciona un módulo --</option>
                    <?php while($row = $res_modulos->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                    } ?>
                </select>
                <div id="locker-container" class="mt-3"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Locker <span id="locker-label-modal"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="selectStatus">Nuevo estado:</label>
                    <select id="selectStatus" class="form-select">
                        <option value="disponible">Disponible</option>
                        <option value="ocupado">Ocupado</option>
                        <option value="mantenimiento">Mantenimiento</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button id="save-status-button" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <script src="jquery-3.3.1.min.js"></script>
    <script>
    $(document).ready(function() {
        let selectedLockerId = null;
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));

        function loadLockers() {
            const idModulo = $('#selectModulo').val();
            if (!idModulo) return;
            $.ajax({
                url: 'lockers_get.php',
                type: 'POST',
                dataType: 'json',
                data: { id_modulo: idModulo },
                success: function(response) {
                    let html = '<div class="locker-grid">';
                    // Ahora accedemos a response.data porque la respuesta cambió
                    const lockers = response.data || response; // Fallback por seguridad
                    
                    if(Array.isArray(lockers)) {
                        lockers.forEach(locker => {
                            html += `<div class="locker ${locker.status}" data-id="${locker.id}" data-label="${locker.etiqueta_completa}">
                                        ${locker.etiqueta_completa}
                                    </div>`;
                        });
                    }
                    html += '</div>';
                    $('#locker-container').html(html);
                }
            });
        }

        $('#selectModulo').on('change', loadLockers);

        $('#locker-container').on('click', '.locker', function() {
            selectedLockerId = $(this).data('id');
            const lockerLabel = $(this).data('label');
            $('#locker-label-modal').text(lockerLabel);
            editModal.show();
        });

        $('#save-status-button').on('click', function() {
            const newStatus = $('#selectStatus').val();
            $.ajax({
                url: 'admin_update_locker.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    id_locker: selectedLockerId,
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        editModal.hide();
                        loadLockers();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            });
        });
    });
    </script>
</body>
</html>