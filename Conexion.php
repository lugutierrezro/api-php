<?php
// Conexion.php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=bdPinotello;charset=utf8", "root", ""); 
    // Cambia "root" y "" por tu usuario y contraseña de MySQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => "Error de conexión: " . $e->getMessage()]));
}
?>