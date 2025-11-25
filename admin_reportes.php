<?php
require_once 'menu.php';
// Validación de seguridad extra por si acceden directo
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header("Location: bienvenido.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes y Estadísticas</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-chart-line"></i> Dashboard de Reportes</h2>
        
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Fecha Inicio:</label>
                        <input type="date" id="fecha_inicio" class="form-control" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Fin:</label>
                        <input type="date" id="fecha_fin" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-primary w-100" onclick="cargarDatos()">
                            <i class="fas fa-filter"></i> Filtrar Datos
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-4 text-center">
            <div class="col-md-6">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Actividad Total (Logs)</div>
                    <div class="card-body">
                        <h1 class="card-title display-4" id="kpi_logs">0</h1>
                        <p class="card-text">Acciones registradas en el periodo</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Lockers Disponibles Hoy</div>
                    <div class="card-body">
                        <h1 class="card-title display-4" id="kpi_disponibles">0</h1>
                        <p class="card-text">Listos para usarse</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header">Historial de Reservas</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartReservas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">Estado Actual de Lockers</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartEstado"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">Top 5 Lockers Más Usados</div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="chartTop"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="jquery-3.3.1.min.js"></script>
    <script>
        // Variables globales para las instancias de los gráficos
        let chartReservas, chartEstado, chartTop;

        function cargarDatos() {
            const inicio = $('#fecha_inicio').val();
            const fin = $('#fecha_fin').val();

            $.ajax({
                url: 'admin_reportes_datos.php',
                type: 'POST',
                dataType: 'json',
                data: { fecha_inicio: inicio, fecha_fin: fin },
                success: function(res) {
                    if(res.error) {
                        alert(res.error);
                        return;
                    }

                    // 1. Actualizar KPIs
                    $('#kpi_logs').text(res.total_logs);
                    $('#kpi_disponibles').text(res.estado_lockers.disponible || 0);

                    // 2. Renderizar Gráfico de Reservas (Línea)
                    renderLineChart(res.historial_reservas);

                    // 3. Renderizar Gráfico de Estado (Pastel)
                    renderPieChart(res.estado_lockers);

                    // 4. Renderizar Top Lockers (Barras)
                    renderBarChart(res.top_lockers);
                },
                error: function() {
                    alert('Error al cargar los reportes');
                }
            });
        }

        function renderLineChart(data) {
            const ctx = document.getElementById('chartReservas').getContext('2d');
            if(chartReservas) chartReservas.destroy();

            chartReservas = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Nuevas Reservas',
                        data: data.data,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: { maintainAspectRatio: false, responsive: true }
            });
        }

        function renderPieChart(data) {
            const ctx = document.getElementById('chartEstado').getContext('2d');
            if(chartEstado) chartEstado.destroy();

            // Preparamos datos seguros (si no hay, ponemos 0)
            const disp = data.disponible || 0;
            const ocup = data.ocupado || 0;
            const mant = data.mantenimiento || 0;

            chartEstado = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Disponible', 'Ocupado', 'Mantenimiento'],
                    datasets: [{
                        data: [disp, ocup, mant],
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107']
                    }]
                },
                options: { maintainAspectRatio: false, responsive: true }
            });
        }

        function renderBarChart(data) {
            const ctx = document.getElementById('chartTop').getContext('2d');
            if(chartTop) chartTop.destroy();

            chartTop = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Cantidad de veces reservado',
                        data: data.data,
                        backgroundColor: '#6610f2'
                    }]
                },
                options: { 
                    maintainAspectRatio: false, 
                    responsive: true,
                    indexAxis: 'y' // Barras horizontales
                }
            });
        }

        // Cargar al inicio
        $(document).ready(function() {
            cargarDatos();
        });
    </script>
</body>
</html>