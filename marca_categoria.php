<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'Conexion.php';
error_reporting(0);
ini_set('display_errors', 0);

// Obtener acción
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'listar_marcas':
        listarMarcas($pdo);
        break;
    case 'guardar_marca':
        guardarMarca($pdo);
        break;
    case 'editar_marca':
        editarMarca($pdo);
        break;
    case 'eliminar_marca':
        eliminarMarca($pdo);
        break;

    case 'listar_categorias':
        listarCategorias($pdo);
        break;
    case 'guardar_categoria':
        guardarCategoria($pdo);
        break;
    case 'editar_categoria':
        editarCategoria($pdo);
        break;
    case 'eliminar_categoria':
        eliminarCategoria($pdo);
        break;
    case 'restaurar_marca':
        restaurarMarca($pdo);
        break;
    case 'restaurar_categoria':
        restaurarCategoria($pdo);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

// ============================
// FUNCIONES PARA MARCA
// ============================

function listarMarcas($pdo) {
    $stmt = $pdo->prepare("CALL sp_listar_marcas()");
    $stmt->execute();
    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($marcas);
}

function guardarMarca($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("CALL sp_insertar_marca(:nombre)");
    $stmt->execute(['nombre' => $data['nombre']]);
    echo json_encode(['mensaje' => 'Marca registrada correctamente']);
}

function editarMarca($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("CALL sp_actualizar_marca(:id, :nombre)");
    $stmt->execute([
        'id' => $data['idMarca'],
        'nombre' => $data['nombre']
    ]);
    echo json_encode(['mensaje' => 'Marca actualizada correctamente']);
}

function eliminarMarca($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("CALL sp_eliminar_marca(:id)");
    $stmt->execute(['id' => $data['idMarca']]);
    echo json_encode(['mensaje' => 'Marca eliminada correctamente']);

}

function restaurarMarca($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("CALL sp_restaurar_marca(:id)");
    $stmt->execute(['id' => $data['idMarca']]);
    echo json_encode(['mensaje' => 'Marca restaurada correctamente']);
}

// ============================
// FUNCIONES PARA CATEGORIA
// ============================

function listarCategorias($pdo) {
    $stmt = $pdo->prepare("CALL sp_listar_categorias()");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categorias);
}

function guardarCategoria($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("CALL sp_insertar_categoria(:nombre)");
    $stmt->execute(['nombre' => $data['nombre']]);
    echo json_encode(['mensaje' => 'Categoría registrada correctamente']);
}

function editarCategoria($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("CALL sp_actualizar_categoria(:id, :nombre)");
    $stmt->execute([
        'id' => $data['idCategoria'],
        'nombre' => $data['nombre']
    ]);
    echo json_encode(['mensaje' => 'Categoría actualizada correctamente']);
}

function eliminarCategoria($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("CALL sp_eliminar_categoria(:id)");
    $stmt->execute(['id' => $data['idCategoria']]);
    echo json_encode(['mensaje' => 'Categoría eliminada correctamente']);
}

function restaurarCategoria($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("CALL sp_restaurar_categoria(:id)");
    $stmt->execute(['id' => $data['idCategoria']]);
    echo json_encode(['mensaje' => 'Categoría restaurada correctamente']);
}

?>
