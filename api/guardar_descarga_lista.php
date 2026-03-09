<?php
require __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos']);
    exit;
}

$nombre = trim($data['nombre'] ?? '');
$email = trim($data['email'] ?? '');
$whatsapp = trim($data['whatsapp'] ?? '');

if (empty($nombre) || empty($email) || empty($whatsapp)) {
    echo json_encode(['success' => false, 'error' => 'Faltan completar campos obligatorios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'El correo electrónico no es válido']);
    exit;
}

// Validación básica de teléfono (solo números y longitud mínima 8 y max 15)
$whatsapp_clean = preg_replace('/[^0-9]/', '', $whatsapp);
if (strlen($whatsapp_clean) < 8 || strlen($whatsapp_clean) > 15) {
    echo json_encode(['success' => false, 'error' => 'El número de WhatsApp no parece ser válido']);
    exit;
}

try {
    $fecha = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO descargas_listas (nombre, email, whatsapp, fecha) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $whatsapp_clean, $fecha]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos. Póngase en contacto con el administrador.',
        'debug' => $e->getMessage()
    ]);
}
