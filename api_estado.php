<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'Conexion.php'; // aquí se define $pdo
error_reporting(E_ALL);
ini_set('display_errors', 1);

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'actualizar_estado_general':
        actualizarEstadoGeneral($pdo);
        break;
    case 'cambiar_estado_usuario':
        cambiarEstadoUsuario($pdo);
        break;
    case 'listar_deshabilitados':
        listarDeshabilitados($pdo);
        break;
    case 'listar_usuarios':
        listarUsuarios($pdo);
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
}

function actualizarEstadoGeneral($pdo) {
    $tabla = $_GET['tabla'] ?? '';
    $campoId = $_GET['campo'] ?? '';
    $valorId = $_GET['valor'] ?? '';
    $nuevoEstado = $_GET['estado'] ?? '';

    if ($tabla && $campoId && $valorId && $nuevoEstado) {
        try {
            $stmt = $pdo->prepare("CALL sp_actualizar_estado_general(?, ?, ?, ?, @msg)");
            $stmt->execute([$tabla, $campoId, $valorId, $nuevoEstado]);

            $res = $pdo->query("SELECT @msg AS mensaje");
            $mensaje = $res->fetch(PDO::FETCH_ASSOC);
            echo json_encode($mensaje);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Parámetros incompletos']);
    }
}

function cambiarEstadoUsuario($pdo) {
    $idUsuario = $_GET['idUsuario'] ?? '';
    $nuevoEstado = $_GET['estado'] ?? '';

    if ($idUsuario && $nuevoEstado !== '') {
        try {
            $stmt = $pdo->prepare("CALL sp_cambiar_estado_usuario(?, ?, @msg)");
            $stmt->execute([$idUsuario, $nuevoEstado]);

            $res = $pdo->query("SELECT @msg AS mensaje");
            $mensaje = $res->fetch(PDO::FETCH_ASSOC);
            echo json_encode($mensaje);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Parámetros incompletos']);
    }
}

function listarDeshabilitados($pdo) {
    try {
        $stmt = $pdo->prepare("CALL sp_listar_todo_deshabilitado()");
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($resultados);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function listarUsuarios($pdo) {
    try {
        $stmt = $pdo->prepare("CALL sp_listar_usuarios_con_empleado()");
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($datos);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
