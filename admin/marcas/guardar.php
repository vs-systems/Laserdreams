<?php
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');

    if ($nombre !== '') {
        $tipo_dolar = $_POST['tipo_dolar'] === 'oficial' ? 'oficial' : 'blue';
        $recargo_dolar_pesos = (float) ($_POST['recargo_dolar_pesos'] ?? 0);
        $recargo_bancario_porcentaje = (float) ($_POST['recargo_bancario_porcentaje'] ?? 0);

        try {
            $stmt = $pdo->prepare("INSERT INTO marcas (nombre, tipo_dolar, recargo_dolar_pesos, recargo_bancario_porcentaje) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $tipo_dolar, $recargo_dolar_pesos, $recargo_bancario_porcentaje]);
            header('Location: index.php?ok=1');
            exit;
        } catch (PDOException $e) {
            die("Error al guardar marca: " . $e->getMessage());
        }
    }
}

header('Location: index.php');
exit;
