<?php
// final_migration.php - Full database consistency check and updates
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/includes/db.php';

try {
    $pdo->beginTransaction();

    // 1. Create marcas table
    $pdo->exec("CREATE TABLE IF NOT EXISTS marcas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Update productos table
    $columns = $pdo->query("DESCRIBE productos")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('en_stock', $columns)) {
        $pdo->exec("ALTER TABLE productos ADD COLUMN en_stock TINYINT(1) DEFAULT 1 AFTER es_destacado");
    }

    if (!in_array('marca_id', $columns)) {
        $pdo->exec("ALTER TABLE productos ADD COLUMN marca_id INT DEFAULT NULL AFTER categoria_id");
        $pdo->exec("ALTER TABLE productos ADD FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE SET NULL");
    }

    if (!in_array('es_usado', $columns)) {
        $pdo->exec("ALTER TABLE productos ADD COLUMN es_usado TINYINT(1) DEFAULT 0 AFTER es_destacado");
    }

    // 3. Update pedidos table
    $columns_pedidos = $pdo->query("DESCRIBE pedidos")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('localidad', $columns_pedidos)) {
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN localidad VARCHAR(255) DEFAULT NULL AFTER email");
    }

    // 4. Data migration: text brands to marcas table (if not already done)
    $stmt = $pdo->query("SHOW COLUMNS FROM productos LIKE 'marca'");
    if ($stmt->fetch()) {
        $stmt = $pdo->query("SELECT DISTINCT marca FROM productos WHERE marca IS NOT NULL AND marca != ''");
        $old_brands = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($old_brands as $brand_name) {
            $stmt_ins = $pdo->prepare("INSERT IGNORE INTO marcas (nombre) VALUES (?)");
            $stmt_ins->execute([$brand_name]);

            $stmt_id = $pdo->prepare("SELECT id FROM marcas WHERE nombre = ?");
            $stmt_id->execute([$brand_name]);
            $brand_id = $stmt_id->fetchColumn();

            $stmt_upd = $pdo->prepare("UPDATE productos SET marca_id = ? WHERE marca = ?");
            $stmt_upd->execute([$brand_id, $brand_name]);
        }
    }

    $pdo->commit();
    echo "<h1>Migración Final completada con éxito.</h1>";
    echo "<ul>
            <li>✅ Tabla de Marcas verificada/creada.</li>
            <li>✅ Columnas 'en_stock', 'marca_id', 'es_usado' verificadas en productos.</li>
            <li>✅ Columna 'localidad' añadida a pedidos.</li>
            <li>✅ Datos de marcas migrados.</li>
          </ul>";

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo "<h1>Error en la migración final:</h1><pre>" . $e->getMessage() . "</pre>";
}
?>