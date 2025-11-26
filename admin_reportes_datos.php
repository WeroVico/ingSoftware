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

// Recibir filtros de fechas
$fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
$fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));

$inicio_sql = "$fecha_inicio 00:00:00";
$fin_sql = "$fecha_fin 23:59:59";

$respuesta = [];

// 1. GRÁFICO: Estado Actual de los Lockers
$sql_estado = "SELECT status, COUNT(*) as total FROM locker GROUP BY status";
$res_estado = $con->query($sql_estado);
$datos_estado = [];
while($row = $res_estado->fetch_assoc()) {
    $datos_estado[$row['status']] = $row['total'];
}
$respuesta['estado_lockers'] = $datos_estado;

// 2. GRÁFICO: Historial de Reservas por Día
$sql_reservas = "SELECT DATE(fecha_inicio) as fecha, COUNT(*) as total 
                 FROM reservacion 
                 WHERE fecha_inicio BETWEEN ? AND ? 
                 GROUP BY DATE(fecha_inicio) 
                 ORDER BY fecha ASC";
$stmt = $con->prepare($sql_reservas);
$stmt->bind_param("ss", $inicio_sql, $fin_sql);
$stmt->execute();
$res_reservas = $stmt->get_result();

$etiquetas_fechas = [];
$valores_reservas = [];
while($row = $res_reservas->fetch_assoc()) {
    $etiquetas_fechas[] = date("d/m", strtotime($row['fecha']));
    $valores_reservas[] = $row['total'];
}
$respuesta['historial_reservas'] = [
    'labels' => $etiquetas_fechas,
    'data' => $valores_reservas
];

// 3. KPI: Total de acciones en Logs
$sql_logs = "SELECT COUNT(*) as total FROM sistema_logs WHERE fecha BETWEEN ? AND ?";
$stmt_l = $con->prepare($sql_logs);
$stmt_l->bind_param("ss", $inicio_sql, $fin_sql);
$stmt_l->execute();
$respuesta['total_logs'] = $stmt_l->get_result()->fetch_assoc()['total'];

// 4. TOP 5 Lockers más usados
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

$top_etiquetas = [];
$top_valores = [];
while($row = $res_top->fetch_assoc()) {
    $top_etiquetas[] = $row['etiqueta_completa'];
    $top_valores[] = $row['uso'];
}
$respuesta['top_lockers'] = [
    'labels' => $top_etiquetas,
    'data' => $top_valores
];

header('Content-Type: application/json');
echo json_encode($respuesta);
?>