<?php
require_once 'menu.php';
require "funciones/conecta.php";
$con = conecta();

// Verificar si ya tiene reserva
$id_usuario = $_SESSION['id_usuario'];
$sql_reserva = "SELECT * FROM reservacion WHERE id_usuario = ? AND status = 'activa'";
$stmt_reserva = $con->prepare($sql_reserva);
$stmt_reserva->bind_param("i", $id_usuario);
$stmt_reserva->execute();
if ($stmt_reserva->get_result()->num_rows > 0) {
    header("Location: info_reservacion.php");
    exit();
}

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
        .locker-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 15px; margin-top: 20px; }
        .locker { padding: 15px; border: 1px solid #ddd; border-radius: 8px; text-align: center; font-weight: bold; cursor: pointer; transition: all 0.2s; }
        .locker.disponible { background-color: #d4edda; border-color: #c3e6cb; }
        .locker.disponible:hover { background-color: #28a745; color: white; transform: scale(1.1); }
        .locker.ocupado, .locker.mantenimiento { background-color: #f8d7da; border-color: #f5c6cb; cursor: not-allowed; color: #721c24; }
        .pagination-container { margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Buscador de Lockers</h3>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Módulo:</label>
                        <select id="selectModulo" class="form-select">
                            <option value="0">Todos los módulos</option>
                            <?php while($row = $res_modulos->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado:</label>
                        <select id="filterStatus" class="form-select">
                            <option value="">Todos</option>
                            <option value="disponible">Disponible</option>
                            <option value="ocupado">Ocupado</option>
                            <option value="mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Buscar (ID/Etiqueta):</label>
                        <input type="text" id="searchLocker" class="form-control" placeholder="Ej. X-10">
                    </div>
                </div>

                <hr>
                
                <div id="locker-container">
                    <p class="text-center text-muted">Usa los filtros para encontrar tu locker.</p>
                </div>

                <nav class="pagination-container">
                    <ul class="pagination justify-content-center" id="pagination-controls">
                        </ul>
                </nav>
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
                        <label class="form-label">Duración:</label>
                        <select id="selectDuracion" class="form-select">
                            <option value="2">2 horas</option>
                            <option value="3">3 horas</option>
                            <option value="4">4 horas</option>
                        </select>
                    </div>
                    <p class="small text-muted">Horario: 7:00 AM - 12:00 AM</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirm-button" class="btn btn-primary">Reservar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="jquery-3.3.1.min.js"></script>
    <script>
        let currentPage = 1;
        let selectedLockerId = null;
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

        function loadLockers(page = 1) {
            const idModulo = $('#selectModulo').val();
            const search = $('#searchLocker').val();
            const status = $('#filterStatus').val();

            $('#locker-container').html('<div class="text-center spinner-border text-primary" role="status"></div>');

            $.ajax({
                url: 'lockers_get.php',
                type: 'POST',
                dataType: 'json',
                data: { 
                    id_modulo: idModulo,
                    search: search,
                    status: status,
                    page: page
                },
                success: function(response) {
                    let html = '';
                    const lockers = response.data;
                    const pagination = response.pagination;

                    if (lockers.length > 0) {
                        html += '<div class="locker-grid">';
                        lockers.forEach(locker => {
                            html += `<div class="locker ${locker.status}" data-id="${locker.id}" data-label="${locker.etiqueta_completa}">
                                        <i class="fas fa-archive fa-2x mb-2"></i>
                                        <div>${locker.etiqueta_completa}</div>
                                     </div>`;
                        });
                        html += '</div>';
                    } else {
                        html += '<div class="alert alert-warning text-center">No se encontraron lockers con esos criterios.</div>';
                    }
                    $('#locker-container').html(html);

                    // Renderizar Paginación
                    let pagHtml = '';
                    if (pagination.total_pages > 1) {
                        pagHtml += `<li class="page-item ${pagination.current_page == 1 ? 'disabled' : ''}">
                                        <button class="page-link" onclick="loadLockers(${pagination.current_page - 1})">Anterior</button>
                                    </li>`;
                        
                        for(let i=1; i<=pagination.total_pages; i++){
                             pagHtml += `<li class="page-item ${pagination.current_page == i ? 'active' : ''}">
                                            <button class="page-link" onclick="loadLockers(${i})">${i}</button>
                                        </li>`;
                        }

                        pagHtml += `<li class="page-item ${pagination.current_page == pagination.total_pages ? 'disabled' : ''}">
                                        <button class="page-link" onclick="loadLockers(${pagination.current_page + 1})">Siguiente</button>
                                    </li>`;
                    }
                    $('#pagination-controls').html(pagHtml);
                }
            });
        }

        $(document).ready(function() {
            // Cargar lockers al inicio
            loadLockers();

            // Eventos de filtros (recargan la búsqueda)
            $('#selectModulo, #filterStatus').on('change', function() { loadLockers(1); });
            $('#searchLocker').on('keyup', function() { loadLockers(1); });

            // Click en locker
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
                        data: { id_locker: selectedLockerId, duracion: duracion },
                        success: function(res) {
                            if (res.success) {
                                window.location.href = 'bienvenido.php?status=success';
                            } else {
                                alert(res.message);
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>