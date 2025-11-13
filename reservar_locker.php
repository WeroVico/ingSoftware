<?php
require_once 'menu.php';
require "funciones/conecta.php";
$con = conecta();

// 1. VERIFICAR SI EL USUARIO YA TIENE UNA RESERVA ACTIVA
$id_usuario = $_SESSION['id_usuario'];
$sql_reserva = "SELECT * FROM reservacion WHERE id_usuario = ? AND status = 'activa'";
$stmt_reserva = $con->prepare($sql_reserva);
$stmt_reserva->bind_param("i", $id_usuario);
$stmt_reserva->execute();
$res_reserva = $stmt_reserva->get_result();
if ($res_reserva->num_rows > 0) {
    // Si ya tiene, lo redirigimos a la página de información
    header("Location: info_reservacion.php");
    exit();
}

// 2. OBTENER LOS MÓDULOS DISPONIBLES
$sql_modulos = "SELECT * FROM modulo ORDER BY nombre";
$res_modulos = $con->query($sql_modulos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar un Locker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .locker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .locker {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }
        .locker.disponible {
            background-color: #d4edda; /* Verde */
            border-color: #c3e6cb;
        }
        .locker.disponible:hover {
            background-color: #28a745;
            color: white;
            transform: scale(1.1);
        }
        .locker.ocupado, .locker.mantenimiento {
            background-color: #f8d7da; /* Rojo */
            border-color: #f5c6cb;
            cursor: not-allowed;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h3>Selecciona un Locker Disponible</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="selectModulo" class="form-label"><strong>1. Elige un Módulo:</strong></label>
                    <select id="selectModulo" class="form-select">
                        <option selected disabled>-- Selecciona un edificio --</option>
                        <?php while($row = $res_modulos->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                        } ?>
                    </select>
                </div>
                <hr>
                <div id="locker-container">
                    <p class="text-muted text-center">Selecciona un módulo para ver los lockers disponibles.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Reservación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Vas a reservar el locker <strong id="locker-label"></strong>.</p>
                    <div class="mb-3">
                        <label for="selectDuracion" class="form-label"><strong>Selecciona la duración:</strong></label>
                        <select id="selectDuracion" class="form-select">
                            <option value="2">2 horas</option>
                            <option value="3">3 horas</option>
                            <option value="4">4 horas</option>
                            <option value="5">poquito xd</option>
                        </select>
                    </div>
                    <p class="small text-muted">Recuerda que el horario de servicio es de 7:00 AM a 9:00 PM.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirm-button" class="btn btn-primary">Sí, Reservar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="jquery-3.3.1.min.js"></script>
    <script>
        function unirseListaEspera(idModulo) {
            if (confirm('Todos los lockers de este módulo están ocupados. ¿Quieres unirte a la lista de espera para ser notificado cuando uno se libere?')) {
                $.ajax({
                    url: 'entrar_lista_espera.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { id_modulo: idModulo },
                    success: function(response) {
                        if (response.success) {
                            alert('¡Listo! Has sido añadido a la lista de espera. Recibirás un correo cuando un locker se libere.');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('No se pudo procesar la solicitud. Intenta de nuevo más tarde.');
                    }
                });
            }
        }

        $(document).ready(function() {
            let selectedLockerId = null;
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

            $('#selectModulo').on('change', function() {
                const idModulo = $(this).val();
                $('#locker-container').html('<p class="text-center">Cargando lockers...</p>');

                $.ajax({
                    url: 'lockers_get.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { id_modulo: idModulo },
                    success: function(response) {
                        let html = '';
                        let disponibles = 0;
                        if (response.length > 0) {
                            html += '<div class="locker-grid">';
                            response.forEach(locker => {
                                if (locker.status === 'disponible') {
                                    disponibles++;
                                }
                                html += `<div class="locker ${locker.status}" data-id="${locker.id}" data-label="${locker.etiqueta_completa}">
                                            <i class="fas fa-archive"></i>
                                            <div>${locker.etiqueta_completa}</div>
                                         </div>`;
                            });
                            html += '</div>';
                        } else {
                            html += '<p class="text-center">No hay lockers en este módulo.</p>';
                        }
                        
                        $('#locker-container').html(html);

                        if (disponibles === 0 && response.length > 0) {
                            $('#locker-container').append(
                                `<div class="text-center mt-4">
                                    <p class="fw-bold">No hay lockers disponibles en este momento.</p>
                                    <button class="btn btn-warning" onclick="unirseListaEspera(${idModulo})">Anotarme en la lista de espera</button>
                                </div>`
                            );
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert(`Error al cargar los lockers. \n\nDetalles: ${textStatus} - ${errorThrown}`);
                        console.log(jqXHR.responseText);
                        $('#locker-container').html('<p class="text-center text-danger">No se pudieron cargar los lockers.</p>');
                    }
                });
            });

            $('#locker-container').on('click', '.locker.disponible', function() {
                selectedLockerId = $(this).data('id');
                $('#locker-label').text($(this).data('label'));
                confirmModal.show();
            });

            $('#confirm-button').on('click', function() {
                const duracion = $('#selectDuracion').val();
                if (selectedLockerId) {
                    $.ajax({
                        url: 'reservar_procesa.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_locker: selectedLockerId,
                            duracion: duracion
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.href = 'bienvenido.php?status=success';
                            } else {
                                alert('Error: ' + response.message);
                                location.reload();
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>