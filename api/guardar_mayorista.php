<?php
require __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos']);
    exit;
}

$nombre = trim($data['nombre'] ?? '');
$telefono = trim($data['telefono'] ?? '');
$localidad = trim($data['localidad'] ?? '');
$tipo_cliente = trim($data['tipo_cliente'] ?? '');
$tipo_cliente_otro = trim($data['tipo_cliente_otro'] ?? '');
$productos_interes = $data['productos_interes'] ?? [];

if (empty($nombre) || empty($telefono) || empty($localidad) || empty($tipo_cliente)) {
    echo json_encode(['success' => false, 'error' => 'Faltan completar campos obligatorios']);
    exit;
}

if ($tipo_cliente == 'Otro' && empty($tipo_cliente_otro)) {
    echo json_encode(['success' => false, 'error' => 'Por favor especifique el otro tipo de cliente']);
    exit;
}

// Convert products to JSON
$productos_json = json_encode($productos_interes);

try {
    $fecha = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO solicitudes_mayoristas (nombre, localidad, telefono, tipo_cliente, tipo_cliente_otro, productos_interes, fecha) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $nombre,
        $localidad,
        $telefono,
        $tipo_cliente,
        $tipo_cliente_otro,
        $productos_json,
        $fecha
    ]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos. Detalles: ' . $e->getMessage()]);
}
