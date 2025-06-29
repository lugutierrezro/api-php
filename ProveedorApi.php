<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'Conexion.php'; // asegúrate que contiene $pdo
error_reporting(0);
ini_set('display_errors', 0);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'listar_proveedores':
        listarProveedores($pdo);
        break;
    case 'guardar_proveedor':
        guardarProveedor($pdo);
        break;
    case 'editar_proveedor':
        editarProveedor($pdo);
        break;
    case 'eliminar_proveedor':
        eliminarProveedor($pdo);
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

// FUNCIONES

function listarProveedores($pdo) {
    $buscar = $_GET['buscar'] ?? '';
    $stmt = $pdo->prepare("CALL sp_listar_Proveedores(:buscar)");
    $stmt->execute(['buscar' => $buscar]);
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $proveedores = array_map(function ($p) {
        return [
            'idProveedor' => $p['idProveedor'],
            'nombre' => $p['Nombre'],
            'apellido' => $p['Apellido'],
            'dni' => $p['Dni'],
            'telefono' => $p['Telefono'],
            'ruc' => $p['ruc'],
            'direccion' => $p['Direccion']
        ];
    }, $proveedores);

    echo json_encode($proveedores);
}

function guardarProveedor($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $params = [
        ':p_nombre' => $data['nombre'],
        ':p_apellido' => $data['apellido'],
        ':p_dni' => $data['dni'],
        ':p_direccion' => $data['direccion'],
        ':p_telefono' => $data['telefono'],
        ':p_ruc' => $data['ruc']
    ];

    $stmt = $pdo->prepare("CALL sp_guardar_Proveedor(:p_nombre, :p_apellido, :p_dni, :p_direccion, :p_telefono, :p_ruc)");
    $stmt->execute($params);
    echo json_encode(['mensaje' => 'Proveedor guardado correctamente']);
}

function editarProveedor($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $params = [
        ':p_idProveedor' => $data['idProveedor'],
        ':p_nombre' => $data['nombre'],
        ':p_apellido' => $data['apellido'],
        ':p_dni' => $data['dni'],
        ':p_direccion' => $data['direccion'],
        ':p_telefono' => $data['telefono'],
        ':p_ruc' => $data['ruc']
    ];

    $stmt = $pdo->prepare("CALL sp_editar_Proveedor(:p_idProveedor, :p_nombre, :p_apellido, :p_dni, :p_direccion, :p_telefono, :p_ruc)");
    $stmt->execute($params);
    echo json_encode(['mensaje' => 'Proveedor editado correctamente']);
}

function eliminarProveedor($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $idProveedor = $data['idProveedor'] ?? null;

    $stmt = $pdo->prepare("CALL sp_eliminar_Proveedor(:p_idProveedor)");
    $stmt->execute(['p_idProveedor' => $idProveedor]);
    echo json_encode(['mensaje' => 'Proveedor eliminado correctamente']);
}
?>
