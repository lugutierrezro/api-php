<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'Conexion.php';
error_reporting(0);
ini_set('display_errors', 0);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'registrar_venta':
        registrarVenta($pdo);
        break;
    case 'listar_ventas':
        listarVentas($pdo);
        break;
    case 'buscar_venta':
        buscarVenta($pdo);
        break;
    case 'listar_detalle_venta':
        listarDetalleVenta($pdo);
        break;
    case 'eliminar_venta':
        eliminarVenta($pdo);
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

// FUNCIONES

function registrarVenta($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['error' => 'JSON inválido']);
        return;
    }

    try {
        $stmt = $pdo->prepare("CALL sp_venta_completa(
            :p_fecha, :p_hora, :p_serie, :p_num_documento, :p_tipo_documento,
            :p_idUsuario, :p_idCliente, :p_productos, :p_cantidades, :p_precios
        )");

        $stmt->execute([
            ':p_fecha' => $data['fecha'],
            ':p_hora' => $data['hora'],
            ':p_serie' => $data['serie'],
            ':p_num_documento' => $data['num_documento'],
            ':p_tipo_documento' => $data['tipo_documento'],
            ':p_idUsuario' => $data['idUsuario'],
            ':p_idCliente' => $data['idCliente'],
            ':p_productos' => implode(',', $data['productos']),
            ':p_cantidades' => implode(',', $data['cantidades']),
            ':p_precios' => implode(',', $data['precios']),
        ]);

        echo json_encode(['mensaje' => 'Venta registrada correctamente']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al registrar venta', 'detalle' => $e->getMessage()]);
    }
}

function listarVentas($pdo) {
    try {
        $stmt = $pdo->prepare("CALL sp_listar_ventas()");
        $stmt->execute();
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = array_map(function ($v) {
            return [
                'idVenta' => $v['idVenta'],
                'fecha' => $v['fecha'],
                'hora' => $v['hora'],
                'serie' => $v['serie'],
                'num_documento' => $v['num_documento'],
                'tipo_documento' => $v['tipo_documento'],
                'subtotal' => $v['subtotal'],
                'igv' => $v['igv'],
                'total' => $v['total'],
                'estado' => $v['estado'],
                'cliente' => $v['Cliente'],
                'usuario' => $v['Usuario']
            ];
        }, $ventas);

        echo json_encode($resultado);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al listar ventas', 'detalle' => $e->getMessage()]);
    }
}

function buscarVenta($pdo) {
    $buscar = $_GET['buscar'] ?? '';
    try {
        $stmt = $pdo->prepare("CALL sp_buscar_venta(:buscar)");
        $stmt->execute([':buscar' => $buscar]);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = array_map(function ($v) {
            return [
                'idVenta' => $v['idVenta'],
                'fecha' => $v['fecha'],
                'hora' => $v['hora'],
                'serie' => $v['serie'],
                'num_documento' => $v['num_documento'],
                'tipo_documento' => $v['tipo_documento'],
                'subtotal' => $v['subtotal'],
                'igv' => $v['igv'],
                'total' => $v['total'],
                'estado' => $v['estado'],
                'cliente' => $v['Cliente'],
                'usuario' => $v['Usuario']
            ];
        }, $ventas);

        echo json_encode($resultado);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al buscar venta', 'detalle' => $e->getMessage()]);
    }
}

function listarDetalleVenta($pdo) {
    $idVenta = $_GET['idVenta'] ?? null;

    if (!$idVenta) {
        echo json_encode(['error' => 'Falta el idVenta']);
        return;
    }

    try {
        $stmt = $pdo->prepare("CALL sp_detalle_venta_por_id(:p_idVenta)");
        $stmt->execute([':p_idVenta' => $idVenta]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = array_map(function ($d) {
            return [
                'idDetalleVenta' => $d['idDetalleVenta'],
                'producto' => $d['Producto'],
                'cantidad' => $d['cantidad'],
                'precio' => $d['precio'],
                'total' => $d['total']
            ];
        }, $detalles);

        echo json_encode($resultado);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al listar detalle de venta', 'detalle' => $e->getMessage()]);
    }
}

function eliminarVenta($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $idVenta = $data['idVenta'] ?? null;

    if (!$idVenta) {
        echo json_encode(['error' => 'Falta idVenta']);
        return;
    }

    try {
        $stmt = $pdo->prepare("CALL sp_eliminar_venta(:p_idVenta)");
        $stmt->execute([':p_idVenta' => $idVenta]);
        echo json_encode(['mensaje' => 'Venta anulada correctamente']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al anular venta', 'detalle' => $e->getMessage()]);
    }
}
?>
