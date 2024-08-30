<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';
$user = 'tu_usuario';
$password = 'tu_contraseña';
$db = 'tu_base_de_datos';

$connection = new mysqli($host, $user, $password, $db);
if ($connection->connect_error) {
    die("Conexión fallida: " . $connection->connect_error);
}

// Obtener los parámetros de la solicitud de DataTables
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 0;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$search = isset($_GET['search']['value']) ? $connection->real_escape_string($_GET['search']['value']) : '';
$orderColumn = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'ASC';

// Define las columnas de la tabla que se pueden ordenar
$columns = ['columna1', 'columna2', 'columna3']; // Cambia estos nombres por los de tus columnas

// Construir la consulta base
$queryBase = "SELECT * FROM tu_tabla";

// Filtrado
if ($search) {
    $queryBase .= " WHERE (columna1 LIKE '%$search%' OR columna2 LIKE '%$search%' OR columna3 LIKE '%$search%')";
}

// Obtener el número total de registros filtrados
$queryCountFiltered = "SELECT COUNT(*) as total FROM ($queryBase) as temp";
$totalFilteredResult = $connection->query($queryCountFiltered);
$totalFiltered = $totalFilteredResult->fetch_assoc()['total'];

// Añadir ordenación
$queryBase .= " ORDER BY " . $columns[$orderColumn] . " $orderDir";

// Añadir limitación de paginación
$queryBase .= " LIMIT $start, $length";

// Ejecutar la consulta final
$result = $connection->query($queryBase);

// Obtener los datos
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Obtener el número total de registros en la tabla sin filtrar
$queryCountTotal = "SELECT COUNT(*) as total FROM tu_tabla";
$totalResult = $connection->query($queryCountTotal);
$totalRecords = $totalResult->fetch_assoc()['total'];

// Preparar la respuesta para DataTables
$response = [
    "draw" => $draw,
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
];

// Devolver la respuesta en formato JSON
echo json_encode($response);

$connection->close();
