<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'Conexion.php'; 

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'buscar_empleado':
        buscarEmpleado($pdo);
        break;
    case 'guardar_empleado':
        guardarEmpleado($pdo);
        break;
    case 'eliminar_empleado':
        eliminarEmpleado($pdo);
        break;
    case 'login':
        login($pdo);
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

// --- FUNCIONES ---

function buscarEmpleado($pdo) {
    $buscar = $_GET['buscar'] ?? '';
    $stmt = $pdo->prepare("CALL sp_buscar_empleado(:buscar)");
    $stmt->execute(['buscar' => $buscar]);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($empleados);
}

function guardarEmpleado($pdo) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        file_put_contents("debug_guardar.txt", json_encode($data) . PHP_EOL, FILE_APPEND);
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        

        $params = [
            ':p_nombre' => $data['nombre'],
            ':p_apellido' => $data['apellido'],
            ':p_dni' => $data['dni'],
            ':p_direccion' => $data['direccion'],
            ':p_telefono' => $data['telefono'],
            ':p_idRol' => $data['idRol'],
            ':p_idEstado' => $data['idEstado'],
            ':p_usuario' => $data['usuario'],
            ':p_password' => $passwordHash
        ];

        $stmt = $pdo->prepare("CALL sp_guardar_empleado(:p_nombre, :p_apellido, :p_dni, :p_direccion, :p_telefono, :p_idRol, :p_idEstado, :p_usuario, :p_password)");
        $stmt->execute($params);

        echo json_encode(['mensaje' => 'Empleado registrado correctamente']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function eliminarEmpleado($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    $idUsuario = $data['idUsuario'] ?? null;

    if (!$idUsuario) {
        echo json_encode(['error' => 'ID de usuario no proporcionado']);
        return;
    }

    $stmt = $pdo->prepare("CALL sp_eliminar_empleado(:p_idUsuario)");
    $stmt->execute(['p_idUsuario' => $idUsuario]);

    echo json_encode(['mensaje' => 'Empleado eliminado correctamente']);
}

function login($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    $usuario = $data['usuario'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT 
            u.idUsuario, u.contraseña, u.usuario,
            e.idEmpleado,
            p.Nombre, p.Apellido,
            r.nombreRol AS Rol,
            ec.estado AS EstadoCuenta
        FROM Usuario u
        JOIN Empleado e ON u.idEmpleado = e.idEmpleado
        JOIN Persona p ON e.idPersonaEmpleado = p.idPersona
        JOIN Rol r ON u.idRol = r.idRol
        JOIN EstadoCuenta ec ON u.idEstado = ec.idEstado
        WHERE u.usuario = :usuario
        LIMIT 1
    ");
    $stmt->execute(['usuario' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['contraseña']) && $user['EstadoCuenta'] === 'Habilitado') {
        echo json_encode([
            'idUsuario' => $user['idUsuario'],
            'idEmpleado' => $user['idEmpleado'],
            'nombre' => $user['Nombre'],
            'apellido' => $user['Apellido'],
            'rol' => $user['Rol'],
            'estado' => $user['EstadoCuenta']
        ]);
    } else {
        echo json_encode(['error' => 'Usuario o contraseña incorrecta, o cuenta deshabilitada']);
    }
}
?>