<?php
$host = "gondola.proxy.rlwy.net";
$db = "railway";
$user = "root";
$pass = "mEGOhqhgNhZDyIGBtOdWSaWTwiAJCxUw";
$port = 59100;
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Opcional: echo "Conexión exitosa";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>