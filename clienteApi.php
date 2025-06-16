<?php
header('Content-Type: application/json');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Ajustar para producción
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'Conexion.php';
error_reporting(0);
ini_set('display_errors', 0);
// Obtener acción
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'listar_clientes':
        listarClientes($pdo);
        break;
    case 'guardar_cliente':
        guardarCliente($pdo);
        break;
    case 'editar_cliente':
        editarCliente($pdo);
        break;
    case 'eliminar_cliente':
        eliminarCliente($pdo);
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

// FUNCIONES

function listarClientes($pdo) {
    $buscar = $_GET['buscar'] ?? '';
    $stmt = $pdo->prepare("CALL sp_listar_Clientes(:buscar)");
    $stmt->execute(['buscar' => $buscar]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $clientes = array_map(function ($cliente) {
        return [
            'idCliente' => $cliente['idCliente'],
            'nombre' => $cliente['Nombre'],
            'apellido' => $cliente['Apellido'],
            'dni' => $cliente['Dni'],
            'telefono' => $cliente['Telefono'],
            'ruc' => $cliente['ruc'],
            'direccion' => $cliente['Direccion'],
        ];
    }, $clientes);

    echo json_encode($clientes);
}

function guardarCliente($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $params = [
        ':p_nombre' => $data['nombre'],
        ':p_apellido' => $data['apellido'],
        ':p_dni' => $data['dni'],
        ':p_direccion' => $data['direccion'],
        ':p_telefono' => $data['telefono'],
        ':p_ruc' => $data['ruc']
    ];

    $stmt = $pdo->prepare("CALL sp_guardar_Cliente(:p_nombre, :p_apellido, :p_dni, :p_direccion, :p_telefono, :p_ruc)");
    $stmt->execute($params);
    echo json_encode(['mensaje' => 'Cliente guardado correctamente']);
}

function editarCliente($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $params = [
        ':p_idCliente' => $data['idCliente'],
        ':p_nombre' => $data['nombre'],
        ':p_apellido' => $data['apellido'],
        ':p_dni' => $data['dni'],
        ':p_direccion' => $data['direccion'],
        ':p_telefono' => $data['telefono'],
        ':p_ruc' => $data['ruc']
    ];

    $stmt = $pdo->prepare("CALL sp_editar_Cliente(:p_idCliente, :p_nombre, :p_apellido, :p_dni, :p_direccion, :p_telefono, :p_ruc)");
    $stmt->execute($params);
    echo json_encode(['mensaje' => 'Cliente editado correctamente']);
}

function eliminarCliente($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $codigo = $data['idCliente'] ?? null;

    $stmt = $pdo->prepare("CALL sp_eliminar_cliente(:codigo)");
    $stmt->execute(['codigo' => $codigo]);
    echo json_encode(['mensaje' => 'Cliente eliminado correctamente']);
}
?>
