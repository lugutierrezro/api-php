<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'Conexion.php';
error_reporting(0);
ini_set('display_errors', 0);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'listar_productos':
        listarProductos($pdo);
        break;
    case 'guardar_producto':
        guardarProducto($pdo);
        break;
    case 'editar_producto':
        editarProducto($pdo);
        break;
    case 'eliminar_producto':
        eliminarProducto($pdo);
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

// ==================== FUNCIONES ====================

function listarProductos($pdo) {
    $buscar = $_GET['buscar'] ?? '';
    $stmt = $pdo->prepare("CALL sp_listar_Productos(:pbuscar)");
    $stmt->execute(['pbuscar' => $buscar]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos);
}

function guardarProducto($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $params = [
        ':pidProducto' => $data['idProducto'],
        ':pnombre' => $data['nombre'],
        ':pprecioCompra' => $data['precioCompra'],
        ':pprecioVenta' => $data['precioVenta'],
        ':pfechaIngreso' => $data['fechaIngreso'],
        ':pfechaVencimiento' => $data['fechaVencimiento'],
        ':pidCategoria' => $data['idCategoria'],
        ':pidMarca' => $data['idMarca']
    ];

    $stmt = $pdo->prepare("CALL sp_guardar_producto(:pidProducto, :pnombre, :pprecioCompra, :pprecioVenta, :pfechaIngreso, :pfechaVencimiento, :pidCategoria, :pidMarca)");
    $stmt->execute($params);
    echo json_encode(['mensaje' => 'Producto guardado correctamente']);
}

function editarProducto($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $params = [
        ':pidProducto' => $data['idProducto'],
        ':pnombre' => $data['nombre'],
        ':pprecioCompra' => $data['precioCompra'],
        ':pprecioVenta' => $data['precioVenta'],
        ':pfechaIngreso' => $data['fechaIngreso'],
        ':pfechaVencimiento' => $data['fechaVencimiento'],
        ':pidCategoria' => $data['idCategoria'],
        ':pidMarca' => $data['idMarca']
    ];

    $stmt = $pdo->prepare("CALL sp_editar_Producto(:pidProducto, :pnombre, :pprecioCompra, :pprecioVenta, :pfechaIngreso, :pfechaVencimiento, :pidCategoria, :pidMarca)");
    $stmt->execute($params);
    echo json_encode(['mensaje' => 'Producto editado correctamente']);
}

function eliminarProducto($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $idProducto = $data['idProducto'] ?? null;

    if (!$idProducto) {
        echo json_encode(['error' => 'ID de producto no proporcionado']);
        return;
    }

    $stmt = $pdo->prepare("CALL sp_eliminar_Producto(:pidProducto)");
    $stmt->execute([':pidProducto' => $idProducto]);

    echo json_encode(['mensaje' => 'Producto eliminado correctamente (estado inactivo)']);
}
