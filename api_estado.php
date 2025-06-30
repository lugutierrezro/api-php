<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'Conexion.php';
error_reporting(0);
ini_set('display_errors', 0);

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'actualizar_estado_general':
        actualizarEstadoGeneral($conn);
        break;
    case 'cambiar_estado_usuario':
        cambiarEstadoUsuario($conn);
        break;
    case 'listar_deshabilitados':
        listarDeshabilitados($conn);
        break;
    case 'listar_usuarios':
        listarUsuarios($conn);
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
}

function actualizarEstadoGeneral($conn) {
    $tabla = $_GET['tabla'] ?? '';
    $campoId = $_GET['campo'] ?? '';
    $valorId = $_GET['valor'] ?? '';
    $nuevoEstado = $_GET['estado'] ?? '';

    if ($tabla && $campoId && $valorId && $nuevoEstado) {
        $stmt = $conn->prepare("CALL sp_actualizar_estado_general(?, ?, ?, ?, @msg)");
        $stmt->bind_param("ssss", $tabla, $campoId, $valorId, $nuevoEstado);
        $stmt->execute();
        $stmt->close();

        $res = $conn->query("SELECT @msg AS mensaje");
        echo json_encode($res->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Parámetros incompletos']);
    }
}

function cambiarEstadoUsuario($conn) {
    $idUsuario = $_GET['idUsuario'] ?? '';
    $nuevoEstado = $_GET['estado'] ?? '';

    if ($idUsuario && $nuevoEstado !== '') {
        $stmt = $conn->prepare("CALL sp_cambiar_estado_usuario(?, ?, @msg)");
        $stmt->bind_param("ii", $idUsuario, $nuevoEstado);
        $stmt->execute();
        $stmt->close();

        $res = $conn->query("SELECT @msg AS mensaje");
        echo json_encode($res->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Parámetros incompletos']);
    }
}

function listarDeshabilitados($conn) {
    $resultados = [];

    if ($conn->multi_query("CALL sp_listar_todo_deshabilitado()")) {
        do {
            if ($res = $conn->store_result()) {
                while ($fila = $res->fetch_all(MYSQLI_ASSOC)) {
                    $resultados[] = $fila;
                }
                $res->free();
            }
        } while ($conn->next_result());
        echo json_encode($resultados);
    } else {
        echo json_encode(['error' => 'No se pudo ejecutar el procedimiento']);
    }
}

function listarUsuarios($conn) {
    $res = $conn->query("CALL sp_listar_usuarios_con_empleado()");
    $datos = [];

    while ($fila = $res->fetch_assoc()) {
        $datos[] = $fila;
    }

    echo json_encode($datos);
}
?>
