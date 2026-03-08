<?php
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/db.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM marcas WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: index.php?ok=eliminado');
        exit;
    } catch (PDOException $e) {
        die("Error al eliminar marca: " . $e->getMessage());
    }
}

header('Location: index.php');
exit;
