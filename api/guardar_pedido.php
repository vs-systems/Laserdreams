<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$nombre = trim($input['nombre'] ?? '');
$localidad = trim($input['localidad'] ?? '');
$email = trim($input['email'] ?? 'vía WhatsApp');
$carrito = $input['carrito'] ?? [];
$total = (float) ($input['total'] ?? 0);

if (empty($nombre) || empty($carrito)) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO pedidos (nombre, localidad, email, carrito, total, estado)
        VALUES (?, ?, ?, ?, ?, 'Nuevo')
    ");

    $stmt->execute([
        $nombre,
        $localidad,
        $email,
        json_encode($carrito),
        $total
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al guardar en DB: ' . $e->getMessage()]);
}
