<?php
// admin_reportes_datos.php
session_start();
require "funciones/conecta.php";
$con = conecta();

// Seguridad: Solo administradores
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

// Recibir filtros de fechas (por defecto últimos 30 días)
$fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
$fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));

// Ajustar formato para consultas (inicio del día y fin del día)
$inicio_sql = "$fecha_inicio 00:00:00";
$fin_sql = "$fecha_fin 23:59:59";

$response = [];

// 1. GRÁFICO: Estado Actual de los Lockers (Pastel)
$sql_estado = "SELECT status, COUNT(*) as total FROM locker GROUP BY status";
$res_estado = $con->query($sql_estado);
$data_estado = [];
while($row = $res_estado->fetch_assoc()) {
    $data_estado[$row['status']] = $row['total'];
}
$response['estado_lockers'] = $data_estado;

// 2. GRÁFICO: Reservas por Día en el rango seleccionado (Línea)
$sql_reservas = "SELECT DATE(fecha_inicio) as fecha, COUNT(*) as total 
                 FROM reservacion 
                 WHERE fecha_inicio BETWEEN ? AND ? 
                 GROUP BY DATE(fecha_inicio) 
                 ORDER BY fecha ASC";
$stmt = $con->prepare($sql_reservas);
$stmt->bind_param("ss", $inicio_sql, $fin_sql);
$stmt->execute();
$res_reservas = $stmt->get_result();

$labels_fechas = [];
$data_reservas = [];
while($row = $res_reservas->fetch_assoc()) {
    $labels_fechas[] = date("d/m", strtotime($row['fecha']));
    $data_reservas[] = $row['total'];
}
$response['historial_reservas'] = [
    'labels' => $labels_fechas,
    'data' => $data_reservas
];

// 3. KPI: Total de acciones registradas en Logs (Métrica simple)
$sql_logs = "SELECT COUNT(*) as total FROM sistema_logs WHERE fecha BETWEEN ? AND ?";
$stmt_l = $con->prepare($sql_logs);
$stmt_l->bind_param("ss", $inicio_sql, $fin_sql);
$stmt_l->execute();
$response['total_logs'] = $stmt_l->get_result()->fetch_assoc()['total'];

// 4. TOP 5 Lockers más usados en el periodo (Barras)
$sql_top = "SELECT l.etiqueta_completa, COUNT(r.id) as uso 
            FROM reservacion r
            JOIN locker l ON r.id_locker = l.id
            WHERE r.fecha_inicio BETWEEN ? AND ?
            GROUP BY r.id_locker
            ORDER BY uso DESC
            LIMIT 5";
$stmt_t = $con->prepare($sql_top);
$stmt_t->bind_param("ss", $inicio_sql, $fin_sql);
$stmt_t->execute();
$res_top = $stmt_t->get_result();

$top_labels = [];
$top_data = [];
while($row = $res_top->fetch_assoc()) {
    $top_labels[] = $row['etiqueta_completa'];
    $top_data[] = $row['uso'];
}
$response['top_lockers'] = [
    'labels' => $top_labels,
    'data' => $top_data
];

header('Content-Type: application/json');
echo json_encode($response);
?>