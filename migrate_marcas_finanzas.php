<?php
require __DIR__ . '/includes/db.php';

try {
    echo "Agregando columnas financieras a 'marcas'...<br>";
    $pdo->exec("ALTER TABLE marcas ADD COLUMN IF NOT EXISTS tipo_dolar ENUM('blue', 'oficial') DEFAULT 'blue'");
    $pdo->exec("ALTER TABLE marcas ADD COLUMN IF NOT EXISTS recargo_dolar_pesos DECIMAL(10,2) DEFAULT 0.00");
    $pdo->exec("ALTER TABLE marcas ADD COLUMN IF NOT EXISTS recargo_bancario_porcentaje DECIMAL(5,2) DEFAULT 0.00");

    echo "<br><b>Migración completada exitosamente.</b>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
