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
    case 'registrar_suministro':
        registrarSuministro($pdo);
        break;
    case 'listar_suministros':
        listarSuministros($pdo);
        break;
    case 'buscar_suministro':
        buscarSuministro($pdo);
        break;
    case 'eliminar_suministro':
        eliminarSuministro($pdo);
        break;
    case 'listar_detalle':
        listarDetalleSuministro($pdo);
        break;
    case 'historial_compras': // NUEVA ACCIÓN
        historialCompras($pdo);
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

// FUNCIONES

function registrarSuministro($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['error' => 'JSON inválido']);
        return;
    }

    if (
        !isset($data['productos']) || !is_array($data['productos']) ||
        !isset($data['cantidades']) || !is_array($data['cantidades']) ||
        !isset($data['precios']) || !is_array($data['precios'])
    ) {
        echo json_encode(['error' => 'Los datos de productos, cantidades o precios no son válidos', 'data' => $data]);
        return;
    }

    try {
        $stmt = $pdo->prepare("CALL sp_suministro_completo(
            :p_fecha, :p_hora, :p_num_documento, :p_tipo_documento,
            :p_idUsuario, :p_idProveedor, :p_productos, :p_cantidades, :p_precios
        )");

        $stmt->execute([
            ':p_fecha' => $data['fecha'],
            ':p_hora' => $data['hora'],
            ':p_num_documento' => $data['num_documento'],
            ':p_tipo_documento' => $data['tipo_documento'],
            ':p_idUsuario' => $data['idUsuario'],
            ':p_idProveedor' => $data['idProveedor'],
            ':p_productos' => implode(',', $data['productos']),
            ':p_cantidades' => implode(',', $data['cantidades']),
            ':p_precios' => implode(',', $data['precios']),
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['mensaje' => $result['mensaje'] ?? 'Suministro registrado correctamente']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al registrar suministro', 'detalle' => $e->getMessage()]);
    }
}

function listarDetalleSuministro($pdo) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(['error' => 'Falta el parámetro id']);
        return;
    }

    try {
        $stmt = $pdo->prepare("CALL sp_listar_detalle_suministro(:id)");
        $stmt->execute([':id' => $id]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($detalles);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al listar detalle', 'detalle' => $e->getMessage()]);
    }
}

function listarSuministros($pdo) {
    try {
        $stmt = $pdo->prepare("CALL sp_listar_suministros()");
        $stmt->execute();
        $suministros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($suministros);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al listar suministros', 'detalle' => $e->getMessage()]);
    }
}

function buscarSuministro($pdo) {
    $buscar = $_GET['buscar'] ?? '';
    try {
        $stmt = $pdo->prepare("CALL sp_buscar_suministro(:buscar)");
        $stmt->execute([':buscar' => $buscar]);
        $suministros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($suministros);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al buscar suministro', 'detalle' => $e->getMessage()]);
    }
}

function eliminarSuministro($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $idSuministro = $data['idSuministro'] ?? null;

    if (!$idSuministro) {
        echo json_encode(['error' => 'Falta idSuministro']);
        return;
    }

    try {
        $stmt = $pdo->prepare("CALL sp_eliminar_suministro(:p_idSuministro)");
        $stmt->execute([':p_idSuministro' => $idSuministro]);
        echo json_encode(['mensaje' => 'Suministro anulado correctamente']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al anular suministro', 'detalle' => $e->getMessage()]);
    }
}

// NUEVA FUNCIÓN
function historialCompras($pdo) {
    try {
        $stmt = $pdo->prepare("CALL sp_historial_compras()");
        $stmt->execute();
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($historial);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al obtener historial de compras', 'detalle' => $e->getMessage()]);
    }
}
?>
