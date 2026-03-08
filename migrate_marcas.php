<?php
// migrate_marcas.php - Migration script for Brands system
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

    // 2. Add marca_id and es_usado to productos
    // Check if column exists first to avoid errors
    $columns = $pdo->query("DESCRIBE productos")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('marca_id', $columns)) {
        $pdo->exec("ALTER TABLE productos ADD COLUMN marca_id INT DEFAULT NULL AFTER categoria_id");
        $pdo->exec("ALTER TABLE productos ADD FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE SET NULL");
    }
    
    if (!in_array('es_usado', $columns)) {
        $pdo->exec("ALTER TABLE productos ADD COLUMN es_usado TINYINT(1) DEFAULT 0 AFTER es_destacado");
    }

    // 3. Migrate data: text brands to marcas table
    $stmt = $pdo->query("SELECT DISTINCT marca FROM productos WHERE marca IS NOT NULL AND marca != ''");
    $old_brands = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($old_brands as $brand_name) {
        $stmt_ins = $pdo->prepare("INSERT IGNORE INTO marcas (nombre) VALUES (?)");
        $stmt_ins->execute([$brand_name]);
        
        // Get the ID
        $stmt_id = $pdo->prepare("SELECT id FROM marcas WHERE nombre = ?");
        $stmt_id->execute([$brand_name]);
        $brand_id = $stmt_id->fetchColumn();

        // Update products
        $stmt_upd = $pdo->prepare("UPDATE productos SET marca_id = ? WHERE marca = ?");
        $stmt_upd->execute([$brand_id, $brand_name]);
    }

    $pdo->commit();
    echo "<h1>Migración de Marcas completada exitosamente.</h1>";
    echo "<p>Tabla 'marcas' creada, columnas 'marca_id' y 'es_usado' añadidas, y datos migrados.</p>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<h1>Error en la migración:</h1><pre>" . $e->getMessage() . "</pre>";
}

// Security: script deletes itself after execution
// unlink(__FILE__); 
?>
