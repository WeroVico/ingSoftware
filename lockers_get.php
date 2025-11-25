<?php
// lockers_get.php
require "funciones/conecta.php";
$con = conecta();

// 1. Recibir parámetros de filtros y paginación
$id_modulo = isset($_POST['id_modulo']) ? filter_var($_POST['id_modulo'], FILTER_VALIDATE_INT) : 0;
$busqueda  = isset($_POST['search']) ? trim($_POST['search']) : '';
$status    = isset($_POST['status']) ? trim($_POST['status']) : '';
$pagina    = isset($_POST['page']) ? filter_var($_POST['page'], FILTER_VALIDATE_INT) : 1;
$limite    = 20; // Cantidad de lockers por página
$offset    = ($pagina - 1) * $limite;

// 2. Construir la consulta base
$sql = "SELECT id, etiqueta_completa, status, id_modulo FROM locker WHERE 1=1";
$params = [];
$types = "";

// Aplicar filtro de Módulo (si se seleccionó uno)
if ($id_modulo > 0) {
    $sql .= " AND id_modulo = ?";
    $params[] = $id_modulo;
    $types .= "i";
}

// Aplicar filtro de Búsqueda (por etiqueta, ej: "X-10")
if (!empty($busqueda)) {
    $sql .= " AND etiqueta_completa LIKE ?";
    $params[] = "%" . $busqueda . "%";
    $types .= "s";
}

// Aplicar filtro de Estado (disponible, ocupado, etc)
if (!empty($status)) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

// 3. Contar el total de resultados (para la paginación)
$sql_count = str_replace("SELECT id, etiqueta_completa, status, id_modulo", "SELECT COUNT(*) as total", $sql);
$stmt_count = $con->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_records = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limite);

// 4. Obtener los lockers de la página actual
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limite;
$params[] = $offset;
$types .= "ii";

$stmt = $con->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$lockers = [];
while ($row = $result->fetch_assoc()) {
    $lockers[] = $row;
}

// 5. Devolver respuesta estructurada
header('Content-Type: application/json');
echo json_encode([
    'data' => $lockers,
    'pagination' => [
        'total_records' => $total_records,
        'total_pages' => $total_pages,
        'current_page' => $pagina
    ]
]);
?>