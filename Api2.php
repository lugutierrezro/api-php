<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

// Si es GET, mostramos un mensaje simple
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        "status" => "ok",
        "message" => "API de Sensor Solar activa. Envia datos usando POST."
    ]);
    exit();
}

// ===== CONEXIÓN A POSTGRESQL =====
$host = "dpg-d38bpinfte5s73buuht0-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "solarpanel";
$user = "solarpanel_user";
$password = "oBsuyBBYSmxFICCdUxWOb97QK49EeAxG";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "No se pudo conectar a la base de datos"]);
    exit();
}

// ===== LEER JSON DEL POST =====
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['voltage']) || !isset($input['current']) || !isset($input['power']) || !isset($input['temperature']) || !isset($input['humidity'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit();
}

// ===== INSERTAR DATOS =====
$query = "INSERT INTO sensor_readings (voltage, current, power, temperature, humidity) VALUES ($1, $2, $3, $4, $5)";
$result = pg_query_params($conn, $query, [
    $input['voltage'],
    $input['current'],
    $input['power'],
    $input['temperature'],
    $input['humidity']
]);

if ($result) {
    echo json_encode(["status" => "success", "message" => "Datos guardados correctamente"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error al insertar los datos"]);
}

pg_close($conn);
?>
