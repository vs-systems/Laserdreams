<?php
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');

    if ($id > 0 && $nombre !== '') {
        $tipo_dolar = $_POST['tipo_dolar'] === 'oficial' ? 'oficial' : 'blue';
        $recargo_dolar_pesos = (float) ($_POST['recargo_dolar_pesos'] ?? 0);
        $recargo_bancario_porcentaje = (float) ($_POST['recargo_bancario_porcentaje'] ?? 0);

        try {
            $stmt = $pdo->prepare("UPDATE marcas SET nombre = ?, tipo_dolar = ?, recargo_dolar_pesos = ?, recargo_bancario_porcentaje = ? WHERE id = ?");
            $stmt->execute([$nombre, $tipo_dolar, $recargo_dolar_pesos, $recargo_bancario_porcentaje, $id]);
            header('Location: index.php?ok=editado');
            exit;
        } catch (PDOException $e) {
            die("Error al actualizar marca: " . $e->getMessage());
        }
    }
}

header('Location: index.php');
exit;
