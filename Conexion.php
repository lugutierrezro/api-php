<?php
// Conexion.php - usando los datos de Railway

$host = 'nozomi.proxy.rlwy.net';
$dbname = 'railway';
$username = 'root';
$password = 'iAiLhGEhumdUbRsrdVsbIWEGaQTvxVGl';
$port = '23370';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", 
        $username, 
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => "Error de conexión: " . $e->getMessage()]));
}
?>